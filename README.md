# DigiPus (Laravel 12 + Tailwind v4 + MySQL)

Perpustakaan digital dengan 4 jenis akun: **Pengunjung, Anggota, Petugas, Administrator**, masing-masing punya tampilan sendiri.

## Pasang & jalankan
```
composer install
copy .env.example .env          # lalu: php artisan key:generate
# buat database kosong "digipus" di MySQL, cek DB_USERNAME/DB_PASSWORD di .env
php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000. CSS/JS sudah ter-build di `public/build` (tanpa npm). Ubah tampilan: `npm install && npm run build`.

> Struktur tabel berubah dari versi sebelumnya: **wajib `php artisan migrate:fresh --seed`** (data lama terhapus). Seluruh anggota tim menjalankannya juga setelah `git pull`.
> Hapus file lama bila masih ada: `app/Support/BukuSync.php`, `app/Console/Commands/SyncBuku.php`, `database/seeders/SyncBukuSeeder.php` (database kini sumber data utama; mereka menimpa hasil edit admin).

## Akun bawaan (kata sandi semua: `digipus123`, ubah lewat `SEED_PASSWORD` di .env sebelum seed)
| Username | Peran | Untuk |
|---|---|---|
| `admin` | Administrator | panel admin `/admin` |
| `petugas` | Petugas | panel petugas `/petugas` |
| `demo` | Anggota (DP-2026-0142) | uji coba anggota: ada pinjaman, booking, denda |
| `tamu` | Pengunjung | uji coba pengunjung |
| `calon` | Pengunjung + pengajuan anggota | uji alur "Jadikan anggota" |

## Hak akses per peran
**Pengunjung** (daftar sendiri / Google / Apple): Discover, Book Collection, detail buku, cari & filter, scan QR buku (lihat detail). *Tidak bisa booking*; bisa **Ajukan keanggotaan**.
**Anggota**: semua milik pengunjung + **booking** (antre bila stok habis), **Peminjaman Saya** (pinjaman, tenggat, booking, denda, riwayat), **kartu anggota digital (QR)**, batalkan booking.
**Petugas** (`/petugas`, tampilan sendiri): beranda sirkulasi, **Transaksi** (scan kartu anggota + scan buku untuk pinjam / terima kembali), daftar peminjaman, **booking** (siapkan eksemplar / batalkan), **denda** (tandai lunas), cari & verifikasi anggota, laporan, cetak QR.
**Administrator** (`/admin`, tampilan ala template admin): dashboard statistik, **data buku & eksemplar** (CRUD + upload sampul), kategori, **Tampilan Dashboard** (atur Book Recomendation, urutan, sampul kategori, judul bagian), **anggota** (jadikan pengunjung anggota, daftarkan langsung, status, perpanjang kartu, reset sandi), akun staf & peran, pengaturan (durasi, maks buku, tarif denda), laporan (CSV), log aktivitas, cetak QR. Admin juga bisa membuka panel petugas.

## Alur keanggotaan
Daftar (email atau Google/Apple) -> akun **Pengunjung** -> "Ajukan keanggotaan" (opsional) -> datang bawa identitas -> admin menekan **Jadikan anggota** (menu Pengunjung) atau mendaftarkan langsung (menu Anggota) -> nomor anggota `DP-tahun-urut` + kartu berlaku 2 tahun -> akun menjadi **Anggota**.

## Sirkulasi (aturan otomatis)
Booking -> petugas **Siapkan** (eksemplar jadi "dibooking") -> anggota datang -> petugas scan kartu + scan buku -> **Pinjamkan** (cek: keanggotaan aktif & belum kedaluwarsa, batas maks buku, denda belum lunas bila diaktifkan, eksemplar milik booking orang lain ditolak) -> **kembali**: denda otomatis (hari terlambat x tarif) tercatat bila lewat tenggat.

## Sistem QR
QR buku = kode eksemplar (`DGP-005-02`), QR kartu = nomor anggota (`DP-2026-0142`). Halaman scan: kamera, unggah gambar, atau ketik kode. Kamera hanya jalan di `localhost`/`127.0.0.1`/`https`.
Cetak: menu **Cetak QR** (label standar 64x36 mm, stiker 32x40 mm) dan **Kartu anggota** (85,6x54 mm, depan-belakang) dari menu Anggota. Cetak dengan skala 100%.

## Login Google & Apple (opsional)
Tanpa paket tambahan; isi `.env` lalu restart `php artisan serve`. Akun baru dari Google/Apple otomatis menjadi **Pengunjung**; bila emailnya sudah terdaftar & terverifikasi, akun ditautkan.
**Google**: console.cloud.google.com -> APIs & Services -> Credentials -> *OAuth client ID* (Web application). Authorized redirect URI: `http://127.0.0.1:8000/auth/google/callback` (samakan dengan `APP_URL`). Isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET`.
**Apple**: developer.apple.com (perlu akun Apple Developer berbayar) -> Identifiers -> *Services ID* (Sign in with Apple, domain & Return URL **harus https** dan bukan localhost; untuk uji lokal pakai tunnel seperti ngrok) -> Keys -> buat key (.p8). Isi `APPLE_CLIENT_ID` (Services ID), `APPLE_TEAM_ID`, `APPLE_KEY_ID`, `APPLE_PRIVATE_KEY_PATH` (lokasi file .p8) dan `APPLE_REDIRECT_URI=https://domain-kamu/auth/apple/callback`.
Selama kunci belum diisi, tombol menampilkan pesan "belum dikonfigurasi".

## Struktur penting
- `app/Support/Sirkulasi.php`: aturan booking/pinjam/kembali/denda (satu sumber untuk scan QR dan tombol petugas). `Report.php`: laporan. `OAuth.php`: bantu OAuth.
- `app/Http/Controllers/{Admin,Petugas}`, `ScanController`, `SocialAuthController`, `routes/web.php` (middleware `role:...`).
- `resources/views/{layouts,admin,petugas,member,qr,laporan}`; warna tema bisa diubah di `public/css/tema.css` tanpa build.
- `app/Support/DummyData.php`: hanya data awal untuk seeder. Perubahan buku sehari-hari dilakukan lewat panel Admin.

## Kerja tim (GitHub)
`.env` jangan di-commit. Untuk berbagi tampilan tanpa npm, hapus `/public/build` dari `.gitignore` dan commit hasil `npm run build`.
