@extends('layouts.admin')
@section('title', 'Anggota')
@section('sub', 'Data anggota perpustakaan, status keanggotaan, dan kartu')
@section('actions')<a href="{{ route('admin.anggota.create') }}" class="btn-s">@include('partials.icons', ['icon' => 'plus', 'cls' => 'size-4']) Daftarkan anggota</a>@endsection
@section('content')
<div class="card">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-1.5">
      @foreach (['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'blokir' => 'Blokir'] as $k => $label)
        <a href="{{ route('admin.anggota.index', array_filter(['status' => $k, 'q' => $q])) }}" class="tab-i {{ (string) $status === (string) $k ? 'on' : '' }}">{{ $label }}</a>
      @endforeach
    </div>
    @include('partials.search', ['action' => route('admin.anggota.index'), 'q' => $q, 'hidden' => ['status' => $status], 'placeholder' => 'Cari nama, email, atau nomor...'])
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>No. anggota</th><th>Nama</th><th>Status</th><th>Berlaku sampai</th><th>Pinjam</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $a)
          <tr>
            <td class="font-mono text-xs font-semibold">{{ $a->no_anggota }}</td>
            <td><p class="font-semibold">{{ $a->user->name }}</p><p class="text-xs text-neutral-500">{{ $a->user->email }}</p></td>
            <td>
              <form method="POST" action="{{ route('admin.anggota.status', $a) }}">@csrf
                <select name="status" onchange="this.form.submit()" class="fld !w-32 !py-1.5" aria-label="Status keanggotaan">
                  @foreach (['aktif', 'nonaktif', 'blokir'] as $s)<option value="{{ $s }}" @selected($a->status_keanggotaan === $s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
              </form>
            </td>
            <td>@if ($a->berlaku_sampai)<span class="{{ $a->berlaku_sampai->isPast() ? 'font-semibold text-rose-600' : '' }}">{{ $a->berlaku_sampai->format('d/m/Y') }}</span>@else - @endif</td>
            <td>{{ $a->aktif_count }}</td>
            <td class="text-right"><div class="flex flex-wrap justify-end gap-2">
              <a href="{{ route('qr.kartu', $a) }}" target="_blank" class="btn-l sm">Kartu</a>
              <form method="POST" action="{{ route('admin.anggota.perpanjang', $a) }}" data-confirm="Perpanjang masa berlaku 2 tahun?">@csrf<button class="btn-l sm">Perpanjang</button></form>
              <form method="POST" action="{{ route('admin.pengguna.reset', $a->user) }}" data-confirm="Reset kata sandi {{ $a->user->name }}?">@csrf<button class="btn-l sm">Reset sandi</button></form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Belum ada anggota.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
