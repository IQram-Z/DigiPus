@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('sub', 'Aturan peminjaman dan denda perpustakaan')
@section('content')
<form method="POST" action="{{ route('admin.pengaturan.simpan') }}" class="card max-w-2xl space-y-4">@csrf
  <div class="grid gap-4 sm:grid-cols-3">
    <x-field name="durasi_pinjam_hari" label="Durasi pinjam (hari)" type="number" :value="$set->durasi_pinjam_hari" min="1" max="60" required />
    <x-field name="maks_buku_dipinjam" label="Maks. buku per anggota" type="number" :value="$set->maks_buku_dipinjam" min="1" max="20" required />
    <x-field name="tarif_denda_per_hari" label="Denda per hari (Rp)" type="number" :value="$set->tarif_denda_per_hari" min="0" required />
  </div>
  <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="blokir_pinjam_jika_denda" value="1" class="mt-0.5 size-4 accent-[#32269e]" @checked($set->blokir_pinjam_jika_denda)><span><b>Blokir peminjaman &amp; booking bila masih ada denda belum lunas</b><br><span class="text-neutral-500">Anggota harus melunasi denda dulu sebelum meminjam lagi.</span></span></label>
  <button class="btn-s !px-8">Simpan pengaturan</button>
</form>
@endsection
