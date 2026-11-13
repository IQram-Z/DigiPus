@extends('layouts.staff')
@section('title', 'Beranda Petugas')
@section('sub', 'Ringkasan sirkulasi perpustakaan hari ini')
@section('actions')
  <a href="{{ route('petugas.transaksi') }}" class="btn-s">@include('partials.icons', ['icon' => 'scan', 'cls' => 'size-4']) Mulai scan transaksi</a>
@endsection
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
  <x-stat label="Peminjaman aktif" :value="$stats['aktif']" icon="book" :href="route('petugas.peminjaman')" />
  <x-stat label="Jatuh tempo hari ini" :value="$stats['jatuh_tempo']" icon="clock" tone="amber" :href="route('petugas.peminjaman')" />
  <x-stat label="Terlambat" :value="$stats['terlambat']" icon="clipboard" tone="red" :href="route('petugas.peminjaman', ['tab' => 'terlambat'])" />
  <x-stat label="Booking menunggu" :value="$stats['menunggu']" icon="inbox" tone="amber" :href="route('petugas.booking')" />
  <x-stat label="Siap diambil" :value="$stats['siap']" icon="check" tone="green" :href="route('petugas.booking', ['tab' => 'siap'])" />
  <x-stat label="Denda belum lunas" :value="'Rp ' . number_format($stats['denda'], 0, ',', '.')" icon="wallet" tone="red" :href="route('petugas.denda')" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <section class="card">
    <div class="flex items-center justify-between"><h2 class="ttl">Antrean booking</h2><a href="{{ route('petugas.booking') }}" class="text-xs font-semibold text-royal hover:underline">Lihat semua</a></div>
    <div class="mt-3 space-y-2">
      @forelse ($antrean as $b)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <div class="min-w-0"><p class="truncate font-semibold">{{ $b->buku->judul }}</p><p class="text-xs text-neutral-500">{{ $b->anggota->user->name }} &middot; {{ $b->tgl_booking->format('d/m H:i') }}</p></div>
          <div class="flex shrink-0 items-center gap-2">
            <x-badge :tone="$b->status_booking === 'antre' ? 'red' : 'amber'">{{ $b->status_booking === 'antre' ? 'Antre' : 'Menunggu' }}</x-badge>
            <form method="POST" action="{{ route('petugas.siapkan', $b) }}">@csrf<button class="btn-s sm">Siapkan</button></form>
          </div>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada booking yang menunggu.</p>
      @endforelse
    </div>
  </section>

  <section class="card">
    <div class="flex items-center justify-between"><h2 class="ttl">Perlu perhatian: terlambat</h2><a href="{{ route('petugas.peminjaman', ['tab' => 'terlambat']) }}" class="text-xs font-semibold text-royal hover:underline">Lihat semua</a></div>
    <div class="mt-3 space-y-2">
      @forelse ($terlambat as $p)
        @php $hari = \App\Support\Sirkulasi::hariTerlambat($p); @endphp
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <div class="min-w-0"><p class="truncate font-semibold">{{ $p->eksemplar->buku->judul }}</p><p class="text-xs text-neutral-500">{{ $p->anggota->user->name }} &middot; tenggat {{ $p->tgl_tenggat->format('d/m/Y') }}</p></div>
          <x-badge tone="red">{{ $hari }} hari &middot; Rp {{ number_format($hari * $tarif, 0, ',', '.') }}</x-badge>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada peminjaman yang terlambat.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection
