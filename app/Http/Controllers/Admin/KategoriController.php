<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Support\Sirkulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Pengelolaan kategori buku (SRS DGP-018). */
class KategoriController extends Controller
{
    public function index()
    {
        return view('admin.kategori', ['items' => Kategori::withCount('buku')->orderBy('urutan')->orderBy('id')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate(['nama_kategori' => 'required|string|max:60|unique:kategori,nama_kategori', 'urutan' => 'nullable|integer|min:0|max:999']);
        Kategori::create(['nama_kategori' => $d['nama_kategori'], 'urutan' => $d['urutan'] ?? (Kategori::max('urutan') + 1)]);
        Sirkulasi::log(Auth::user(), "Tambah kategori: {$d['nama_kategori']}");
        return back()->with('toast', 'Kategori ditambahkan.');
    }

    public function update(Request $r, Kategori $kategori)
    {
        $d = $r->validate(['nama_kategori' => 'required|string|max:60|unique:kategori,nama_kategori,' . $kategori->id, 'urutan' => 'nullable|integer|min:0|max:999']);
        $kategori->update(['nama_kategori' => $d['nama_kategori'], 'urutan' => $d['urutan'] ?? $kategori->urutan]);
        return back()->with('toast', 'Kategori disimpan.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->buku()->exists()) return back()->with('toast', 'Kategori masih dipakai buku. Pindahkan bukunya dulu.');
        $kategori->delete();
        return back()->with('toast', 'Kategori dihapus.');
    }
}
