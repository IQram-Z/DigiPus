@extends('layouts.staff')
@section('title', $a->user->name)
@section('sub', 'Detail dan verifikasi anggota')
@section('actions')
  <a href="{{ route('petugas.anggota') }}" class="btn-l">Kembali</a>
  <a href="{{ route('qr.kartu', $a) }}" target="_blank" class="btn-l">Cetak kartu</a>
  <a href="{{ route('petugas.transaksi', ['member' => $a->no_anggota]) }}" class="btn-s">Mulai transaksi</a>
@endsection
@section('content')
<div class="grid gap-4 sm:grid-cols-4">
  <x-stat label="No. anggota" :value="$a->no_anggota" icon="card" />
  <x-stat label="Status" :value="ucfirst($a->status_keanggotaan)" icon="shield" :tone="$a->status_keanggotaan === 'aktif' ? 'green' : 'red'" :hint="$a->berlaku_sampai ? 'Berlaku sampai ' . $a->berlaku_sampai->format('d/m/Y') : null" />
  <x-stat label="Dipinjam" :value="count($s['loans'])" icon="book" />
  <x-stat label="Denda belum lunas" :value="'Rp ' . number_format($s['denda'], 0, ',', '.')" icon="wallet" :tone="$s['denda'] ? 'red' : 'green'" />
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <section class="card"><h2 class="ttl">Sedang dipinjam</h2><div class="mt-3 space-y-2">
    @forelse ($s['loans'] as $l)
      <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3"><p class="min-w-0 truncate font-semibold">{{ $l['title'] }}</p><x-badge :tone="$l['late'] ? 'red' : 'green'">{{ $l['late'] ? 'Terlambat · ' : '' }}{{ $l['due'] }}</x-badge></div>
    @empty <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada.</p> @endforelse
  </div></section>
  <section class="card"><h2 class="ttl">Booking aktif</h2><div class="mt-3 space-y-2">
    @forelse ($s['bookings'] as $b)
      <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3"><p class="min-w-0 truncate font-semibold">{{ $b['title'] }}</p><x-badge tone="amber">{{ ucfirst($b['status']) }}{{ $b['code'] ? ' · ' . $b['code'] : '' }}</x-badge></div>
    @empty <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada.</p> @endforelse
  </div></section>
  <section class="card xl:col-span-2"><h2 class="ttl">Riwayat terakhir</h2><div class="mt-3 grid gap-2 sm:grid-cols-2">
    @forelse ($riwayat as $p)
      <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3"><p class="min-w-0 truncate font-semibold">{{ $p->eksemplar->buku->judul }}</p><p class="shrink-0 text-xs text-neutral-500">{{ $p->tgl_pinjam->format('d/m/Y') }}</p></div>
    @empty <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500 sm:col-span-2">Belum ada riwayat.</p> @endforelse
  </div></section>
</div>
@endsection
