<?php

namespace App\Support;

use App\Models\{Booking, Denda, Peminjaman, Pengembalian};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Data laporan statistik (SRS DGP-015, DGP-022): periode harian / bulanan / tahunan. */
class Report
{
    public static function data(string $periode): array
    {
        $today = Carbon::today();
        $buckets = []; // key => label

        if ($periode === 'bulanan') {
            $keyFmt = 'Y-m'; $judul = '12 bulan terakhir';
            for ($i = 11; $i >= 0; $i--) { $d = $today->copy()->startOfMonth()->subMonths($i); $buckets[$d->format($keyFmt)] = $d->format('m/y'); }
            $from = $today->copy()->startOfMonth()->subMonths(11);
        } elseif ($periode === 'tahunan') {
            $keyFmt = 'Y'; $judul = '5 tahun terakhir';
            for ($i = 4; $i >= 0; $i--) { $d = $today->copy()->startOfYear()->subYears($i); $buckets[$d->format($keyFmt)] = $d->format('Y'); }
            $from = $today->copy()->startOfYear()->subYears(4);
        } else {
            $periode = 'harian'; $keyFmt = 'Y-m-d'; $judul = '14 hari terakhir';
            for ($i = 13; $i >= 0; $i--) { $d = $today->copy()->subDays($i); $buckets[$d->format($keyFmt)] = $d->format('d/m'); }
            $from = $today->copy()->subDays(13);
        }

        $loans = array_fill_keys(array_keys($buckets), 0);
        $returns = $loans;
        foreach (Peminjaman::where('tgl_pinjam', '>=', $from->toDateString())->pluck('tgl_pinjam') as $d) {
            $k = Carbon::parse($d)->format($keyFmt); if (isset($loans[$k])) $loans[$k]++;
        }
        foreach (Pengembalian::where('tgl_kembali', '>=', $from->toDateString())->pluck('tgl_kembali') as $d) {
            $k = Carbon::parse($d)->format($keyFmt); if (isset($returns[$k])) $returns[$k]++;
        }

        $populer = DB::table('peminjaman')
            ->join('eksemplar_buku', 'eksemplar_buku.id', '=', 'peminjaman.eksemplar_id')
            ->join('buku', 'buku.id', '=', 'eksemplar_buku.buku_id')
            ->where('peminjaman.tgl_pinjam', '>=', $from->toDateString())
            ->groupBy('buku.id', 'buku.judul', 'buku.penulis')
            ->orderByDesc('total')->limit(10)
            ->get(['buku.judul', 'buku.penulis', DB::raw('count(*) as total')]);

        $aktif = DB::table('peminjaman')
            ->join('anggota', 'anggota.id', '=', 'peminjaman.anggota_id')
            ->join('users', 'users.id', '=', 'anggota.user_id')
            ->where('peminjaman.tgl_pinjam', '>=', $from->toDateString())
            ->groupBy('anggota.id', 'users.name', 'anggota.no_anggota')
            ->orderByDesc('total')->limit(10)
            ->get(['users.name', 'anggota.no_anggota', DB::raw('count(*) as total')]);

        return [
            'periode' => $periode, 'judul' => $judul, 'from' => $from,
            'labels' => array_values($buckets), 'loans' => array_values($loans), 'returns' => array_values($returns),
            'total_pinjam' => array_sum($loans), 'total_kembali' => array_sum($returns),
            'total_booking' => Booking::where('tgl_booking', '>=', $from->toDateString())->count(),
            'total_denda' => (int) Denda::where('created_at', '>=', $from->toDateString())->sum('total_denda'),
            'populer' => $populer, 'anggota_aktif' => $aktif,
        ];
    }
}
