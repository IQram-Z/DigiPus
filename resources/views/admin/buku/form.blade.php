@extends('layouts.admin')
@section('title', $buku->exists ? 'Edit Buku' : 'Tambah Buku')
@section('sub', $buku->exists ? $buku->judul : 'Isi data buku dan jumlah eksemplar awal')
@section('actions')<a href="{{ route('admin.buku.index') }}" class="btn-l">Kembali</a>@endsection
@section('content')
@php $edit = $buku->exists; @endphp
<form method="POST" action="{{ $edit ? route('admin.buku.update', $buku) : route('admin.buku.store') }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
  @csrf
  @if ($edit) @method('PUT') @endif
  <section class="card grid gap-4 sm:grid-cols-2">
    <x-field name="judul" label="Judul" :value="$buku->judul" class="sm:col-span-2" required />
    <x-field name="penulis" label="Penulis" :value="$buku->penulis" required />
    <x-select name="kategori_id" label="Kategori" :options="$kategori->pluck('nama_kategori', 'id')->all()" :value="$buku->kategori_id" blank="Pilih kategori" />
    <x-field name="penerbit" label="Penerbit" :value="$buku->penerbit" />
    <x-field name="tahun_terbit" label="Tahun terbit" type="number" :value="$buku->tahun_terbit" />
    <x-field name="isbn" label="ISBN" :value="$buku->isbn" />
    <x-field name="lokasi_rak" label="Lokasi rak" :value="$buku->lokasi_rak" hint="Contoh: A-2" />
    <x-field name="tags" label="Label (tags)" :value="$buku->tags" hint="Dipisah koma, contoh: Fiksi,Sejarah" />
    <x-field name="rating" label="Rating" type="number" :value="$buku->rating" step="0.1" min="0" max="9.9" />
    <x-textarea name="sinopsis" label="Sinopsis / deskripsi" :value="$buku->sinopsis" :rows="7" class="sm:col-span-2" />
  </section>

  <aside class="space-y-6">
    <section class="card">
      <p class="fl">Sampul buku</p>
      <img id="cover-preview" src="{{ $buku->coverUrl() }}" alt="" class="mx-auto aspect-[2/3] w-40 rounded-lg object-cover shadow-lg">
      <input type="file" name="sampul" accept="image/*" data-preview="#cover-preview" class="fld mt-3 !px-2 !py-1.5 text-xs">
      <p class="mt-1 text-xs text-neutral-500">JPG/PNG/WebP maks. 4 MB, disarankan rasio 2:3 (mis. 500x750).</p>
      @error('sampul')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </section>
    <section class="card space-y-3">
      <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="rekomendasi" value="1" class="size-4 accent-[#32269e]" @checked(old('rekomendasi', $buku->rekomendasi))> Tampilkan di Book Recomendation</label>
      <x-field name="urutan_rekomendasi" label="Urutan di rak rekomendasi" type="number" :value="$buku->urutan_rekomendasi ?? 0" hint="Angka kecil tampil lebih dulu" />
      @unless ($edit)<x-field name="jumlah" label="Jumlah eksemplar awal" type="number" :value="1" min="0" max="50" hint="Kode QR dibuat otomatis (DGP-xxx-xx)" />@endunless
    </section>
    <button class="btn-s w-full !py-3">{{ $edit ? 'Simpan perubahan' : 'Tambah buku' }}</button>
  </aside>
</form>

@if ($edit)
<section class="card mt-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <h2 class="ttl">Eksemplar ({{ $buku->eksemplar->count() }})</h2>
    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('qr.labels', ['ids' => $buku->eksemplar->pluck('id')->all()]) }}" target="_blank" class="btn-l sm">@include('partials.icons', ['icon' => 'printer', 'cls' => 'size-4']) Cetak QR semua</a>
      <form method="POST" action="{{ route('admin.eksemplar.tambah', $buku) }}" class="flex items-center gap-2">@csrf<input type="number" name="jumlah" value="1" min="1" max="50" class="fld !w-20 !py-1.5" aria-label="Jumlah"><button class="btn-r sm">Tambah eksemplar</button></form>
    </div>
  </div>
  <div class="mt-3 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Kode (isi QR)</th><th>Status</th><th>Ubah status</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @foreach ($buku->eksemplar as $e)
          @php $tone = ['tersedia' => 'green', 'dibooking' => 'amber', 'dipinjam' => 'blue', 'hilang' => 'red'][$e->status] ?? 'gray'; @endphp
          <tr>
            <td class="font-mono text-xs font-semibold">{{ $e->kode_eksemplar }}</td>
            <td><x-badge :tone="$tone">{{ ucfirst($e->status) }}</x-badge></td>
            <td>
              <form method="POST" action="{{ route('admin.eksemplar.ubah', $e) }}" class="flex items-center gap-2">@csrf @method('PATCH')
                <select name="status" class="fld !w-32 !py-1.5"><option value="tersedia" @selected($e->status === 'tersedia')>Tersedia</option><option value="hilang" @selected($e->status === 'hilang')>Hilang</option></select>
                <button class="btn-l sm">Simpan</button>
              </form>
            </td>
            <td class="text-right"><div class="flex justify-end gap-2">
              <a href="{{ route('qr.labels', ['ids' => [$e->id]]) }}" target="_blank" class="btn-l sm">Cetak QR</a>
              <form method="POST" action="{{ route('admin.eksemplar.hapus', $e) }}" data-confirm="Hapus eksemplar {{ $e->kode_eksemplar }}?">@csrf @method('DELETE')<button class="btn-x sm">Hapus</button></form>
            </div></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
@endif
@endsection
