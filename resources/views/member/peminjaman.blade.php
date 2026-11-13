@extends('layouts.page')
@section('title', 'Peminjaman Saya')
@section('heading', 'Peminjaman Saya')
@section('sub', 'Status pinjaman, booking, tenggat, dan denda kamu')
@section('body')
@php
  $totalDenda = $denda->where('status_pembayaran', 'belum_lunas')->sum('total_denda');
  $today = \Carbon\Carbon::today();
@endphp
<div class="grid gap-4 sm:grid-cols-3">
  <x-stat label="Sedang dipinjam" :value="$aktif->count()" icon="book" :hint="'Maks. ' . \App\Models\PengaturanPerpustakaan::cfg()->maks_buku_dipinjam . ' buku'" />
  <x-stat label="Booking aktif" :value="$booking->count()" icon="inbox" tone="amber" />
  <x-stat label="Denda belum lunas" :value="'Rp ' . number_format($totalDenda, 0, ',', '.')" icon="wallet" :tone="$totalDenda ? 'red' : 'green'" :hint="'Tarif Rp ' . number_format($tarif, 0, ',', '.') . '/hari'" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <section class="card">
    <h2 class="ttl">Sedang dipinjam</h2>
    <div class="mt-3 space-y-2">
      @forelse ($aktif as $p)
        @php $telat = \App\Support\Sirkulasi::hariTerlambat($p); @endphp
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <div class="min-w-0"><p class="truncate font-semibold">{{ $p->eksemplar->buku->judul }}</p><p class="text-xs text-neutral-500">Dipinjam {{ $p->tgl_pinjam->format('d/m/Y') }} &middot; kode {{ $p->eksemplar->kode_eksemplar }}</p></div>
          <div class="shrink-0 text-right">
            <p class="text-xs text-neutral-500">Tenggat {{ $p->tgl_tenggat->format('d/m/Y') }}</p>
            @if ($telat > 0)<x-badge tone="red">Terlambat {{ $telat }} hari &middot; Rp {{ number_format($telat * $tarif, 0, ',', '.') }}</x-badge>
            @else<x-badge tone="green">{{ (int) $today->diffInDays($p->tgl_tenggat, false) }} hari lagi</x-badge>@endif
          </div>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada buku yang sedang dipinjam.</p>
      @endforelse
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Booking</h2>
    <div class="mt-3 space-y-2">
      @forelse ($booking as $b)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <div class="min-w-0">
            <p class="truncate font-semibold">{{ $b->buku->judul }}</p>
            <p class="text-xs text-neutral-500">
              @if ($b->status_booking === 'disiapkan')Siap diambil &middot; eksemplar {{ $b->eksemplar?->kode_eksemplar }} (Rak {{ $b->buku->lokasi_rak }})
              @elseif ($b->status_booking === 'antre')Stok habis, kamu dalam antrean
              @else Menunggu petugas menyiapkan buku @endif
            </p>
          </div>
          <div class="flex shrink-0 items-center gap-2">
            <x-badge :tone="$b->status_booking === 'disiapkan' ? 'green' : 'amber'">{{ ucfirst($b->status_booking) }}</x-badge>
            <form method="POST" action="{{ route('booking.destroy', $b) }}" data-confirm="Batalkan booking ini?">@csrf @method('DELETE')<button class="btn-l sm">Batal</button></form>
          </div>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Belum ada booking. Pilih buku di Discover lalu tekan "Booking buku".</p>
      @endforelse
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Denda</h2>
    <div class="mt-3 space-y-2">
      @forelse ($denda as $d)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <div class="min-w-0"><p class="truncate font-semibold">{{ $d->peminjaman->eksemplar->buku->judul }}</p><p class="text-xs text-neutral-500">Terlambat {{ $d->jumlah_hari_terlambat }} hari &times; Rp {{ number_format($d->tarif_per_hari, 0, ',', '.') }}</p></div>
          <div class="shrink-0 text-right"><p class="font-bold text-ink">Rp {{ number_format($d->total_denda, 0, ',', '.') }}</p>
            @if ($d->status_pembayaran === 'lunas')<x-badge tone="green">Lunas {{ $d->tgl_bayar?->format('d/m/Y') }}</x-badge>@else<x-badge tone="red">Belum lunas</x-badge>@endif
          </div>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada denda. Terima kasih sudah tepat waktu!</p>
      @endforelse
      @if ($totalDenda > 0)<p class="text-xs text-neutral-500">Pembayaran denda dilakukan langsung di petugas perpustakaan.</p>@endif
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Riwayat peminjaman</h2>
    <div class="mt-3 space-y-2">
      @forelse ($riwayat as $p)
        <div class="flex items-center justify-between gap-3 rounded-xl bg-neutral-50 px-4 py-3">
          <p class="min-w-0 truncate font-semibold">{{ $p->eksemplar->buku->judul }}</p>
          <p class="shrink-0 text-xs text-neutral-500">{{ $p->tgl_pinjam->format('d/m/Y') }} &rarr; {{ $p->pengembalian?->tgl_kembali?->format('d/m/Y') ?? '-' }}</p>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Belum ada riwayat.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection
