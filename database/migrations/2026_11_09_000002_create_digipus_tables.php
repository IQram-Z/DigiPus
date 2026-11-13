<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sesuai ERD SRS DigiPus. PK memakai "id" standar Laravel.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $t) {
            $t->id();
            $t->string('nama_kategori')->unique();
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('buku', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kategori_id')->constrained('kategori');
            $t->string('judul');
            $t->string('penulis');
            $t->string('penerbit')->nullable();
            $t->unsignedSmallInteger('tahun_terbit')->nullable();
            $t->string('isbn', 20)->nullable();
            $t->text('sinopsis')->nullable();
            $t->string('tags')->nullable();                 // dipisah koma: "Fiksi,Sejarah"
            $t->string('sampul')->nullable();               // path relatif di /public, mis. covers/x.jpg
            $t->string('lokasi_rak')->nullable();
            $t->decimal('rating', 2, 1)->default(4.5);
            $t->boolean('rekomendasi')->default(false);     // tampil di "Book Recomendation"
            $t->unsignedSmallInteger('urutan_rekomendasi')->default(0);
            $t->timestamps();
        });

        // sampul wakil tiap kategori (tile "Book Category"); ditambahkan setelah tabel buku ada
        Schema::table('kategori', function (Blueprint $t) {
            $t->foreignId('buku_wakil_id')->nullable()->constrained('buku')->nullOnDelete();
        });

        Schema::create('eksemplar_buku', function (Blueprint $t) {
            $t->id();
            $t->foreignId('buku_id')->constrained('buku')->cascadeOnDelete();
            $t->string('kode_eksemplar')->unique();         // isi QR buku
            $t->enum('status', ['tersedia', 'dibooking', 'dipinjam', 'hilang'])->default('tersedia');
            $t->timestamps();
        });

        Schema::create('anggota', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('no_anggota')->unique();             // isi QR kartu anggota
            $t->string('foto')->nullable();
            $t->string('status_keanggotaan')->default('aktif');   // aktif | nonaktif | blokir
            $t->date('tgl_daftar');
            $t->date('berlaku_sampai')->nullable();
            $t->foreignId('didaftarkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('booking', function (Blueprint $t) {
            $t->id();
            $t->foreignId('anggota_id')->constrained('anggota');
            $t->foreignId('buku_id')->constrained('buku');
            $t->foreignId('eksemplar_id')->nullable()->constrained('eksemplar_buku');
            $t->foreignId('petugas_id')->nullable()->constrained('users');
            $t->dateTime('tgl_booking');
            $t->enum('status_booking', ['menunggu', 'antre', 'disiapkan', 'diambil', 'dibatalkan'])->default('menunggu');
            $t->timestamps();
            $t->index('status_booking');
        });

        Schema::create('peminjaman', function (Blueprint $t) {
            $t->id();
            $t->foreignId('anggota_id')->constrained('anggota');
            $t->foreignId('eksemplar_id')->constrained('eksemplar_buku');
            $t->foreignId('booking_id')->nullable()->constrained('booking');
            $t->foreignId('petugas_id')->nullable()->constrained('users');
            $t->date('tgl_pinjam');
            $t->date('tgl_tenggat');
            $t->enum('status_peminjaman', ['dipinjam', 'dikembalikan', 'terlambat'])->default('dipinjam');
            $t->timestamps();
            $t->index('status_peminjaman');
        });

        Schema::create('pengembalian', function (Blueprint $t) {
            $t->id();
            $t->foreignId('peminjaman_id')->unique()->constrained('peminjaman');
            $t->foreignId('petugas_id')->nullable()->constrained('users');
            $t->date('tgl_kembali');
            $t->timestamps();
        });

        Schema::create('denda', function (Blueprint $t) {
            $t->id();
            $t->foreignId('peminjaman_id')->unique()->constrained('peminjaman');
            $t->foreignId('petugas_id')->nullable()->constrained('users');
            $t->unsignedInteger('jumlah_hari_terlambat');
            $t->unsignedInteger('tarif_per_hari');
            $t->unsignedInteger('total_denda');
            $t->enum('status_pembayaran', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $t->date('tgl_bayar')->nullable();
            $t->timestamps();
        });

        Schema::create('pengaturan_perpustakaan', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('durasi_pinjam_hari')->default(7);
            $t->unsignedSmallInteger('maks_buku_dipinjam')->default(3);
            $t->unsignedInteger('tarif_denda_per_hari')->default(1000);
            $t->boolean('blokir_pinjam_jika_denda')->default(true);
            // isi dashboard yang bisa diubah admin
            $t->string('judul_dashboard')->default('Discover');
            $t->string('judul_rekomendasi')->default('Book Recomendation');
            $t->string('judul_kategori')->default('Book Category');
            $t->string('judul_koleksi')->default('Book Collection');
            $t->unsignedTinyInteger('max_rekomendasi')->default(8);
            $t->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('aktivitas');
            $t->timestamp('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::table('kategori', function (Blueprint $t) {
            $t->dropForeign(['buku_wakil_id']);
            $t->dropColumn('buku_wakil_id');
        });
        foreach (['log_aktivitas', 'pengaturan_perpustakaan', 'denda', 'pengembalian', 'peminjaman', 'booking', 'anggota', 'eksemplar_buku', 'buku', 'kategori'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
