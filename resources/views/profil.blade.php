@extends($layout)
@section('title', 'Profil')
@section('sub', 'Informasi akun dan kata sandi')
@section('content')
<div class="{{ $layout === 'layouts.app' ? 'mx-auto max-w-2xl px-5 py-10 lg:py-16' : 'max-w-2xl' }}">
  @if ($layout === 'layouts.app')
    <h1 class="mb-6 text-3xl font-bold text-ink">Profil</h1>
    @include('partials.alerts')
  @endif
  <section class="card">
    <div class="flex items-center gap-4">
      <img src="{{ $user->fotoUrl() }}" alt="" referrerpolicy="no-referrer" class="size-16 rounded-full object-cover ring-2 ring-neutral-200">
      <div class="min-w-0">
        <p class="truncate text-lg font-bold text-ink">{{ $user->name }}</p>
        <p class="text-sm text-neutral-500">{{ $user->email }}</p>
        <div class="mt-1 flex flex-wrap gap-1.5">
          <x-badge tone="blue">{{ $user->roleLabel() }}</x-badge>
          @if ($user->anggota)<x-badge tone="green">{{ $user->anggota->no_anggota }}</x-badge>@endif
          @if ($user->provider)<x-badge>Masuk dengan {{ ucfirst($user->provider) }}</x-badge>@endif
        </div>
      </div>
    </div>
    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
      <div class="rounded-xl bg-neutral-50 p-3"><dt class="text-xs text-neutral-500">Username</dt><dd class="font-semibold">{{ $user->username }}</dd></div>
      <div class="rounded-xl bg-neutral-50 p-3"><dt class="text-xs text-neutral-500">Status akun</dt><dd class="font-semibold">{{ ucfirst($user->status_akun) }}</dd></div>
    </dl>
  </section>

  <form method="POST" action="{{ route('profil.update') }}" class="card mt-6 space-y-4">
    @csrf
    <h2 class="ttl">Ubah profil</h2>
    <x-field name="name" label="Nama lengkap" :value="$user->name" required />
    <hr class="border-neutral-100">
    <p class="text-sm font-semibold text-ink">Ganti kata sandi <span class="font-normal text-neutral-500">(kosongkan bila tidak diubah)</span></p>
    @if (! $user->provider)<x-field name="current_password" label="Kata sandi saat ini" type="password" />@endif
    <div class="grid gap-4 sm:grid-cols-2">
      <x-field name="password" label="Kata sandi baru" type="password" hint="Minimal 6 karakter" />
      <x-field name="password_confirmation" label="Ulangi kata sandi baru" type="password" />
    </div>
    <button class="btn-s !px-6">Simpan</button>
  </form>
</div>
@endsection
