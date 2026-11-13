<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\{OAuth, Sirkulasi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt, Http};
use Illuminate\Support\Str;

/**
 * Login / daftar dengan Google dan Apple (OAuth 2.0 / OpenID Connect, tanpa paket tambahan).
 * Akun baru dibuat sebagai PENGUNJUNG. Kunci OAuth diisi di .env (lihat README).
 */
class SocialAuthController extends Controller
{
    private function configured(string $p): bool
    {
        $c = config("services.$p");
        return $p === 'google'
            ? ! empty($c['client_id']) && ! empty($c['client_secret'])
            : ! empty($c['client_id']) && ! empty($c['team_id']) && ! empty($c['key_id']) && (! empty($c['private_key']) || ! empty($c['private_key_path']));
    }

    private function fail(string $msg)
    {
        return redirect()->route('login')->withErrors(['login' => $msg]);
    }

    // state tanpa sesi (terenkripsi + kedaluwarsa) agar callback POST dari Apple tetap valid
    private function makeState(string $p): string
    {
        return Crypt::encryptString(json_encode(['t' => time(), 'p' => $p, 'n' => Str::random(10)]));
    }

    private function stateValid(string $state, string $p): bool
    {
        try { $d = json_decode(Crypt::decryptString($state), true); } catch (\Throwable $e) { return false; }
        return is_array($d) && ($d['p'] ?? '') === $p && time() - ($d['t'] ?? 0) < 600;
    }

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'apple'], true), 404);
        $label = ucfirst($provider);
        if (! $this->configured($provider)) {
            return $this->fail("Login dengan $label belum dikonfigurasi. Isi kunci OAuth di file .env (lihat README bagian Login Google & Apple).");
        }

        $c = config("services.$provider");
        $q = ['client_id' => $c['client_id'], 'redirect_uri' => $c['redirect'], 'response_type' => 'code', 'state' => $this->makeState($provider)];

        if ($provider === 'google') {
            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($q + ['scope' => 'openid email profile', 'prompt' => 'select_account']);
        } else {
            $url = 'https://appleid.apple.com/auth/authorize?' . http_build_query($q + ['response_mode' => 'form_post', 'scope' => 'name email']);
        }
        return redirect()->away($url);
    }

    public function callback(Request $r, string $provider)
    {
        abort_unless(in_array($provider, ['google', 'apple'], true), 404);
        $label = ucfirst($provider);

        if ($r->filled('error')) return $this->fail("Login dengan $label dibatalkan.");
        if (! $this->stateValid((string) $r->input('state'), $provider)) return $this->fail('Sesi login sosial tidak valid atau sudah kedaluwarsa. Coba lagi.');
        if (! $r->filled('code')) return $this->fail("Tidak ada kode otorisasi dari $label.");

        try {
            $info = $provider === 'google' ? $this->google((string) $r->input('code')) : $this->apple((string) $r->input('code'), $r->input('user'));
            $user = $this->resolveUser($provider, $info);
        } catch (\Throwable $e) {
            report($e);
            return $this->fail("Gagal masuk dengan $label. " . ($e instanceof \DomainException ? $e->getMessage() : 'Coba lagi atau gunakan email dan kata sandi.'));
        }

        if ($user->status_akun !== 'aktif') return $this->fail('Akun ini dinonaktifkan. Hubungi administrator.');

        Auth::login($user, true);
        $r->session()->regenerate();
        Sirkulasi::log($user, "Login dengan $label");
        return redirect()->intended(route($user->homeRoute()));
    }

    private function google(string $code): array
    {
        $c = config('services.google');
        $t = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code, 'client_id' => $c['client_id'], 'client_secret' => $c['client_secret'],
            'redirect_uri' => $c['redirect'], 'grant_type' => 'authorization_code',
        ])->throw()->json();

        $u = Http::withToken($t['access_token'])->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();

        return [
            'id' => $u['sub'], 'email' => $u['email'] ?? null, 'verified' => (bool) ($u['email_verified'] ?? false),
            'name' => $u['name'] ?? ($u['email'] ?? 'Pengguna Google'), 'picture' => $u['picture'] ?? null,
        ];
    }

    private function apple(string $code, $userJson): array
    {
        $c = config('services.apple');
        $pem = ! empty($c['private_key']) ? str_replace('\n', "\n", $c['private_key']) : (is_file((string) $c['private_key_path']) ? file_get_contents($c['private_key_path']) : '');
        $secret = OAuth::appleClientSecret($c['team_id'], $c['client_id'], $c['key_id'], $pem);

        $t = Http::asForm()->post('https://appleid.apple.com/auth/token', [
            'client_id' => $c['client_id'], 'client_secret' => $secret, 'code' => $code,
            'grant_type' => 'authorization_code', 'redirect_uri' => $c['redirect'],
        ])->throw()->json();

        // id_token diterima langsung dari token endpoint Apple lewat TLS, jadi cukup diperiksa iss & aud
        $p = OAuth::jwtPayload($t['id_token'] ?? '');
        if (($p['iss'] ?? '') !== 'https://appleid.apple.com' || ($p['aud'] ?? '') !== $c['client_id'] || empty($p['sub'])) {
            throw new \DomainException('Token Apple tidak valid.');
        }

        $name = null; // nama hanya dikirim Apple pada login pertama
        if ($userJson) {
            $j = json_decode((string) $userJson, true);
            $name = trim(($j['name']['firstName'] ?? '') . ' ' . ($j['name']['lastName'] ?? '')) ?: null;
        }

        return [
            'id' => $p['sub'], 'email' => $p['email'] ?? null,
            'verified' => filter_var($p['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'name' => $name ?: (isset($p['email']) ? strtok($p['email'], '@') : 'Pengguna Apple'), 'picture' => null,
        ];
    }

    private function resolveUser(string $provider, array $i): User
    {
        $user = User::where('provider', $provider)->where('provider_id', $i['id'])->first();

        if (! $user && $i['email']) {
            $user = User::where('email', $i['email'])->first();
            if ($user && ! $i['verified']) throw new \DomainException('Email sudah terdaftar dan belum terverifikasi oleh penyedia.');
            if ($user) $user->update(['provider' => $provider, 'provider_id' => $i['id'], 'foto' => $user->foto ?: $i['picture']]); // tautkan ke akun yang ada
        }

        if (! $user) {
            $email = $i['email'] ?: "{$provider}_{$i['id']}@social.digipus.local";
            $user = User::create([
                'name' => $i['name'], 'username' => User::usernameUnik(strtok($email, '@')), 'email' => $email,
                'password' => Str::random(40), 'role' => 'pengunjung', 'provider' => $provider, 'provider_id' => $i['id'],
                'foto' => $i['picture'], 'email_verified_at' => $i['verified'] ? now() : null,
            ]);
            Sirkulasi::log($user, 'Registrasi dengan ' . ucfirst($provider));
        }

        return $user;
    }
}
