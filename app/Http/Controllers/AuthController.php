<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Sirkulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, RateLimiter};

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $r)
    {
        $key = 'login:' . $r->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['login' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.']);
        }

        $data = $r->validate(['login' => 'required|string', 'password' => 'required'], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $ok = Auth::attempt([$field => $data['login'], 'password' => $data['password'], 'status_akun' => 'aktif']);

        if (! $ok) {
            RateLimiter::hit($key, 60); // ditahan 1 menit setelah 5 kali salah (SRS)
            return back()->withInput()->withErrors(['login' => 'Username atau kata sandi salah, atau akun dinonaktifkan.']);
        }

        RateLimiter::clear($key);
        $r->session()->regenerate();
        Sirkulasi::log(Auth::user(), 'Login');
        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    /** Registrasi membuat akun PENGUNJUNG. Jadi anggota (kartu + booking) lewat verifikasi admin. */
    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:60',
            'email' => 'required|email|max:120|unique:users,email',
            'password' => 'required|min:6',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = User::create([
            'name' => $data['name'], 'username' => User::usernameUnik(strtok($data['email'], '@')),
            'email' => $data['email'], 'password' => $data['password'], 'role' => 'pengunjung',
        ]);

        Auth::login($user);
        $r->session()->regenerate();
        Sirkulasi::log($user, 'Registrasi akun');
        return redirect()->route('dashboard')->with('toast', 'Akun berhasil dibuat. Ajukan keanggotaan di menu "Jadi Anggota" untuk booking dan kartu digital.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
