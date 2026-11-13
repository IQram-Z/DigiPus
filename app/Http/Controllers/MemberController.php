<?php

namespace App\Http\Controllers;

use App\Models\{Booking, Denda, PengaturanPerpustakaan, Peminjaman};
use App\Support\Sirkulasi;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /** Riwayat, status peminjaman, tenggat, dan denda (SRS DGP-006, DGP-007) - khusus anggota. */
    public function peminjaman()
    {
        $a = Auth::user()->anggota;

        $aktif = Peminjaman::with('eksemplar.buku')->where('anggota_id', $a->id)->where('status_peminjaman', 'dipinjam')->orderBy('tgl_tenggat')->get();
        $booking = Booking::with('buku', 'eksemplar')->where('anggota_id', $a->id)->whereIn('status_booking', Sirkulasi::BOOKING_AKTIF)->orderByDesc('id')->get();
        $denda = Denda::with('peminjaman.eksemplar.buku')->whereHas('peminjaman', fn ($q) => $q->where('anggota_id', $a->id))
            ->orderBy('status_pembayaran')->orderByDesc('id')->get();   // belum_lunas dulu
        $riwayat = Peminjaman::with('eksemplar.buku', 'pengembalian')->where('anggota_id', $a->id)->where('status_peminjaman', 'dikembalikan')->orderByDesc('id')->limit(15)->get();

        return view('member.peminjaman', [
            'a' => $a, 'aktif' => $aktif, 'booking' => $booking, 'denda' => $denda, 'riwayat' => $riwayat,
            'tarif' => PengaturanPerpustakaan::cfg()->tarif_denda_per_hari,
        ]);
    }

    /** Halaman "Jadi Anggota" untuk pengunjung. */
    public function join()
    {
        $user = Auth::user();
        if ($user->isAnggota()) return redirect()->route('scan');
        return view('member.join', ['user' => $user, 'set' => PengaturanPerpustakaan::cfg()]);
    }

    public function ajukan()
    {
        $user = Auth::user();
        abort_if($user->isAnggota(), 403);
        if (! $user->pengajuan_at) {
            $user->update(['pengajuan_at' => now()]);
            Sirkulasi::log($user, 'Mengajukan keanggotaan');
        }
        return back()->with('toast', 'Pengajuan keanggotaan terkirim. Admin akan memverifikasi akunmu.');
    }
}
