@extends('layouts.admin')
@section('title', 'Dashboard')
@section('sub', 'Ringkasan perpustakaan DigiPus')
@section('actions')
  <a href="{{ route('admin.buku.create') }}" class="btn-s">@include('partials.icons', ['icon' => 'plus', 'cls' => 'size-4']) Tambah buku</a>
  <a href="{{ route('admin.anggota.create') }}" class="btn-r">@include('partials.icons', ['icon' => 'users', 'cls' => 'size-4']) Daftarkan anggota</a>
@endsection
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <x-stat label="Judul buku" :value="$stats['buku']" icon="book" :hint="$stats['eksemplar'] . ' eksemplar, ' . $stats['tersedia'] . ' tersedia'" :href="route('admin.buku.index')" />
  <x-stat label="Anggota aktif" :value="$stats['anggota']" icon="users" tone="green" :href="route('admin.anggota.index')" />
  <x-stat label="Pengunjung" :value="$stats['pengunjung']" icon="user" tone="amber" :hint="$pengajuan->count() . ' pengajuan menunggu'" :href="route('admin.pengunjung')" />
  <x-stat label="Akun staf" :value="$stats['staf']" icon="shield" :href="route('admin.staf')" />
  <x-stat label="Peminjaman aktif" :value="$stats['aktif']" icon="clipboard" />
  <x-stat label="Terlambat" :value="$stats['terlambat']" icon="clock" tone="red" />
  <x-stat label="Booking aktif" :value="$stats['booking']" icon="inbox" tone="amber" />
  <x-stat label="Denda belum lunas" :value="'Rp ' . number_format($stats['denda'], 0, ',', '.')" icon="wallet" tone="red" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
  <section class="card xl:col-span-2">
    <div class="flex items-center justify-between"><h2 class="ttl">Peminjaman &amp; pengembalian ({{ $d['judul'] }})</h2><a href="{{ route('admin.laporan') }}" class="text-xs font-semibold text-royal hover:underline">Laporan lengkap</a></div>
    <div class="mt-3">@include('partials.chart', ['d' => $d])</div>
  </section>

  <section class="card">
    <div class="flex items-center justify-between"><h2 class="ttl">Pengajuan anggota</h2><a href="{{ route('admin.pengunjung', ['tab' => 'pengajuan']) }}" class="text-xs font-semibold text-royal hover:underline">Semua</a></div>
    <div class="mt-3 space-y-2">
      @forelse ($pengajuan as $u)
        <div class="flex items-center justify-between gap-2 rounded-xl bg-neutral-50 px-3 py-2.5">
          <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $u->name }}</p><p class="truncate text-xs text-neutral-500">{{ $u->email }}</p></div>
          <form method="POST" action="{{ route('admin.pengunjung.jadikan', $u) }}" data-confirm="Jadikan {{ $u->name }} sebagai anggota?">@csrf<button class="btn-s sm">Jadikan anggota</button></form>
        </div>
      @empty
        <p class="rounded-xl bg-neutral-50 px-4 py-6 text-center text-sm text-neutral-500">Tidak ada pengajuan.</p>
      @endforelse
    </div>
  </section>

  <section class="card xl:col-span-2">
    <div class="flex items-center justify-between"><h2 class="ttl">Aktivitas terbaru</h2><a href="{{ route('admin.log') }}" class="text-xs font-semibold text-royal hover:underline">Semua log</a></div>
    <div class="mt-3 overflow-x-auto">
      <table class="tbl"><tbody>
        @foreach ($log as $l)
          <tr><td class="whitespace-nowrap text-xs text-neutral-500">{{ $l->waktu?->format('d/m H:i') }}</td><td class="font-semibold">{{ $l->user?->name ?? 'Sistem' }}</td><td>{{ $l->aktivitas }}</td></tr>
        @endforeach
      </tbody></table>
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Buku terpopuler</h2>
    <ol class="mt-3 space-y-2">
      @forelse ($d['populer']->take(5) as $i => $b)
        <li class="flex items-center gap-3 text-sm"><span class="grid size-6 place-items-center rounded-full bg-ink/5 text-xs font-bold text-ink">{{ $i + 1 }}</span><span class="min-w-0 flex-1 truncate">{{ $b->judul }}</span><span class="text-xs font-semibold text-neutral-500">{{ $b->total }}x</span></li>
      @empty
        <li class="text-sm text-neutral-500">Belum ada data.</li>
      @endforelse
    </ol>
  </section>
</div>
@endsection
