<?php

namespace Database\Seeders;

use App\Models\{Anggota, Booking, Buku, Denda, EksemplarBuku, Kategori, Pengembalian, PengaturanPerpustakaan, Peminjaman, User};
use App\Support\DummyData;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // buku yang tampil di "Book Recomendation" (urutan = urutan di rak) dan sampul wakil tiap kategori
    private const REKOMENDASI = ['What It Takes', 'Laut Bercerita', 'Introvert on the Job', "Man's Search for Meaning", 'Sang Alkemis', 'Rumah: Sebuah Novel'];
    private const WAKIL = ['Romance' => 'Tarot Scammer', 'Fantasy' => 'The Full Moon Eye Clinic', 'History' => 'Taipan', 'Mystery' => 'Teka-Teki Gambar Aneh', 'Self Growth' => 'Introvert on the Job', 'Horror' => 'Lewat Tengah Malam'];

    public function run(): void
    {
        PengaturanPerpustakaan::create(['durasi_pinjam_hari' => 7, 'maks_buku_dipinjam' => 3, 'tarif_denda_per_hari' => 1000]);

        $kat = [];
        foreach (DummyData::categories() as $i => $nama) {
            $kat[$nama] = Kategori::create(['nama_kategori' => $nama, 'urutan' => $i + 1])->id;
        }

        foreach (DummyData::books() as $b) {
            $idx = array_search($b['title'], self::REKOMENDASI, true);
            $buku = Buku::create([
                'kategori_id' => $kat[$b['cat']], 'judul' => $b['title'], 'penulis' => $b['author'],
                'sinopsis' => $b['synopsis'], 'sampul' => $b['cover'], 'tags' => $b['tags'],
                'lokasi_rak' => str_replace('Rak ', '', $b['shelf']), 'rating' => $b['rating'],
                'rekomendasi' => $idx !== false, 'urutan_rekomendasi' => $idx === false ? 0 : $idx + 1,
            ]);
            for ($i = 1; $i <= $b['total']; $i++) {
                EksemplarBuku::create([
                    'buku_id' => $buku->id, 'kode_eksemplar' => sprintf('DGP-%03d-%02d', $buku->id, $i),
                    'status' => $i <= $b['total'] - $b['available'] ? 'dipinjam' : 'tersedia',
                ]);
            }
        }
        foreach (self::WAKIL as $cat => $judul) {
            Kategori::where('nama_kategori', $cat)->update(['buku_wakil_id' => Buku::where('judul', $judul)->value('id')]);
        }

        // ---- Akun bawaan (kata sandi: env SEED_PASSWORD, bawaan "digipus123") ----
        $pw = env('SEED_PASSWORD', 'digipus123');
        $akun = fn (string $nama, string $user, string $role, array $extra = []) => User::create(array_merge([
            'name' => $nama, 'username' => $user, 'email' => "$user@digipus.test", 'password' => $pw,
            'role' => $role, 'email_verified_at' => now(),
        ], $extra));

        $admin = $akun('Administrator', 'admin', 'administrator');
        $akun('Petugas Perpustakaan', 'petugas', 'petugas');
        $demo = $akun('Demo Anggota', 'demo', 'anggota');           // akun uji coba anggota
        $akun('Tamu Pengunjung', 'tamu', 'pengunjung');             // akun uji coba pengunjung (belum anggota)
        $akun('Calon Anggota', 'calon', 'pengunjung', ['pengajuan_at' => now()->subHours(3)]); // contoh pengajuan keanggotaan

        $a = Anggota::create([
            'user_id' => $demo->id, 'no_anggota' => 'DP-2026-0142', 'tgl_daftar' => now(),
            'berlaku_sampai' => now()->addYears(2), 'didaftarkan_oleh' => $admin->id,
        ]);

        // ---- Transaksi contoh untuk akun demo ----
        $eks = fn ($judul) => Buku::where('judul', $judul)->first()->eksemplar()->first()->id;
        Peminjaman::create(['anggota_id' => $a->id, 'eksemplar_id' => $eks('The Full Moon Eye Clinic'), 'tgl_pinjam' => now()->subDays(3), 'tgl_tenggat' => now()->addDays(4)]);
        $p = Peminjaman::create(['anggota_id' => $a->id, 'eksemplar_id' => $eks('Tarot Scammer'), 'tgl_pinjam' => now()->subDays(26), 'tgl_tenggat' => now()->subDays(19), 'status_peminjaman' => 'dikembalikan']);
        Pengembalian::create(['peminjaman_id' => $p->id, 'tgl_kembali' => now()->subDays(7)]);
        Denda::create(['peminjaman_id' => $p->id, 'jumlah_hari_terlambat' => 12, 'tarif_per_hari' => 1000, 'total_denda' => 12000]);
        Booking::create(['anggota_id' => $a->id, 'buku_id' => Buku::where('judul', 'Teka-Teki Gambar Aneh')->value('id'), 'tgl_booking' => now(), 'status_booking' => 'menunggu']);
    }
}
