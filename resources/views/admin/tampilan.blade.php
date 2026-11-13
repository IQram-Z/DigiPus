@extends('layouts.admin')
@section('title', 'Tampilan Dashboard')
@section('sub', 'Atur isi dashboard pengunjung & anggota: Book Recomendation, Book Category, dan judul bagian')
@section('content')
<form method="POST" action="{{ route('admin.tampilan.simpan') }}" class="space-y-6">
  @csrf
  <section class="card">
    <h2 class="ttl">Judul bagian</h2>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <x-field name="judul_dashboard" label="Judul halaman" :value="$set->judul_dashboard" required />
      <x-field name="judul_rekomendasi" label="Judul rekomendasi" :value="$set->judul_rekomendasi" required />
      <x-field name="judul_kategori" label="Judul kategori" :value="$set->judul_kategori" required />
      <x-field name="judul_koleksi" label="Judul koleksi" :value="$set->judul_koleksi" required />
      <x-field name="max_rekomendasi" label="Maks. buku rekomendasi" type="number" :value="$set->max_rekomendasi" min="1" max="24" required />
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Book Recomendation</h2>
    <p class="text-sm text-neutral-500">Centang buku yang tampil, lalu atur urutannya (angka kecil lebih dulu). Empat buku pertama langsung terlihat, sisanya bisa digeser.</p>
    <div class="mt-3 max-h-[28rem] overflow-y-auto">
      <table class="tbl">
        <thead><tr><th class="w-12">Tampil</th><th>Buku</th><th>Kategori</th><th class="w-28">Urutan</th></tr></thead>
        <tbody>
          @foreach ($books as $b)
            <tr>
              <td><input type="checkbox" name="rek[{{ $b->id }}]" value="1" class="size-4 accent-[#32269e]" @checked($b->rekomendasi)></td>
              <td><div class="flex items-center gap-3"><img src="{{ $b->coverUrl() }}" alt="" class="h-12 w-8 rounded object-cover shadow"><span class="font-semibold">{{ $b->judul }}</span></div></td>
              <td><x-badge tone="blue">{{ $b->kategori->nama_kategori }}</x-badge></td>
              <td><input type="number" name="urutan[{{ $b->id }}]" value="{{ $b->urutan_rekomendasi }}" min="0" max="999" class="fld !py-1.5"></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Book Category: sampul wakil</h2>
    <p class="text-sm text-neutral-500">Pilih buku yang sampulnya tampil di kotak tiap kategori. Biarkan "Otomatis" untuk memakai buku pertama.</p>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      @foreach ($kategori as $k)
        <x-select :name="'wakil[' . $k->id . ']'" :label="$k->nama_kategori" :options="$k->buku->pluck('judul', 'id')->all()" :value="$k->buku_wakil_id" blank="Otomatis" />
      @endforeach
    </div>
  </section>

  <div class="sticky bottom-4 flex justify-end"><button class="btn-s !px-8 !py-3 shadow-xl shadow-amber/30">Simpan tampilan</button></div>
</form>
@endsection
