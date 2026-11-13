@extends('layouts.admin')
@section('title', 'Kategori Buku')
@section('sub', 'Kelola kategori yang tampil di Book Category')
@section('content')
<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
  <section class="card">
    <h2 class="ttl">Daftar kategori</h2>
    <div class="mt-3 space-y-2">
      @foreach ($items as $k)
        <div class="flex flex-wrap items-center gap-2 rounded-xl bg-neutral-50 p-3">
          <form method="POST" action="{{ route('admin.kategori.update', $k) }}" class="flex flex-1 flex-wrap items-center gap-2">@csrf @method('PUT')
            <input name="nama_kategori" value="{{ $k->nama_kategori }}" class="fld min-w-[8rem] flex-1" aria-label="Nama kategori">
            <input name="urutan" type="number" value="{{ $k->urutan }}" class="fld !w-20" aria-label="Urutan" title="Urutan tampil">
            <x-badge tone="blue">{{ $k->buku_count }} buku</x-badge>
            <button class="btn-r sm">Simpan</button>
          </form>
          <form method="POST" action="{{ route('admin.kategori.destroy', $k) }}" data-confirm="Hapus kategori {{ $k->nama_kategori }}?">@csrf @method('DELETE')<button class="btn-x sm">Hapus</button></form>
        </div>
      @endforeach
    </div>
  </section>
  <form method="POST" action="{{ route('admin.kategori.store') }}" class="card space-y-4">@csrf
    <h2 class="ttl">Tambah kategori</h2>
    <x-field name="nama_kategori" label="Nama kategori" required />
    <x-field name="urutan" label="Urutan tampil" type="number" hint="Kosongkan untuk ditaruh paling akhir" />
    <button class="btn-s w-full">Tambah</button>
  </form>
</div>
@endsection
