<?php

namespace App\Http\Controllers;

use App\Models\{Booking, Buku, Denda, Kategori, PengaturanPerpustakaan, Peminjaman};
use Illuminate\Support\Facades\Auth;

/** Dashboard pengunjung & anggota (desain Figma). Petugas/admin diarahkan ke panel masing-masing. */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isStaff()) return redirect()->route($user->homeRoute());

        $set = PengaturanPerpustakaan::cfg();
        $anggota = $user->isAnggota() ? $user->anggota : null;

        $books = Buku::with('kategori')
            ->withCount(['eksemplar as total', 'eksemplar as available' => fn ($q) => $q->where('status', 'tersedia')])
            ->orderBy('id')->get();

        $mapped = $books->map(fn ($b) => [
            'id' => $b->id, 'title' => $b->judul, 'author' => $b->penulis, 'cat' => $b->kategori->nama_kategori,
            'tags' => $b->tagList(), 'cover' => $b->coverUrl(), 'total' => $b->total, 'available' => $b->available,
            'shelf' => 'Rak ' . $b->lokasi_rak, 'synopsis' => $b->sinopsis, 'rating' => number_format((float) $b->rating, 1),
        ])->values();
        $byId = $mapped->keyBy('id');

        // Book Recomendation: diatur admin (menu Tampilan Dashboard)
        $rekIds = $books->where('rekomendasi', true)->sortBy([['urutan_rekomendasi', 'asc'], ['id', 'asc']])->take($set->max_rekomendasi)->pluck('id');
        $featured = $rekIds->map(fn ($id) => $byId->get($id))->filter()->values()->all();

        // Book Category: sampul wakil diatur admin, bila kosong pakai buku pertama di kategori itu
        $categories = Kategori::orderBy('urutan')->orderBy('id')->get()->map(function ($k) use ($books, $byId) {
            $wid = $k->buku_wakil_id ?: optional($books->firstWhere('kategori_id', $k->id))->id;
            $book = $wid ? $byId->get($wid) : null;
            return $book ? ['name' => $k->nama_kategori, 'book' => $book] : null;
        })->filter()->values()->all();

        $stats = ['dipinjam' => 0, 'booking' => 0, 'denda' => 0];
        if ($anggota) {
            $stats = [
                'dipinjam' => Peminjaman::where('anggota_id', $anggota->id)->where('status_peminjaman', 'dipinjam')->count(),
                'booking' => Booking::where('anggota_id', $anggota->id)->whereIn('status_booking', ['menunggu', 'antre', 'disiapkan'])->count(),
                'denda' => (int) Denda::where('status_pembayaran', 'belum_lunas')->whereHas('peminjaman', fn ($q) => $q->where('anggota_id', $anggota->id))->sum('total_denda'),
            ];
        }

        return view('dashboard', [
            'user' => $user, 'isMember' => (bool) $anggota, 'set' => $set,
            'books' => $mapped->all(), 'featured' => $featured, 'categories' => $categories, 'stats' => $stats,
        ]);
    }
}
