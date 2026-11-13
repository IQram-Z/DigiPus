<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') · Panel Petugas · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tema.css') }}?v={{ @filemtime(public_path('css/tema.css')) }}">
</head>
<body class="min-h-screen bg-[#f5f4fb] font-sans text-neutral-800">
  @php
    $su = auth()->user();
    $menunggu = \App\Models\Booking::whereIn('status_booking', ['menunggu', 'antre'])->count();
    $nav = [
      ['Beranda', 'petugas.dashboard', 'layout', 'petugas.dashboard', 0],
      ['Transaksi', 'petugas.transaksi', 'scan', 'petugas.transaksi', 0],
      ['Peminjaman', 'petugas.peminjaman', 'book', 'petugas.peminjaman', 0],
      ['Booking', 'petugas.booking', 'inbox', 'petugas.booking', $menunggu],
      ['Denda', 'petugas.denda', 'wallet', 'petugas.denda', 0],
      ['Anggota', 'petugas.anggota*', 'users', 'petugas.anggota*', 0],
      ['Laporan', 'petugas.laporan', 'chart', 'petugas.laporan', 0],
      ['Cetak QR', 'qr.index', 'printer', 'qr.*', 0],
    ];
  @endphp
  <header class="sticky top-0 z-30 bg-royal text-white shadow-lg shadow-ink/20">
    <div class="mx-auto flex max-w-[92rem] items-center gap-3 px-4 py-3 lg:gap-5 lg:px-8">
      <a href="{{ route('petugas.dashboard') }}" class="shrink-0 text-xl font-extrabold">DigiPus.<span class="ml-2 rounded-md bg-amber px-2 py-0.5 align-middle text-[0.6rem] font-bold tracking-widest">PETUGAS</span></a>
      <nav class="no-sb flex flex-1 gap-1 overflow-x-auto" aria-label="Menu petugas">
        @foreach ($nav as [$label, $name, $icon, $pattern, $count])
          <a href="{{ route(rtrim($name, '*') === $name ? $name : rtrim($name, '*')) }}" class="pill-i {{ request()->routeIs($pattern) ? 'on' : '' }}">
            @include('partials.icons', ['icon' => $icon]){{ $label }}@if ($count) <span class="rounded-full bg-amber px-1.5 text-[0.65rem] font-bold text-white">{{ $count }}</span>@endif
          </a>
        @endforeach
      </nav>
      <div class="relative shrink-0">
        <button id="user-btn" class="flex items-center gap-2 text-xs" aria-haspopup="true">
          <img src="{{ $su->fotoUrl() }}" alt="" referrerpolicy="no-referrer" class="size-9 rounded-full object-cover ring-2 ring-white/30">
          <span class="hidden max-w-[8rem] truncate sm:inline">{{ $su->name }}</span>@include('partials.icons', ['icon' => 'down', 'cls' => 'size-3'])
        </button>
        <div id="user-menu" class="invisible absolute right-0 top-12 z-40 w-56 origin-top-right scale-90 rounded-2xl bg-white p-4 text-black opacity-0 shadow-2xl transition duration-300">
          <p class="truncate text-sm font-semibold">{{ $su->name }}</p>
          <p class="text-xs text-neutral-500">{{ $su->roleLabel() }}</p>
          @if ($su->isAdmin())<a href="{{ route('admin.dashboard') }}" class="mt-3 block rounded-xl bg-ink/5 py-2 text-center text-xs font-semibold text-ink hover:bg-ink/10">Buka panel Admin</a>@endif
          <a href="{{ route('profil') }}" class="mt-2 block rounded-xl bg-ink/5 py-2 text-center text-xs font-semibold text-ink hover:bg-ink/10">Profil &amp; kata sandi</a>
          <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="w-full rounded-xl bg-ink/5 py-2 text-xs font-semibold text-ink hover:bg-ink hover:text-white">Keluar</button></form>
        </div>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-[92rem] px-4 py-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
      <div><h1 class="text-2xl font-bold text-ink lg:text-[1.75rem]">@yield('title')</h1><p class="text-sm text-neutral-500">@yield('sub')</p></div>
      <div class="flex flex-wrap gap-2">@yield('actions')</div>
    </div>
    @include('partials.alerts')
    @yield('content')
  </main>

  <div id="toast" class="pointer-events-none fixed bottom-6 left-1/2 z-[60] -translate-x-1/2 translate-y-24 rounded-full bg-ink px-5 py-3 text-sm font-medium text-white opacity-0 shadow-2xl transition duration-500" role="status" data-flash="{{ session('toast') }}"></div>
  @stack('scripts')
</body>
</html>
