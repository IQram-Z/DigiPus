<?php

namespace App\Http\Controllers;

use App\Support\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Laporan statistik (SRS DGP-015 untuk petugas, DGP-022 untuk administrator). */
class LaporanController extends Controller
{
    public function index(Request $r)
    {
        $d = Report::data((string) $r->query('p', 'harian'));

        if ($r->query('export') === 'csv') {
            return response()->streamDownload(function () use ($d) {
                $o = fopen('php://output', 'w');
                fwrite($o, "\xEF\xBB\xBF");
                fputcsv($o, ['Laporan DigiPus', $d['judul']]);
                fputcsv($o, []);
                fputcsv($o, ['Periode', 'Peminjaman', 'Pengembalian']);
                foreach ($d['labels'] as $i => $l) fputcsv($o, [$l, $d['loans'][$i], $d['returns'][$i]]);
                fputcsv($o, []);
                fputcsv($o, ['Buku terpopuler', 'Penulis', 'Jumlah dipinjam']);
                foreach ($d['populer'] as $x) fputcsv($o, [$x->judul, $x->penulis, $x->total]);
                fputcsv($o, []);
                fputcsv($o, ['Anggota paling aktif', 'No. anggota', 'Jumlah pinjam']);
                foreach ($d['anggota_aktif'] as $x) fputcsv($o, [$x->name, $x->no_anggota, $x->total]);
                fclose($o);
            }, 'laporan-digipus-' . $d['periode'] . '-' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $u = Auth::user();
        return view('laporan.index', ['d' => $d, 'layout' => $u->isAdmin() ? 'layouts.admin' : 'layouts.staff', 'admin' => $u->isAdmin()]);
    }
}
