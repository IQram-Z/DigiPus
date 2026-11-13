@extends($layout)
@section('title', 'Laporan')
@section('sub', 'Statistik peminjaman, pengembalian, dan keaktifan anggota')
@section('actions')
  <a href="{{ route($admin ? 'admin.laporan' : 'petugas.laporan', ['p' => $d['periode'], 'export' => 'csv']) }}" class="btn-l">@include('partials.icons', ['icon' => 'list', 'cls' => 'size-4']) Unduh CSV</a>
@endsection
@section('content')
@php $rt = $admin ? 'admin.laporan' : 'petugas.laporan'; @endphp
<div class="mb-5 flex flex-wrap gap-1.5">
  @foreach (['harian' => 'Harian', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'] as $k => $label)
    <a href="{{ route($rt, ['p' => $k]) }}" class="tab-i {{ $d['periode'] === $k ? 'on' : '' }}">{{ $label }}</a>
  @endforeach
  <span class="ml-2 self-center text-sm text-neutral-500">{{ $d['judul'] }}</span>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <x-stat label="Peminjaman" :value="$d['total_pinjam']" icon="book" />
  <x-stat label="Pengembalian" :value="$d['total_kembali']" icon="check" tone="green" />
  <x-stat label="Booking" :value="$d['total_booking']" icon="inbox" tone="amber" />
  <x-stat label="Denda tercatat" :value="'Rp ' . number_format($d['total_denda'], 0, ',', '.')" icon="wallet" tone="red" />
</div>

<section class="card mt-6"><h2 class="ttl">Peminjaman &amp; pengembalian</h2><div class="mt-3">@include('partials.chart', ['d' => $d])</div></section>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
  <section class="card">
    <h2 class="ttl">Buku terpopuler</h2>
    <table class="tbl mt-2"><thead><tr><th>#</th><th>Judul</th><th class="text-right">Dipinjam</th></tr></thead><tbody>
      @forelse ($d['populer'] as $i => $b)
        <tr><td>{{ $i + 1 }}</td><td><p class="font-semibold">{{ $b->judul }}</p><p class="text-xs text-neutral-500">{{ $b->penulis }}</p></td><td class="text-right font-semibold">{{ $b->total }}x</td></tr>
      @empty<tr><td colspan="3" class="py-8 text-center text-neutral-500">Belum ada data.</td></tr>@endforelse
    </tbody></table>
  </section>
  <section class="card">
    <h2 class="ttl">Anggota paling aktif</h2>
    <table class="tbl mt-2"><thead><tr><th>#</th><th>Anggota</th><th class="text-right">Pinjam</th></tr></thead><tbody>
      @forelse ($d['anggota_aktif'] as $i => $m)
        <tr><td>{{ $i + 1 }}</td><td><p class="font-semibold">{{ $m->name }}</p><p class="font-mono text-xs text-neutral-500">{{ $m->no_anggota }}</p></td><td class="text-right font-semibold">{{ $m->total }}x</td></tr>
      @empty<tr><td colspan="3" class="py-8 text-center text-neutral-500">Belum ada data.</td></tr>@endforelse
    </tbody></table>
  </section>
</div>
@endsection
