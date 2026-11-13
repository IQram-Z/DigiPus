<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Anggota, Booking, Buku, Denda, EksemplarBuku, Kategori, LogAktivitas, PengaturanPerpustakaan, Peminjaman, User};
use App\Support\{Report, Sirkulasi};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Dashboard administrator (DGP-016), pengaturan (DGP-021), isi dashboard pengunjung, dan log aktivitas (DGP-023). */
class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();
        $stats = [
            'buku' => Buku::count(),
            'eksemplar' => EksemplarBuku::count(),
            'tersedia' => EksemplarBuku::where('status', 'tersedia')->count(),
            'anggota' => Anggota::where('status_keanggotaan', 'aktif')->count(),
            'pengunjung' => User::where('role', 'pengunjung')->count(),
            'staf' => User::whereIn('role', ['petugas', 'administrator'])->count(),
            'aktif' => Peminjaman::where('status_peminjaman', 'dipinjam')->count(),
            'terlambat' => Peminjaman::where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', '<', $today)->count(),
            'booking' => Booking::whereIn('status_booking', Sirkulasi::BOOKING_AKTIF)->count(),
            'denda' => (int) Denda::where('status_pembayaran', 'belum_lunas')->sum('total_denda'),
        ];

        return view('admin.dashboard', [
            'stats' => $stats, 'd' => Report::data('harian'),
            'pengajuan' => User::where('role', 'pengunjung')->whereNotNull('pengajuan_at')->orderBy('pengajuan_at')->limit(5)->get(),
            'log' => LogAktivitas::with('user')->orderByDesc('id')->limit(8)->get(),
        ]);
    }

    // ---------- Isi dashboard pengunjung/anggota ----------
    public function tampilan()
    {
        return view('admin.tampilan', [
            'set' => PengaturanPerpustakaan::cfg(),
            'books' => Buku::with('kategori')->orderByDesc('rekomendasi')->orderBy('urutan_rekomendasi')->orderBy('judul')->get(),
            'kategori' => Kategori::with('buku')->orderBy('urutan')->orderBy('id')->get(),
        ]);
    }

    public function simpanTampilan(Request $r)
    {
        $data = $r->validate([
            'judul_dashboard' => 'required|string|max:60', 'judul_rekomendasi' => 'required|string|max:60',
            'judul_kategori' => 'required|string|max:60', 'judul_koleksi' => 'required|string|max:60',
            'max_rekomendasi' => 'required|integer|min:1|max:24',
        ]);
        PengaturanPerpustakaan::cfg()->update($data);

        $rek = (array) $r->input('rek', []);
        $urutan = (array) $r->input('urutan', []);
        foreach (Buku::pluck('id') as $id) {
            Buku::whereKey($id)->update(['rekomendasi' => isset($rek[$id]), 'urutan_rekomendasi' => max(0, min(999, (int) ($urutan[$id] ?? 0)))]);
        }
        foreach ((array) $r->input('wakil', []) as $kid => $bid) {
            Kategori::whereKey((int) $kid)->update(['buku_wakil_id' => $bid ? (int) $bid : null]);
        }

        Sirkulasi::log(Auth::user(), 'Mengubah tampilan dashboard');
        return back()->with('toast', 'Tampilan dashboard disimpan.');
    }

    // ---------- Pengaturan peminjaman & denda ----------
    public function pengaturan()
    {
        return view('admin.pengaturan', ['set' => PengaturanPerpustakaan::cfg()]);
    }

    public function simpanPengaturan(Request $r)
    {
        $data = $r->validate([
            'durasi_pinjam_hari' => 'required|integer|min:1|max:60',
            'maks_buku_dipinjam' => 'required|integer|min:1|max:20',
            'tarif_denda_per_hari' => 'required|integer|min:0|max:1000000',
        ]);
        $data['blokir_pinjam_jika_denda'] = $r->boolean('blokir_pinjam_jika_denda');
        PengaturanPerpustakaan::cfg()->update($data);

        Sirkulasi::log(Auth::user(), 'Mengubah pengaturan peminjaman & denda');
        return back()->with('toast', 'Pengaturan disimpan.');
    }

    // ---------- Log aktivitas ----------
    public function log(Request $r)
    {
        $q = LogAktivitas::with('user')->orderByDesc('id');
        if ($s = trim((string) $r->query('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('aktivitas', 'like', "%$s%")->orWhereHas('user', fn ($x) => $x->where('name', 'like', "%$s%")->orWhere('username', 'like', "%$s%"));
            });
        }
        return view('admin.log', ['items' => $q->paginate(25)->withQueryString(), 'q' => $s ?? '']);
    }
}
