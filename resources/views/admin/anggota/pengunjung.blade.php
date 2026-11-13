@extends('layouts.admin')
@section('title', 'Pengunjung')
@section('sub', 'Akun yang sudah mendaftar tetapi belum menjadi anggota')
@section('content')
<div class="card">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex gap-1.5">
      <a href="{{ route('admin.pengunjung') }}" class="tab-i {{ $tab === 'semua' ? 'on' : '' }}">Semua</a>
      <a href="{{ route('admin.pengunjung', ['tab' => 'pengajuan']) }}" class="tab-i {{ $tab === 'pengajuan' ? 'on' : '' }}">Pengajuan anggota</a>
    </div>
    @include('partials.search', ['action' => route('admin.pengunjung'), 'q' => $q, 'hidden' => ['tab' => $tab === 'pengajuan' ? 'pengajuan' : null], 'placeholder' => 'Cari nama, email, username...'])
  </div>
  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Akun</th><th>Masuk lewat</th><th>Pengajuan</th><th>Terdaftar</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $u)
          <tr>
            <td><p class="font-semibold">{{ $u->name }}</p><p class="text-xs text-neutral-500">{{ $u->email }} &middot; {{ $u->username }}</p></td>
            <td>{{ $u->provider ? ucfirst($u->provider) : 'Email' }}</td>
            <td>@if ($u->pengajuan_at)<x-badge tone="amber">{{ $u->pengajuan_at->format('d/m/Y H:i') }}</x-badge>@else<span class="text-neutral-400">-</span>@endif</td>
            <td>{{ $u->created_at?->format('d/m/Y') }}</td>
            <td class="text-right"><div class="flex flex-wrap justify-end gap-2">
              <form method="POST" action="{{ route('admin.pengunjung.jadikan', $u) }}" data-confirm="Jadikan {{ $u->name }} sebagai anggota?">@csrf<button class="btn-s sm">Jadikan anggota</button></form>
              @if ($u->pengajuan_at)<form method="POST" action="{{ route('admin.pengunjung.tolak', $u) }}" data-confirm="Hapus pengajuan ini?">@csrf<button class="btn-l sm">Tolak</button></form>@endif
              <form method="POST" action="{{ route('admin.pengguna.reset', $u) }}" data-confirm="Reset kata sandi {{ $u->name }}?">@csrf<button class="btn-l sm">Reset sandi</button></form>
            </div></td>
          </tr>
        @empty
          <tr><td colspan="5" class="py-10 text-center text-neutral-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
