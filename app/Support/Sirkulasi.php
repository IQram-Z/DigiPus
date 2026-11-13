<?php

namespace App\Support;

use App\Models\{Anggota, Booking, Denda, EksemplarBuku, LogAktivitas, PengaturanPerpustakaan, Peminjaman, Pengembalian, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aturan sirkulasi perpustakaan (SRS DGP-005, 010-014): booking, peminjaman, pengembalian, denda.
 * Dipakai bersama oleh ScanController (QR) dan PetugasController (tombol manual).
 * Setiap method mengembalikan ['ok' => bool, 'status' => int, 'message' => string].
 */
class Sirkulasi
{
    public const BOOKING_AKTIF = ['menunggu', 'antre', 'disiapkan'];

    private static function ok(string $msg): array { return ['ok' => true, 'status' => 200, 'message' => $msg]; }
    private static function fail(string $msg, int $status = 422): array { return ['ok' => false, 'status' => $status, 'message' => $msg]; }

    public static function rupiah(int $n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }

    public static function log(?User $u, string $text): void
    {
        LogAktivitas::create(['user_id' => $u?->id, 'aktivitas' => $text]);
    }

    public static function dendaBelumLunas(Anggota $a): int
    {
        return (int) Denda::where('status_pembayaran', 'belum_lunas')
            ->whereHas('peminjaman', fn ($q) => $q->where('anggota_id', $a->id))->sum('total_denda');
    }

    public static function hariTerlambat(Peminjaman $p): int
    {
        return max(0, (int) Carbon::parse($p->tgl_tenggat)->startOfDay()->diffInDays(Carbon::today(), false));
    }

    /** Booking oleh anggota (SRS DGP-005). Eksemplar baru ditentukan petugas saat "siapkan". */
    public static function booking(Anggota $a, \App\Models\Buku $buku): array
    {
        if ($a->status_keanggotaan !== 'aktif') return self::fail('Keanggotaan kamu tidak aktif. Hubungi petugas perpustakaan.', 403);
        if (Booking::where('anggota_id', $a->id)->where('buku_id', $buku->id)->whereIn('status_booking', self::BOOKING_AKTIF)->exists()) {
            return self::fail('Kamu sudah mem-booking buku ini.');
        }

        $set = PengaturanPerpustakaan::cfg();
        $terpakai = Booking::where('anggota_id', $a->id)->whereIn('status_booking', self::BOOKING_AKTIF)->count()
            + Peminjaman::where('anggota_id', $a->id)->where('status_peminjaman', 'dipinjam')->count();
        if ($terpakai >= $set->maks_buku_dipinjam) return self::fail("Batas peminjaman tercapai ({$set->maks_buku_dipinjam} buku).");

        $denda = self::dendaBelumLunas($a);
        if ($set->blokir_pinjam_jika_denda && $denda > 0) {
            return self::fail('Kamu masih punya denda belum lunas ' . self::rupiah($denda) . '. Lunasi di petugas dulu.');
        }

        $ada = $buku->eksemplar()->where('status', 'tersedia')->exists();
        Booking::create(['anggota_id' => $a->id, 'buku_id' => $buku->id, 'tgl_booking' => now(), 'status_booking' => $ada ? 'menunggu' : 'antre']);
        self::log($a->user, 'Booking buku: ' . $buku->judul);

        return self::ok($ada
            ? "Booking \"{$buku->judul}\" diajukan. Tunggu petugas menyiapkan bukunya."
            : "Stok habis. Kamu masuk antrean \"{$buku->judul}\".");
    }

    /** Petugas menyiapkan eksemplar untuk booking (DGP-010). */
    public static function siapkan(Booking $b, User $petugas): array
    {
        if (! in_array($b->status_booking, ['menunggu', 'antre'], true)) return self::fail("Booking ini tidak bisa disiapkan (status: {$b->status_booking}).");

        $eks = EksemplarBuku::with('buku')->where('buku_id', $b->buku_id)->where('status', 'tersedia')->orderBy('id')->first();
        if (! $eks) return self::fail('Belum ada eksemplar yang tersedia untuk buku ini.');

        DB::transaction(function () use ($b, $eks, $petugas) {
            $eks->update(['status' => 'dibooking']);
            $b->update(['eksemplar_id' => $eks->id, 'status_booking' => 'disiapkan', 'petugas_id' => $petugas->id]);
            self::log($petugas, "Siapkan booking #{$b->id}: {$eks->buku->judul} ({$eks->kode_eksemplar})");
        });

        return self::ok("Buku disiapkan. Ambil eksemplar {$eks->kode_eksemplar} di Rak {$eks->buku->lokasi_rak}.");
    }

    /** Batalkan booking (oleh petugas atau anggota sendiri). */
    public static function batalBooking(Booking $b, ?User $oleh): array
    {
        if (! in_array($b->status_booking, self::BOOKING_AKTIF, true)) return self::fail('Booking ini sudah selesai atau dibatalkan.');

        DB::transaction(function () use ($b, $oleh) {
            if ($b->eksemplar_id) EksemplarBuku::where('id', $b->eksemplar_id)->where('status', 'dibooking')->update(['status' => 'tersedia']);
            $b->update(['status_booking' => 'dibatalkan']);
            self::log($oleh, "Batalkan booking #{$b->id}");
        });

        return self::ok('Booking dibatalkan.');
    }

    /** Petugas mencatat peminjaman setelah memverifikasi anggota & buku (DGP-011). */
    public static function pinjam(Anggota $a, EksemplarBuku $eks, User $petugas): array
    {
        $eks->loadMissing('buku');

        if ($a->status_keanggotaan !== 'aktif') return self::fail('Keanggotaan tidak aktif.');
        if ($a->berlaku_sampai && Carbon::today()->gt($a->berlaku_sampai)) return self::fail('Masa berlaku kartu anggota sudah habis. Perpanjang lewat admin.');
        if (in_array($eks->status, ['dipinjam', 'hilang'], true)) return self::fail("Eksemplar ini berstatus {$eks->status}, tidak bisa dipinjamkan.");
        if ($eks->status === 'dibooking' && ! Booking::where('eksemplar_id', $eks->id)->where('anggota_id', $a->id)->where('status_booking', 'disiapkan')->exists()) {
            return self::fail('Eksemplar ini sedang disiapkan untuk anggota lain.');
        }

        $set = PengaturanPerpustakaan::cfg();
        $aktif = Peminjaman::where('anggota_id', $a->id)->where('status_peminjaman', 'dipinjam')->count();
        if ($aktif >= $set->maks_buku_dipinjam) return self::fail("Batas peminjaman tercapai ({$set->maks_buku_dipinjam} buku).");

        $denda = self::dendaBelumLunas($a);
        if ($set->blokir_pinjam_jika_denda && $denda > 0) {
            return self::fail('Anggota masih punya denda belum lunas ' . self::rupiah($denda) . '. Lunasi dulu di menu Denda.');
        }

        $tenggat = Carbon::today()->addDays($set->durasi_pinjam_hari);
        DB::transaction(function () use ($a, $eks, $petugas, $tenggat) {
            $booking = Booking::where('anggota_id', $a->id)->where('buku_id', $eks->buku_id)->whereIn('status_booking', self::BOOKING_AKTIF)->first();
            if ($booking && $booking->eksemplar_id && $booking->eksemplar_id !== $eks->id) {
                EksemplarBuku::where('id', $booking->eksemplar_id)->where('status', 'dibooking')->update(['status' => 'tersedia']);
            }
            Peminjaman::create([
                'anggota_id' => $a->id, 'eksemplar_id' => $eks->id, 'booking_id' => $booking?->id, 'petugas_id' => $petugas->id,
                'tgl_pinjam' => Carbon::today(), 'tgl_tenggat' => $tenggat, 'status_peminjaman' => 'dipinjam',
            ]);
            $booking?->update(['status_booking' => 'diambil', 'eksemplar_id' => $eks->id, 'petugas_id' => $petugas->id]);
            $eks->update(['status' => 'dipinjam']);
            self::log($petugas, "Pinjam: {$eks->buku->judul} ({$eks->kode_eksemplar}) -> {$a->no_anggota}");
        });

        return self::ok("\"{$eks->buku->judul}\" dipinjamkan ke {$a->user->name}. Tenggat pengembalian {$tenggat->format('d/m/Y')}.");
    }

    /** Pengembalian + denda otomatis bila lewat tenggat (DGP-012, 013). */
    public static function kembali(Peminjaman $p, User $petugas): array
    {
        if ($p->status_peminjaman !== 'dipinjam') return self::fail('Peminjaman ini sudah selesai.');
        $p->loadMissing('eksemplar.buku');

        $tarif = PengaturanPerpustakaan::cfg()->tarif_denda_per_hari;
        $hari = self::hariTerlambat($p);

        DB::transaction(function () use ($p, $petugas, $hari, $tarif) {
            Pengembalian::create(['peminjaman_id' => $p->id, 'petugas_id' => $petugas->id, 'tgl_kembali' => Carbon::today()]);
            $p->update(['status_peminjaman' => 'dikembalikan']);
            $p->eksemplar->update(['status' => 'tersedia']);
            if ($hari > 0) {
                Denda::create(['peminjaman_id' => $p->id, 'petugas_id' => $petugas->id, 'jumlah_hari_terlambat' => $hari, 'tarif_per_hari' => $tarif, 'total_denda' => $hari * $tarif]);
            }
            self::log($petugas, "Kembali: {$p->eksemplar->buku->judul} ({$p->eksemplar->kode_eksemplar})");
        });

        $msg = "\"{$p->eksemplar->buku->judul}\" berhasil dikembalikan.";
        if ($hari > 0) $msg .= " Terlambat $hari hari, denda " . self::rupiah($hari * $tarif) . '.';
        return self::ok($msg);
    }

    /** Dipakai scan QR: cari peminjaman aktif dari kode eksemplar. */
    public static function kembaliByEksemplar(EksemplarBuku $eks, User $petugas): array
    {
        $p = Peminjaman::where('eksemplar_id', $eks->id)->where('status_peminjaman', 'dipinjam')->latest('id')->first();
        if ($p) return self::kembali($p, $petugas);

        if ($eks->status === 'dipinjam') { // data contoh tanpa catatan peminjaman
            $eks->update(['status' => 'tersedia']);
            self::log($petugas, "Reset status eksemplar {$eks->kode_eksemplar}");
            return self::ok("Tidak ada catatan peminjaman untuk {$eks->kode_eksemplar}; status eksemplar diubah menjadi tersedia.");
        }
        return self::fail('Eksemplar ini tidak sedang dipinjam.');
    }

    public static function bayarDenda(Denda $d, User $petugas): array
    {
        if ($d->status_pembayaran === 'lunas') return self::fail('Denda ini sudah lunas.');
        $d->update(['status_pembayaran' => 'lunas', 'tgl_bayar' => Carbon::today(), 'petugas_id' => $petugas->id]);
        self::log($petugas, "Denda #{$d->id} lunas (" . self::rupiah($d->total_denda) . ')');
        return self::ok('Pembayaran denda ' . self::rupiah($d->total_denda) . ' dicatat lunas.');
    }
}
