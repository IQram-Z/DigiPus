@extends('layouts.staff')
@section('title', 'Anggota')
@section('sub', 'Cari dan verifikasi data anggota (pendaftaran anggota dilakukan admin)')
@section('content')
<div class="card">
  @include('partials.search', ['action' => route('petugas.anggota'), 'q' => $q, 'placeholder' => 'Cari nama, email, atau nomor anggota...'])
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>No. anggota</th><th>Nama</th><th>Status</th><th>Berlaku sampai</th><th>Pinjaman aktif</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $a)
          <tr>
            <td class="font-mono text-xs">{{ $a->no_anggota }}</td>
            <td><p class="font-semibold">{{ $a->user->name }}</p><p class="text-xs text-neutral-500">{{ $a->user->email }}</p></td>
            <td><x-badge :tone="$a->status_keanggotaan === 'aktif' ? 'green' : 'red'">{{ ucfirst($a->status_keanggotaan) }}</x-badge></td>
            <td>{{ $a->berlaku_sampai?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $a->aktif_count }}</td>
            <td class="text-right"><div class="flex justify-end gap-2">
              <a href="{{ route('petugas.anggota.show', $a) }}" class="btn-l sm">Detail</a>
              <a href="{{ route('petugas.transaksi', ['member' => $a->no_anggota]) }}" class="btn-s sm">Transaksi</a>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Anggota tidak ditemukan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
