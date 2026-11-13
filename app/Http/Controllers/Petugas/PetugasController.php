<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScanController;
use App\Models\{Anggota, Booking, Denda, PengaturanPerpustakaan, Peminjaman};
use App\Support\Sirkulasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Panel petugas perpustakaan (SRS DGP-009 s.d. DGP-014): booking, peminjaman, pengembalian, denda, verifikasi anggota. */
class PetugasController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();
        $stats = [
            'aktif' => Peminjaman::where('status_peminjaman', 'dipinjam')->count(),
            'jatuh_tempo' => Peminjaman::where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', $today)->count(),
            'terlambat' => Peminjaman::where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', '<', $today)->count(),
            'menunggu' => Booking::whereIn('status_booking', ['menunggu', 'antre'])->count(),
            'siap' => Booking::where('status_booking', 'disiapkan')->count(),
            'denda' => (int) Denda::where('status_pembayaran', 'belum_lunas')->sum('total_denda'),
        ];
        $antrean = Booking::with('anggota.user', 'buku')->whereIn('status_booking', ['menunggu', 'antre'])->orderBy('id')->limit(6)->get();
        $terlambat = Peminjaman::with('anggota.user', 'eksemplar.buku')->where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', '<', $today)->orderBy('tgl_tenggat')->limit(6)->get();

        return view('petugas.dashboard', compact('stats', 'antrean', 'terlambat') + ['tarif' => PengaturanPerpustakaan::cfg()->tarif_denda_per_hari]);
    }

    public function transaksi()
    {
        return view('petugas.transaksi');
    }

    // ---------- Peminjaman ----------
    public function peminjaman(Request $r)
    {
        $today = Carbon::today()->toDateString();
        $tab = in_array($r->query('tab'), ['aktif', 'terlambat', 'selesai'], true) ? $r->query('tab') : 'aktif';

        $q = Peminjaman::with('anggota.user', 'eksemplar.buku')->orderByDesc('id');
        if ($tab === 'selesai') $q->where('status_peminjaman', 'dikembalikan');
        elseif ($tab === 'terlambat') $q->where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', '<', $today);
        else $q->where('status_peminjaman', 'dipinjam');

        if ($s = trim((string) $r->query('q'))) {
            $q->where(function ($w) use ($s) {
                $w->whereHas('anggota.user', fn ($x) => $x->where('name', 'like', "%$s%"))
                  ->orWhereHas('anggota', fn ($x) => $x->where('no_anggota', 'like', "%$s%"))
                  ->orWhereHas('eksemplar', fn ($x) => $x->where('kode_eksemplar', 'like', "%$s%"))
                  ->orWhereHas('eksemplar.buku', fn ($x) => $x->where('judul', 'like', "%$s%"));
            });
        }

        return view('petugas.peminjaman', [
            'items' => $q->paginate(15)->withQueryString(), 'tab' => $tab, 'q' => $s ?? '',
            'tarif' => PengaturanPerpustakaan::cfg()->tarif_denda_per_hari,
            'n_aktif' => Peminjaman::where('status_peminjaman', 'dipinjam')->count(),
            'n_telat' => Peminjaman::where('status_peminjaman', 'dipinjam')->where('tgl_tenggat', '<', $today)->count(),
        ]);
    }

    public function kembali(Peminjaman $peminjaman)
    {
        $res = Sirkulasi::kembali($peminjaman, Auth::user());
        return back()->with('toast', $res['message']);
    }

    // ---------- Booking ----------
    public function booking(Request $r)
    {
        $tab = in_array($r->query('tab'), ['menunggu', 'siap', 'selesai'], true) ? $r->query('tab') : 'menunggu';
        $q = Booking::with('anggota.user', 'buku', 'eksemplar')->orderBy('id', $tab === 'selesai' ? 'desc' : 'asc');
        if ($tab === 'siap') $q->where('status_booking', 'disiapkan');
        elseif ($tab === 'selesai') $q->whereIn('status_booking', ['diambil', 'dibatalkan']);
        else $q->whereIn('status_booking', ['menunggu', 'antre']);

        return view('petugas.booking', [
            'items' => $q->paginate(15)->withQueryString(), 'tab' => $tab,
            'n_menunggu' => Booking::whereIn('status_booking', ['menunggu', 'antre'])->count(),
            'n_siap' => Booking::where('status_booking', 'disiapkan')->count(),
        ]);
    }

    public function siapkan(Booking $booking)
    {
        $res = Sirkulasi::siapkan($booking, Auth::user());
        return back()->with('toast', $res['message']);
    }

    public function batal(Booking $booking)
    {
        $res = Sirkulasi::batalBooking($booking, Auth::user());
        return back()->with('toast', $res['message']);
    }

    // ---------- Denda ----------
    public function denda(Request $r)
    {
        $tab = $r->query('tab') === 'lunas' ? 'lunas' : 'belum';
        $items = Denda::with('peminjaman.anggota.user', 'peminjaman.eksemplar.buku')
            ->where('status_pembayaran', $tab === 'lunas' ? 'lunas' : 'belum_lunas')->orderByDesc('id')->paginate(15)->withQueryString();

        $tarif = PengaturanPerpustakaan::cfg()->tarif_denda_per_hari;
        $berjalan = Peminjaman::with('anggota.user', 'eksemplar.buku')->where('status_peminjaman', 'dipinjam')
            ->where('tgl_tenggat', '<', Carbon::today()->toDateString())->orderBy('tgl_tenggat')->get();

        return view('petugas.denda', compact('items', 'tab', 'tarif', 'berjalan') + ['total' => (int) Denda::where('status_pembayaran', 'belum_lunas')->sum('total_denda')]);
    }

    public function bayar(Denda $denda)
    {
        $res = Sirkulasi::bayarDenda($denda, Auth::user());
        return back()->with('toast', $res['message']);
    }

    // ---------- Anggota (verifikasi, hanya lihat) ----------
    public function anggota(Request $r)
    {
        $q = Anggota::with('user')->withCount(['peminjaman as aktif_count' => fn ($x) => $x->where('status_peminjaman', 'dipinjam')])->orderBy('no_anggota');
        if ($s = trim((string) $r->query('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('no_anggota', 'like', "%$s%")->orWhereHas('user', fn ($x) => $x->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
            });
        }
        return view('petugas.anggota', ['items' => $q->paginate(15)->withQueryString(), 'q' => $s ?? '']);
    }

    public function anggotaShow(Anggota $anggota)
    {
        $anggota->load('user');
        $riwayat = Peminjaman::with('eksemplar.buku')->where('anggota_id', $anggota->id)->where('status_peminjaman', 'dikembalikan')->orderByDesc('id')->limit(10)->get();
        return view('petugas.anggota-show', ['a' => $anggota, 's' => ScanController::summary($anggota), 'riwayat' => $riwayat]);
    }
}
