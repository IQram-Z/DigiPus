<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Booking, Buku, EksemplarBuku, Kategori, Peminjaman};
use App\Support\Sirkulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** Pengelolaan data buku & eksemplar (SRS DGP-017). */
class BukuController extends Controller
{
    private function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori,id', 'judul' => 'required|string|max:150', 'penulis' => 'required|string|max:120',
            'penerbit' => 'nullable|string|max:120', 'tahun_terbit' => 'nullable|integer|min:1000|max:2100', 'isbn' => 'nullable|string|max:20',
            'tags' => 'nullable|string|max:120', 'lokasi_rak' => 'nullable|string|max:20', 'rating' => 'nullable|numeric|min:0|max:9.9',
            'sinopsis' => 'nullable|string|max:5000', 'urutan_rekomendasi' => 'nullable|integer|min:0|max:999',
            'sampul' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }

    private function simpanSampul(Request $r, string $judul): ?string
    {
        if (! $r->hasFile('sampul')) return null;
        $file = $r->file('sampul');
        $name = Str::slug($judul) . '-' . time() . '.' . $file->extension();
        $file->move(public_path('covers'), $name);
        return 'covers/' . $name;
    }

    private function tambahEksemplar(Buku $buku, int $n): void
    {
        $count = $buku->eksemplar()->count();
        for ($i = 0; $i < $n; $i++) {
            do { $count++; $kode = sprintf('DGP-%03d-%02d', $buku->id, $count); } while (EksemplarBuku::where('kode_eksemplar', $kode)->exists());
            EksemplarBuku::create(['buku_id' => $buku->id, 'kode_eksemplar' => $kode, 'status' => 'tersedia']);
        }
    }

    public function index(Request $r)
    {
        $q = Buku::with('kategori')->withCount(['eksemplar as total', 'eksemplar as tersedia' => fn ($x) => $x->where('status', 'tersedia')])->orderByDesc('id');
        if ($s = trim((string) $r->query('q'))) $q->where(fn ($w) => $w->where('judul', 'like', "%$s%")->orWhere('penulis', 'like', "%$s%")->orWhere('isbn', 'like', "%$s%"));
        if ($k = $r->query('kategori')) $q->where('kategori_id', (int) $k);

        return view('admin.buku.index', ['items' => $q->paginate(12)->withQueryString(), 'q' => $s ?? '', 'kat' => $k, 'kategori' => Kategori::orderBy('urutan')->get()]);
    }

    public function create()
    {
        return view('admin.buku.form', ['buku' => new Buku(['rating' => 4.5]), 'kategori' => Kategori::orderBy('urutan')->get()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->rules() + ['jumlah' => 'nullable|integer|min:0|max:50']);
        $buku = Buku::create([
            'kategori_id' => $data['kategori_id'], 'judul' => $data['judul'], 'penulis' => $data['penulis'], 'penerbit' => $data['penerbit'] ?? null,
            'tahun_terbit' => $data['tahun_terbit'] ?? null, 'isbn' => $data['isbn'] ?? null, 'tags' => $data['tags'] ?? null,
            'lokasi_rak' => $data['lokasi_rak'] ?? null, 'rating' => $data['rating'] ?? 4.5, 'sinopsis' => $data['sinopsis'] ?? null,
            'sampul' => $this->simpanSampul($r, $data['judul']), 'rekomendasi' => $r->boolean('rekomendasi'),
            'urutan_rekomendasi' => $data['urutan_rekomendasi'] ?? 0,
        ]);
        $this->tambahEksemplar($buku, (int) ($data['jumlah'] ?? 1));

        Sirkulasi::log(Auth::user(), "Tambah buku: {$buku->judul}");
        return redirect()->route('admin.buku.edit', $buku)->with('toast', 'Buku ditambahkan. Cetak QR eksemplar lewat menu Cetak QR.');
    }

    public function edit(Buku $buku)
    {
        return view('admin.buku.form', ['buku' => $buku->load('eksemplar'), 'kategori' => Kategori::orderBy('urutan')->get()]);
    }

    public function update(Request $r, Buku $buku)
    {
        $data = $r->validate($this->rules());
        $buku->fill(collect($data)->except(['sampul'])->all());
        $buku->rekomendasi = $r->boolean('rekomendasi');
        if ($sampul = $this->simpanSampul($r, $data['judul'])) $buku->sampul = $sampul;
        $buku->save();

        Sirkulasi::log(Auth::user(), "Ubah buku: {$buku->judul}");
        return back()->with('toast', 'Data buku disimpan.');
    }

    public function destroy(Buku $buku)
    {
        $dipakai = Peminjaman::whereIn('eksemplar_id', $buku->eksemplar()->pluck('id'))->exists() || Booking::where('buku_id', $buku->id)->exists();
        if ($dipakai) return back()->with('toast', 'Buku tidak bisa dihapus karena sudah punya riwayat peminjaman/booking.');

        $judul = $buku->judul;
        $buku->delete();
        Sirkulasi::log(Auth::user(), "Hapus buku: $judul");
        return redirect()->route('admin.buku.index')->with('toast', "Buku \"$judul\" dihapus.");
    }

    // ---------- Eksemplar ----------
    public function tambah(Request $r, Buku $buku)
    {
        $n = (int) $r->validate(['jumlah' => 'required|integer|min:1|max:50'])['jumlah'];
        $this->tambahEksemplar($buku, $n);
        Sirkulasi::log(Auth::user(), "Tambah $n eksemplar: {$buku->judul}");
        return back()->with('toast', "$n eksemplar ditambahkan.");
    }

    public function ubahEksemplar(Request $r, EksemplarBuku $eksemplar)
    {
        $status = $r->validate(['status' => 'required|in:tersedia,hilang'])['status'];
        if (in_array($eksemplar->status, ['dipinjam', 'dibooking'], true)) {
            return back()->with('toast', "Eksemplar {$eksemplar->kode_eksemplar} sedang {$eksemplar->status}; selesaikan transaksinya dulu.");
        }
        $eksemplar->update(['status' => $status]);
        return back()->with('toast', "Status {$eksemplar->kode_eksemplar} diubah menjadi $status.");
    }

    public function hapusEksemplar(EksemplarBuku $eksemplar)
    {
        if (Peminjaman::where('eksemplar_id', $eksemplar->id)->exists() || Booking::where('eksemplar_id', $eksemplar->id)->exists() || in_array($eksemplar->status, ['dipinjam', 'dibooking'], true)) {
            return back()->with('toast', 'Eksemplar ini punya riwayat atau sedang dipakai. Tandai "hilang" saja.');
        }
        $kode = $eksemplar->kode_eksemplar;
        $eksemplar->delete();
        return back()->with('toast', "Eksemplar $kode dihapus.");
    }
}
