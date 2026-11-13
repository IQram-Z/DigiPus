@extends('layouts.admin')
@section('title', 'Daftarkan Anggota')
@section('sub', 'Untuk pelanggan yang datang langsung: akun dan keanggotaan dibuat sekaligus')
@section('actions')<a href="{{ route('admin.anggota.index') }}" class="btn-l">Kembali</a>@endsection
@section('content')
<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,32rem)_minmax(0,1fr)]">
  <form method="POST" action="{{ route('admin.anggota.store') }}" class="card space-y-4">@csrf
    <x-field name="name" label="Nama lengkap" required />
    <x-field name="email" label="Email" type="email" required />
    <x-field name="password" label="Kata sandi (opsional)" type="password" hint="Kosongkan untuk membuat kata sandi sementara otomatis" />
    <button class="btn-s w-full !py-3">Daftarkan sebagai anggota</button>
  </form>
  <section class="card text-sm text-neutral-600">
    <h2 class="ttl">Alur keanggotaan</h2>
    <ol class="mt-3 list-decimal space-y-2 pl-5">
      <li>Pelanggan yang sudah punya akun (daftar sendiri atau lewat Google/Apple) tampil di menu <a href="{{ route('admin.pengunjung') }}" class="font-semibold text-royal hover:underline">Pengunjung</a>. Tekan <b>Jadikan anggota</b> setelah identitasnya diverifikasi.</li>
      <li>Pelanggan baru yang datang ke perpustakaan bisa didaftarkan di sini.</li>
      <li>Nomor anggota (format DP-tahun-urut) dan masa berlaku 2 tahun dibuat otomatis. Cetak kartunya dari menu Anggota.</li>
    </ol>
  </section>
</div>
@endsection
