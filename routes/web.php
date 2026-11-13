<?php

use App\Http\Controllers\Admin\{AdminController, AnggotaController, BukuController, KategoriController, StafController};
use App\Http\Controllers\Petugas\PetugasController;
use App\Http\Controllers\{AuthController, BookingController, DashboardController, LaporanController, MemberController, ProfileController, QrController, ScanController, SocialAuthController};
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// ---------- Tamu (belum login) ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // login / daftar dengan Google & Apple (Apple memanggil callback lewat POST)
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.redirect');
    Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.callback');
});

// ---------- Semua pengguna yang sudah login ----------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profil');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // QR (pemeriksaan hak akses ada di controller: pengunjung/anggota hanya lihat, petugas/admin bisa transaksi)
    Route::get('/scan', [ScanController::class, 'index'])->name('scan');
    Route::post('/scan/resolve', [ScanController::class, 'resolve'])->name('scan.resolve');
    Route::post('/scan/pinjam', [ScanController::class, 'pinjam'])->name('scan.pinjam');
    Route::post('/scan/kembali', [ScanController::class, 'kembali'])->name('scan.kembali');

    // booking: anggota saja (pengunjung ditolak dengan pesan di controller)
    Route::post('/booking/{buku}', [BookingController::class, 'store'])->name('booking.store');
});

// ---------- Pengunjung: ajukan jadi anggota ----------
Route::middleware(['auth', 'role:pengunjung,anggota'])->group(function () {
    Route::get('/jadi-anggota', [MemberController::class, 'join'])->name('member.join');
    Route::post('/jadi-anggota', [MemberController::class, 'ajukan'])->name('member.ajukan');
});

// ---------- Anggota ----------
Route::middleware(['auth', 'role:anggota'])->group(function () {
    Route::get('/peminjaman-saya', [MemberController::class, 'peminjaman'])->name('member.peminjaman');
    Route::delete('/booking/{booking}', [BookingController::class, 'destroy'])->name('booking.destroy');
});

// ---------- Petugas & Administrator ----------
Route::middleware(['auth', 'role:petugas,administrator'])->group(function () {
    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/', [PetugasController::class, 'dashboard'])->name('dashboard');
        Route::get('/transaksi', [PetugasController::class, 'transaksi'])->name('transaksi');
        Route::get('/peminjaman', [PetugasController::class, 'peminjaman'])->name('peminjaman');
        Route::post('/peminjaman/{peminjaman}/kembali', [PetugasController::class, 'kembali'])->name('kembali');
        Route::get('/booking', [PetugasController::class, 'booking'])->name('booking');
        Route::post('/booking/{booking}/siapkan', [PetugasController::class, 'siapkan'])->name('siapkan');
        Route::post('/booking/{booking}/batal', [PetugasController::class, 'batal'])->name('batal');
        Route::get('/denda', [PetugasController::class, 'denda'])->name('denda');
        Route::post('/denda/{denda}/bayar', [PetugasController::class, 'bayar'])->name('bayar');
        Route::get('/anggota', [PetugasController::class, 'anggota'])->name('anggota');
        Route::get('/anggota/{anggota}', [PetugasController::class, 'anggotaShow'])->name('anggota.show');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    });

    Route::get('/qr', [QrController::class, 'index'])->name('qr.index');
    Route::get('/qr/cetak', [QrController::class, 'labels'])->name('qr.labels');
    Route::get('/qr/kartu/{anggota}', [QrController::class, 'kartu'])->name('qr.kartu');
});

// ---------- Administrator ----------
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('buku', BukuController::class)->except('show');
    Route::post('/buku/{buku}/eksemplar', [BukuController::class, 'tambah'])->name('eksemplar.tambah');
    Route::patch('/eksemplar/{eksemplar}', [BukuController::class, 'ubahEksemplar'])->name('eksemplar.ubah');
    Route::delete('/eksemplar/{eksemplar}', [BukuController::class, 'hapusEksemplar'])->name('eksemplar.hapus');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    Route::get('/tampilan', [AdminController::class, 'tampilan'])->name('tampilan');
    Route::post('/tampilan', [AdminController::class, 'simpanTampilan'])->name('tampilan.simpan');

    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::get('/anggota/baru', [AnggotaController::class, 'create'])->name('anggota.create');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::post('/anggota/{anggota}/status', [AnggotaController::class, 'status'])->name('anggota.status');
    Route::post('/anggota/{anggota}/perpanjang', [AnggotaController::class, 'perpanjang'])->name('anggota.perpanjang');
    Route::get('/pengunjung', [AnggotaController::class, 'pengunjung'])->name('pengunjung');
    Route::post('/pengunjung/{user}/jadikan-anggota', [AnggotaController::class, 'jadikan'])->name('pengunjung.jadikan');
    Route::post('/pengunjung/{user}/tolak', [AnggotaController::class, 'tolak'])->name('pengunjung.tolak');

    Route::get('/staf', [StafController::class, 'index'])->name('staf');
    Route::post('/staf', [StafController::class, 'store'])->name('staf.store');
    Route::post('/staf/{user}/status', [StafController::class, 'status'])->name('staf.status');
    Route::post('/staf/{user}/role', [StafController::class, 'role'])->name('staf.role');
    Route::post('/pengguna/{user}/reset', [StafController::class, 'reset'])->name('pengguna.reset');

    Route::get('/pengaturan', [AdminController::class, 'pengaturan'])->name('pengaturan');
    Route::post('/pengaturan', [AdminController::class, 'simpanPengaturan'])->name('pengaturan.simpan');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/log', [AdminController::class, 'log'])->name('log');
});
