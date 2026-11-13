@extends('layouts.page')
@section('title', 'Jadi Anggota')
@section('heading', 'Jadi Anggota')
@section('sub', 'Anggota bisa booking buku, punya kartu digital, dan melihat riwayat peminjaman')
@section('body')
<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,24rem)]">
  <section class="card">
    <h2 class="ttl">Yang bisa kamu lakukan</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
      <div class="rounded-2xl bg-neutral-50 p-4"><p class="font-semibold text-ink">Sekarang (pengunjung)</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-neutral-600"><li>Menjelajah katalog &amp; mencari buku</li><li>Melihat detail dan ketersediaan buku</li><li>Memindai QR buku untuk detailnya</li></ul></div>
      <div class="rounded-2xl bg-royal/5 p-4"><p class="font-semibold text-royal">Setelah jadi anggota</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-neutral-600"><li>Booking buku &amp; antre bila stok habis</li><li>Kartu anggota digital (QR) untuk transaksi</li><li>Riwayat peminjaman, tenggat, dan denda</li></ul></div>
    </div>
    <h2 class="ttl mt-6">Cara mendaftar</h2>
    <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-neutral-700">
      <li>Tekan tombol <b>Ajukan keanggotaan</b>.</li>
      <li>Datang ke perpustakaan membawa identitas diri (KTP/kartu pelajar).</li>
      <li>Admin memverifikasi dan mendaftarkan akunmu sebagai anggota. Kamu mendapat nomor anggota dan kartu digital.</li>
    </ol>
  </section>

  <section class="card h-fit text-center">
    @if ($user->pengajuan_at)
      <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-emerald-700">@include('partials.icons', ['icon' => 'check', 'cls' => 'size-7'])</span>
      <p class="mt-3 text-lg font-bold text-ink">Pengajuan terkirim</p>
      <p class="mt-1 text-sm text-neutral-600">Diajukan {{ $user->pengajuan_at->format('d/m/Y H:i') }}. Datang ke perpustakaan untuk verifikasi.</p>
    @else
      <span class="mx-auto grid size-14 place-items-center rounded-full bg-amber/15 text-amber">@include('partials.icons', ['icon' => 'card', 'cls' => 'size-7'])</span>
      <p class="mt-3 text-lg font-bold text-ink">Belum jadi anggota</p>
      <p class="mt-1 text-sm text-neutral-600">Akun: <b>{{ $user->email }}</b></p>
      <form method="POST" action="{{ route('member.ajukan') }}" class="mt-4">@csrf<button class="btn-o !h-[2.9rem]">Ajukan keanggotaan</button></form>
    @endif
  </section>
</div>
@endsection
