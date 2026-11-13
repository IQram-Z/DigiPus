<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') · Admin · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tema.css') }}?v={{ @filemtime(public_path('css/tema.css')) }}">
</head>
<body class="min-h-screen bg-[#f5f4fb] font-sans text-neutral-800">
  @php
    $au = auth()->user();
    $pending = \App\Models\User::where('role', 'pengunjung')->whereNotNull('pengajuan_at')->count();
    $menu = [
      ['Dashboard', 'admin.dashboard', 'layout', 'admin.dashboard', 0],
      'KATALOG',
      ['Buku', 'admin.buku.index', 'book', 'admin.buku.*', 0],
      ['Kategori', 'admin.kategori.index', 'tag', 'admin.kategori.*', 0],
      ['Tampilan Dashboard', 'admin.tampilan', 'rows', 'admin.tampilan*', 0],
      'KEANGGOTAAN',
      ['Anggota', 'admin.anggota.index', 'users', 'admin.anggota.*', 0],
      ['Pengunjung', 'admin.pengunjung', 'user', 'admin.pengunjung*', $pending],
      'SISTEM',
      ['Akun Staf', 'admin.staf', 'shield', 'admin.staf*', 0],
      ['Pengaturan', 'admin.pengaturan', 'sliders', 'admin.pengaturan*', 0],
      ['Laporan', 'admin.laporan', 'chart', 'admin.laporan', 0],
      ['Log Aktivitas', 'admin.log', 'clipboard', 'admin.log', 0],
      'ALAT',
      ['Cetak QR', 'qr.index', 'printer', 'qr.*', 0],
      ['Panel Petugas', 'petugas.dashboard', 'scan', 'petugas.*', 0],
    ];
  @endphp
  <div id="scrim" class="pointer-events-none fixed inset-0 z-30 bg-ink/50 opacity-0 transition-opacity duration-500 lg:hidden"></div>
  <aside id="side" class="fixed inset-y-0 left-0 z-40 flex w-[17rem] -translate-x-full flex-col overflow-y-auto bg-ink px-4 py-6 text-white transition-transform duration-500 ease-out lg:translate-x-0">
    <a href="{{ route('admin.dashboard') }}" class="px-3 text-2xl font-extrabold">DigiPus.<span class="ml-2 rounded-md bg-amber px-2 py-0.5 align-middle text-[0.6rem] font-bold tracking-widest">ADMIN</span></a>
    <nav class="mt-6 flex-1 space-y-1" aria-label="Menu admin">
      @foreach ($menu as $m)
        @if (is_string($m))
          <p class="px-3 pb-1 pt-5 text-[0.65rem] font-semibold tracking-[0.18em] text-white/40">{{ $m }}</p>
        @else
          <a href="{{ route($m[1]) }}" class="side-i {{ request()->routeIs($m[3]) ? 'on' : '' }}">
            @include('partials.icons', ['icon' => $m[2]])<span class="flex-1">{{ $m[0] }}</span>@if ($m[4])<span class="rounded-full bg-amber px-2 text-[0.65rem] font-bold">{{ $m[4] }}</span>@endif
          </a>
        @endif
      @endforeach
    </nav>
    <div class="mt-6 rounded-2xl bg-white/10 p-3 text-xs"><p class="font-semibold">{{ $au->name }}</p><p class="text-white/60">{{ $au->roleLabel() }}</p></div>
  </aside>

  <div class="lg:ml-[17rem]">
    <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-neutral-200/70 bg-white/90 px-4 py-3 backdrop-blur lg:px-8">
      <div class="flex items-center gap-3">
        <button id="menu" class="grid size-10 place-items-center rounded-xl bg-ink/5 text-ink lg:hidden" aria-label="Buka menu">@include('partials.icons', ['icon' => 'rows', 'cls' => 'size-5'])</button>
        <p class="text-sm text-neutral-500">Panel Administrator</p>
      </div>
      <div class="relative">
        <button id="user-btn" class="flex items-center gap-2 text-xs" aria-haspopup="true">
          <img src="{{ $au->fotoUrl() }}" alt="" referrerpolicy="no-referrer" class="size-9 rounded-full object-cover ring-2 ring-neutral-200">
          <span class="hidden max-w-[8rem] truncate font-semibold sm:inline">{{ $au->name }}</span>@include('partials.icons', ['icon' => 'down', 'cls' => 'size-3'])
        </button>
        <div id="user-menu" class="invisible absolute right-0 top-12 z-40 w-56 origin-top-right scale-90 rounded-2xl bg-white p-4 text-black opacity-0 shadow-2xl ring-1 ring-neutral-200 transition duration-300">
          <p class="truncate text-sm font-semibold">{{ $au->name }}</p><p class="text-xs text-neutral-500">{{ $au->roleLabel() }}</p>
          <a href="{{ route('profil') }}" class="mt-3 block rounded-xl bg-ink/5 py-2 text-center text-xs font-semibold text-ink hover:bg-ink/10">Profil &amp; kata sandi</a>
          <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="w-full rounded-xl bg-ink/5 py-2 text-xs font-semibold text-ink hover:bg-ink hover:text-white">Keluar</button></form>
        </div>
      </div>
    </header>

    <main class="px-4 py-6 lg:px-8">
      <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-ink lg:text-[1.75rem]">@yield('title')</h1><p class="text-sm text-neutral-500">@yield('sub')</p></div>
        <div class="flex flex-wrap gap-2">@yield('actions')</div>
      </div>
      @include('partials.alerts')
      @yield('content')
    </main>
  </div>

  <div id="toast" class="pointer-events-none fixed bottom-6 left-1/2 z-[60] max-w-[90vw] -translate-x-1/2 translate-y-24 rounded-full bg-ink px-5 py-3 text-sm font-medium text-white opacity-0 shadow-2xl transition duration-500" role="status" data-flash="{{ session('toast') }}"></div>
  @stack('scripts')
</body>
</html>
