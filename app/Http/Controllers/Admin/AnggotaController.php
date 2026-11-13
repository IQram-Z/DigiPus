<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Anggota, User};
use App\Support\Sirkulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Str;

/** Pengelolaan anggota (SRS DGP-019): jadikan pengunjung sebagai anggota, daftar langsung, ubah status. */
class AnggotaController extends Controller
{
    public function index(Request $r)
    {
        $q = Anggota::with('user')->withCount(['peminjaman as aktif_count' => fn ($x) => $x->where('status_peminjaman', 'dipinjam')])->orderByDesc('id');
        if ($s = trim((string) $r->query('q'))) {
            $q->where(fn ($w) => $w->where('no_anggota', 'like', "%$s%")->orWhereHas('user', fn ($x) => $x->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")));
        }
        if (in_array($r->query('status'), ['aktif', 'nonaktif', 'blokir'], true)) $q->where('status_keanggotaan', $r->query('status'));

        return view('admin.anggota.index', ['items' => $q->paginate(15)->withQueryString(), 'q' => $s ?? '', 'status' => $r->query('status')]);
    }

    public function pengunjung(Request $r)
    {
        $q = User::where('role', 'pengunjung')->orderByRaw('pengajuan_at is null')->orderByDesc('pengajuan_at')->orderByDesc('id');
        if ($s = trim((string) $r->query('q'))) $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('username', 'like', "%$s%"));
        if ($r->query('tab') === 'pengajuan') $q->whereNotNull('pengajuan_at');

        return view('admin.anggota.pengunjung', ['items' => $q->paginate(15)->withQueryString(), 'q' => $s ?? '', 'tab' => $r->query('tab') === 'pengajuan' ? 'pengajuan' : 'semua']);
    }

    public function jadikan(User $user)
    {
        abort_unless($user->role === 'pengunjung', 404);
        $a = Anggota::daftarkan($user, Auth::user());
        Sirkulasi::log(Auth::user(), "Jadikan anggota: {$user->name} ({$a->no_anggota})");
        return back()->with('toast', "{$user->name} resmi menjadi anggota dengan nomor {$a->no_anggota}.");
    }

    public function tolak(User $user)
    {
        $user->update(['pengajuan_at' => null]);
        return back()->with('toast', 'Pengajuan keanggotaan ditolak/dihapus dari antrean.');
    }

    public function create()
    {
        return view('admin.anggota.create');
    }

    /** Daftar langsung (pelanggan datang ke perpustakaan): buat akun + anggota sekaligus. */
    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:60', 'email' => 'required|email|max:120|unique:users,email', 'password' => 'nullable|min:6']);
        $pw = $d['password'] ?? Str::lower(Str::random(8));

        $a = DB::transaction(function () use ($d, $pw) {
            $u = User::create(['name' => $d['name'], 'username' => User::usernameUnik(strtok($d['email'], '@')), 'email' => $d['email'], 'password' => $pw, 'role' => 'pengunjung', 'email_verified_at' => now()]);
            return Anggota::daftarkan($u, Auth::user());
        });

        Sirkulasi::log(Auth::user(), "Daftarkan anggota baru: {$d['name']} ({$a->no_anggota})");
        $msg = "Anggota {$d['name']} terdaftar (No. {$a->no_anggota})." . (empty($d['password']) ? " Kata sandi sementara: $pw" : '');
        return redirect()->route('admin.anggota.index')->with('toast', $msg);
    }

    public function status(Request $r, Anggota $anggota)
    {
        $s = $r->validate(['status' => 'required|in:aktif,nonaktif,blokir'])['status'];
        $anggota->update(['status_keanggotaan' => $s]);
        Sirkulasi::log(Auth::user(), "Status anggota {$anggota->no_anggota}: $s");
        return back()->with('toast', "Status {$anggota->no_anggota} diubah menjadi $s.");
    }

    public function perpanjang(Anggota $anggota)
    {
        $dasar = $anggota->berlaku_sampai && $anggota->berlaku_sampai->isFuture() ? $anggota->berlaku_sampai : Carbon::today();
        $anggota->update(['berlaku_sampai' => $dasar->copy()->addYears(2)]);
        Sirkulasi::log(Auth::user(), "Perpanjang kartu {$anggota->no_anggota}");
        return back()->with('toast', 'Masa berlaku kartu diperpanjang 2 tahun.');
    }
}
