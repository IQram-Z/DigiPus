<?php

namespace App\Http\Controllers;

use App\Models\{Anggota, Buku, EksemplarBuku};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Cetak label QR buku (per eksemplar) dan kartu anggota (petugas & admin). */
class QrController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        $buku = Buku::with('kategori', 'eksemplar')->withCount('eksemplar')->orderBy('judul')->get();
        return view('qr.index', ['buku' => $buku, 'layout' => $u->isAdmin() ? 'layouts.admin' : 'layouts.staff']);
    }

    public function labels(Request $r)
    {
        $q = EksemplarBuku::with('buku.kategori')->orderBy('buku_id')->orderBy('kode_eksemplar');
        if (! $r->boolean('all')) {
            $ids = array_map('intval', (array) $r->query('ids', []));
            $q->whereIn('id', $ids ?: [0]);
        }
        $tpl = in_array($r->query('t'), ['standar', 'stiker'], true) ? $r->query('t') : 'standar';
        return view('qr.labels', ['items' => $q->get(), 'tpl' => $tpl]);
    }

    public function kartu(Anggota $anggota)
    {
        return view('qr.kartu', ['a' => $anggota->load('user')]);
    }
}
