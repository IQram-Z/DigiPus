<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tema.css') }}?v={{ @filemtime(public_path('css/tema.css')) }}">
</head>
<body class="min-h-screen bg-white font-sans text-black">
  <div id="scrim" class="pointer-events-none fixed inset-0 z-30 bg-ink/50 opacity-0 transition-opacity duration-500 lg:hidden"></div>
  @php
    $nu = auth()->user();
    $nav = [
      ['Discover', route('dashboard'), 'home', 'discover', request()->routeIs('dashboard')],
      ['Book Collection', route('dashboard') . '#collection', 'library', 'collection', false],
    ];
    if ($nu->isAnggota()) {
      $nav[] = ['Peminjaman Saya', route('member.peminjaman'), 'clock', null, request()->routeIs('member.peminjaman')];
    } else {
      $nav[] = ['Jadi Anggota', route('member.join'), 'card', null, request()->routeIs('member.join')];
    }
    $nav[] = ['QR Scan', route('scan'), 'qr', 'scan', request()->routeIs('scan')];
  @endphp
  <aside id="side" class="fixed inset-y-0 left-0 z-40 flex w-[16rem] -translate-x-full flex-col bg-white px-[1.75rem] pt-9 shadow-2xl transition-transform duration-500 ease-out lg:w-[16.5rem] lg:translate-x-0 lg:px-[2.375rem] lg:shadow-none">
    <p class="text-[1.375rem] font-bold">DigiPus.</p>
    <p class="mb-4 mt-[2rem] text-[0.75rem] font-medium text-neutral-500">Menu</p>
    <nav class="space-y-[1.15rem]">
      @foreach ($nav as [$label, $href, $icon, $key, $on])
        <a href="{{ $href }}" @if ($key) data-nav="{{ $key }}" @endif class="nav-i {{ $on ? 'on' : '' }}"><span class="tile">@include('partials.icons', ['icon' => $icon])</span>{{ $label }}</a>
      @endforeach
    </nav>
    <hr class="-mx-1 my-[1.4rem] border-neutral-300 lg:-mx-1.5">
    <nav class="space-y-[1.15rem]">
      <a href="{{ route('profil') }}" class="nav-i {{ request()->routeIs('profil') ? 'on' : '' }}"><span class="tile">@include('partials.icons', ['icon' => 'user'])</span>Profil</a>
      <a href="#" data-soon="Help" class="nav-i"><span class="tile">@include('partials.icons', ['icon' => 'help'])</span>Help</a>
    </nav>
    <hr class="-mx-1 my-[1.4rem] border-neutral-300 lg:-mx-1.5">
  </aside>

  <main class="lg:ml-[16.5rem]">@yield('content')</main>

  <div id="toast" class="pointer-events-none fixed bottom-6 left-1/2 z-[60] -translate-x-1/2 translate-y-24 rounded-full bg-ink px-5 py-3 font-pop text-sm font-medium text-white opacity-0 shadow-2xl transition duration-500" role="status" data-flash="{{ session('toast') }}"></div>
  @stack('scripts')
</body>
</html>
