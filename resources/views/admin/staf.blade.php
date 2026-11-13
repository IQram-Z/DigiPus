@extends('layouts.admin')
@section('title', 'Akun Staf')
@section('sub', 'Kelola akun petugas dan administrator beserta hak aksesnya')
@section('content')
<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
  <section class="card">
    <h2 class="ttl">Daftar akun staf</h2>
    <div class="mt-3 overflow-x-auto">
      <table class="tbl">
        <thead><tr><th>Akun</th><th>Peran</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
        <tbody>
          @foreach ($items as $u)
            <tr>
              <td><p class="font-semibold">{{ $u->name }}@if ($u->id === auth()->id()) <x-badge tone="blue">Kamu</x-badge>@endif</p><p class="text-xs text-neutral-500">{{ $u->username }} &middot; {{ $u->email }}</p></td>
              <td>
                @if ($u->id === auth()->id())<x-badge tone="blue">{{ $u->roleLabel() }}</x-badge>
                @else
                  <form method="POST" action="{{ route('admin.staf.role', $u) }}">@csrf
                    <select name="role" onchange="this.form.submit()" class="fld !w-36 !py-1.5" aria-label="Peran"><option value="petugas" @selected($u->role === 'petugas')>Petugas</option><option value="administrator" @selected($u->role === 'administrator')>Administrator</option></select>
                  </form>
                @endif
              </td>
              <td><x-badge :tone="$u->status_akun === 'aktif' ? 'green' : 'red'">{{ ucfirst($u->status_akun) }}</x-badge></td>
              <td class="text-right"><div class="flex flex-wrap justify-end gap-2">
                @if ($u->id !== auth()->id())
                  <form method="POST" action="{{ route('admin.staf.status', $u) }}" data-confirm="{{ $u->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->username }}?">@csrf<button class="btn-l sm">{{ $u->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                @endif
                <form method="POST" action="{{ route('admin.pengguna.reset', $u) }}" data-confirm="Reset kata sandi {{ $u->username }}?">@csrf<button class="btn-l sm">Reset sandi</button></form>
              </div></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <form method="POST" action="{{ route('admin.staf.store') }}" class="card space-y-4">@csrf
    <h2 class="ttl">Tambah akun staf</h2>
    <x-field name="name" label="Nama lengkap" required />
    <x-field name="username" label="Username" required hint="Huruf, angka, strip" />
    <x-field name="email" label="Email" type="email" required />
    <x-field name="password" label="Kata sandi" type="password" required hint="Minimal 6 karakter" />
    <x-select name="role" label="Peran" :options="['petugas' => 'Petugas perpustakaan', 'administrator' => 'Administrator']" value="petugas" />
    <button class="btn-s w-full">Buat akun</button>
  </form>
</div>
@endsection
