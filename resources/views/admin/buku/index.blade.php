@extends('layouts.admin')
@section('title', 'Data Buku')
@section('sub', 'Kelola katalog buku dan eksemplar')
@section('actions')<a href="{{ route('admin.buku.create') }}" class="btn-s">@include('partials.icons', ['icon' => 'plus', 'cls' => 'size-4']) Tambah buku</a>@endsection
@section('content')
<div class="card">
  <form method="GET" class="flex flex-wrap items-center gap-2">
    <input name="q" value="{{ $q }}" placeholder="Cari judul, penulis, ISBN..." class="fld max-w-xs" aria-label="Cari">
    <select name="kategori" class="fld max-w-[12rem]" aria-label="Kategori"><option value="">Semua kategori</option>@foreach ($kategori as $k)<option value="{{ $k->id }}" @selected((string) $kat === (string) $k->id)>{{ $k->nama_kategori }}</option>@endforeach</select>
    <button class="btn-r">Cari</button>
  </form>
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Buku</th><th>Kategori</th><th>Rak</th><th>Eksemplar</th><th>Rekomendasi</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $b)
          <tr>
            <td><div class="flex items-center gap-3"><img src="{{ $b->coverUrl() }}" alt="" class="h-14 w-10 shrink-0 rounded object-cover shadow"><div class="min-w-0"><p class="max-w-[18rem] truncate font-semibold">{{ $b->judul }}</p><p class="text-xs text-neutral-500">{{ $b->penulis }}</p></div></div></td>
            <td><x-badge tone="blue">{{ $b->kategori->nama_kategori }}</x-badge></td>
            <td>{{ $b->lokasi_rak ?: '-' }}</td>
            <td><span class="font-semibold">{{ $b->tersedia }}</span><span class="text-neutral-500"> / {{ $b->total }} tersedia</span></td>
            <td>@if ($b->rekomendasi)<x-badge tone="amber">#{{ $b->urutan_rekomendasi }}</x-badge>@else<span class="text-neutral-400">-</span>@endif</td>
            <td class="text-right"><div class="flex justify-end gap-2">
              <a href="{{ route('admin.buku.edit', $b) }}" class="btn-l sm">Edit</a>
              <form method="POST" action="{{ route('admin.buku.destroy', $b) }}" data-confirm="Hapus buku &quot;{{ $b->judul }}&quot;?">@csrf @method('DELETE')<button class="btn-x sm">Hapus</button></form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Belum ada buku.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
