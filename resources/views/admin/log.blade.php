@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('sub', 'Jejak aktivitas pengguna dan transaksi')
@section('content')
<div class="card">
  @include('partials.search', ['action' => route('admin.log'), 'q' => $q, 'placeholder' => 'Cari aktivitas atau pengguna...'])
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th></tr></thead>
      <tbody>
        @forelse ($items as $l)
          <tr><td class="whitespace-nowrap text-xs text-neutral-500">{{ $l->waktu?->format('d/m/Y H:i:s') }}</td><td><span class="font-semibold">{{ $l->user?->name ?? 'Sistem' }}</span> @if ($l->user)<x-badge>{{ $l->user->roleLabel() }}</x-badge>@endif</td><td>{{ $l->aktivitas }}</td></tr>
        @empty
          <tr><td colspan="3" class="py-10 text-center text-neutral-500">Belum ada aktivitas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
