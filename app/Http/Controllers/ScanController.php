<?php

namespace App\Http\Controllers;

use App\Models\{Anggota, Booking, EksemplarBuku, Peminjaman};
use App\Support\Sirkulasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sistem QR DigiPus.
 *  - QR buku  = kode eksemplar, mis. DGP-005-02 (satu QR per eksemplar fisik)
 *  - QR kartu = nomor anggota,  mis. DP-2026-0142
 * Pengunjung/anggota memindai buku (detail; booking khusus anggota) & kartu sendiri.
 * Petugas/admin: pindai kartu anggota -> mulai transaksi; pindai buku -> pinjamkan / terima pengembalian.
 */
class ScanController extends Controller
{
    private function staff(): bool { return Auth::user()->isStaff(); }
    private function err(string $msg, int $code = 422) { return response()->json(['message' => $msg], $code); }
    private static function fmt($date): string { return Carbon::parse($date)->format('d/m/Y'); }

    /** Halaman scan untuk pengunjung & anggota. */
    public function index()
    {
        $u = Auth::user();
        if ($u->isStaff()) return redirect()->route('petugas.transaksi');
        return view('scan.index', ['member' => $u->isAnggota(), 'noAnggota' => $u->anggota?->no_anggota, 'nama' => $u->name, 'berlaku' => $u->anggota?->berlaku_sampai]);
    }

    public function resolve(Request $r)
    {
        $code = strtoupper(trim((string) $r->input('code')));
        if ($code === '') return $this->err('Kode QR kosong.');
        if (preg_match('/DGP-\d{3,}-\d{2,}/', $code, $m)) return $this->book($m[0]);
        if (preg_match('/DP-\d{4}-\d{4}/', $code, $m)) return $this->member($m[0]);
        return $this->err('Kode QR tidak dikenali. Gunakan QR buku (DGP-...) atau kartu anggota (DP-...).');
    }

    private function book(string $code)
    {
        $eks = EksemplarBuku::with('buku.kategori')->where('kode_eksemplar', $code)->first();
        if (! $eks) return $this->err("Eksemplar $code tidak ditemukan.", 404);

        $b = $eks->buku;
        $loan = Peminjaman::with('anggota.user')->where('eksemplar_id', $eks->id)->where('status_peminjaman', 'dipinjam')->latest('id')->first();

        return response()->json([
            'type' => 'book',
            'book' => [
                'id' => $b->id, 'title' => $b->judul, 'author' => $b->penulis, 'cat' => $b->kategori->nama_kategori,
                'tags' => $b->tagList(), 'cover' => $b->coverUrl(), 'shelf' => 'Rak ' . $b->lokasi_rak,
                'total' => $b->eksemplar()->count(), 'available' => $b->eksemplar()->where('status', 'tersedia')->count(),
            ],
            'copy' => [
                'code' => $eks->kode_eksemplar, 'status' => $eks->status,
                'borrower' => $this->staff() && $loan ? $loan->anggota->user->name : null,
                'due' => $loan ? self::fmt($loan->tgl_tenggat) : null,
            ],
        ]);
    }

    private function member(string $no)
    {
        $a = Anggota::with('user')->where('no_anggota', $no)->first();
        if (! $a) return $this->err("Anggota $no tidak ditemukan.", 404);
        if (! $this->staff() && $a->user_id !== Auth::id()) {
            return $this->err('Kartu anggota orang lain hanya bisa dipindai oleh petugas.', 403);
        }
        return response()->json(['type' => 'member', 'member' => self::summary($a)]);
    }

    /** Ringkasan anggota (dipakai scan & halaman detail anggota petugas). */
    public static function summary(Anggota $a): array
    {
        $a->loadMissing('user');
        $today = Carbon::today();
        $loans = Peminjaman::with('eksemplar.buku')->where('anggota_id', $a->id)->where('status_peminjaman', 'dipinjam')->get()
            ->map(fn ($p) => [
                'title' => $p->eksemplar->buku->judul, 'code' => $p->eksemplar->kode_eksemplar,
                'due' => self::fmt($p->tgl_tenggat), 'late' => $today->gt(Carbon::parse($p->tgl_tenggat)),
            ])->values();
        $bookings = Booking::with('buku', 'eksemplar')->where('anggota_id', $a->id)->whereIn('status_booking', Sirkulasi::BOOKING_AKTIF)->get()
            ->map(fn ($b) => ['title' => $b->buku->judul, 'status' => $b->status_booking, 'code' => $b->eksemplar?->kode_eksemplar])->values();

        return [
            'no' => $a->no_anggota, 'name' => $a->user->name, 'status' => $a->status_keanggotaan,
            'valid' => $a->berlaku_sampai ? self::fmt($a->berlaku_sampai) : null,
            'loans' => $loans, 'bookings' => $bookings, 'denda' => Sirkulasi::dendaBelumLunas($a),
        ];
    }

    public function pinjam(Request $r)
    {
        if (! $this->staff()) return $this->err('Hanya petugas yang dapat mencatat peminjaman.', 403);
        $data = $r->validate(['member' => 'required|string', 'copy' => 'required|string']);

        $a = Anggota::with('user')->where('no_anggota', $data['member'])->first();
        $eks = EksemplarBuku::with('buku')->where('kode_eksemplar', $data['copy'])->first();
        if (! $a || ! $eks) return $this->err('Data anggota atau eksemplar tidak ditemukan.', 404);

        $res = Sirkulasi::pinjam($a, $eks, Auth::user());
        return response()->json(['message' => $res['message'], 'member' => self::summary($a->fresh('user'))], $res['status']);
    }

    public function kembali(Request $r)
    {
        if (! $this->staff()) return $this->err('Hanya petugas yang dapat mencatat pengembalian.', 403);
        $data = $r->validate(['copy' => 'required|string']);

        $eks = EksemplarBuku::with('buku')->where('kode_eksemplar', $data['copy'])->first();
        if (! $eks) return $this->err('Eksemplar tidak ditemukan.', 404);

        $res = Sirkulasi::kembaliByEksemplar($eks, Auth::user());
        return response()->json(['message' => $res['message']], $res['status']);
    }
}
