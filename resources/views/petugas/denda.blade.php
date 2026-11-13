@extends('layouts.staff')
@section('title', 'Denda')
@section('sub', 'Keterlambatan dan pembayaran denda')
@section('content')
<div class="grid gap-4 sm:grid-cols-3">
  <x-stat label="Denda belum lunas" :value="'Rp ' . number_format($total, 0, ',', '.')" icon="wallet" tone="red" />
  <x-stat label="Sedang terlambat" :value="$berjalan->count()" icon="clock" tone="amber" hint="belum dikembalikan" />
  <x-stat label="Tarif denda" :value="'Rp ' . number_format($tarif, 0, ',', '.')" icon="sliders" hint="per hari per buku" />
</div>

@if ($berjalan->count())
<section class="card mt-6">
  <h2 class="ttl">Sedang terlambat (estimasi denda)</h2>
  <div class="mt-3 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Peminjam</th><th>Buku</th><th>Tenggat</th><th>Terlambat</th><th class="text-right">Estimasi denda</th></tr></thead>
      <tbody>
        @foreach ($berjalan as $p)
          @php $hari = \App\Support\Sirkulasi::hariTerlambat($p); @endphp
          <tr><td><p class="font-semibold">{{ $p->anggota->user->name }}</p><p class="text-xs text-neutral-500">{{ $p->anggota->no_anggota }}</p></td>
            <td>{{ $p->eksemplar->buku->judul }}</td><td>{{ $p->tgl_tenggat->format('d/m/Y') }}</td><td><x-badge tone="red">{{ $hari }} hari</x-badge></td>
            <td class="text-right font-semibold">Rp {{ number_format($hari * $tarif, 0, ',', '.') }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
@endif

<section class="card mt-6">
  @include('partials.tabs', ['route' => 'petugas.denda', 'tab' => $tab, 'q' => null, 'tabs' => [['belum', 'Belum lunas', null], ['lunas', 'Lunas', null]]])
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Anggota</th><th>Buku</th><th>Keterlambatan</th><th>Total</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $d)
          <tr>
            <td><p class="font-semibold">{{ $d->peminjaman->anggota->user->name }}</p><p class="text-xs text-neutral-500">{{ $d->peminjaman->anggota->no_anggota }}</p></td>
            <td>{{ $d->peminjaman->eksemplar->buku->judul }}</td>
            <td>{{ $d->jumlah_hari_terlambat }} hari &times; Rp {{ number_format($d->tarif_per_hari, 0, ',', '.') }}</td>
            <td class="font-bold text-ink">Rp {{ number_format($d->total_denda, 0, ',', '.') }}</td>
            <td>@if ($d->status_pembayaran === 'lunas')<x-badge tone="green">Lunas {{ $d->tgl_bayar?->format('d/m/Y') }}</x-badge>@else<x-badge tone="red">Belum lunas</x-badge>@endif</td>
            <td class="text-right">@if ($d->status_pembayaran === 'belum_lunas')
              <form method="POST" action="{{ route('petugas.bayar', $d) }}" data-confirm="Catat pembayaran denda ini sebagai lunas?">@csrf<button class="btn-s sm">Tandai lunas</button></form>@endif</td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</section>
@endsection
