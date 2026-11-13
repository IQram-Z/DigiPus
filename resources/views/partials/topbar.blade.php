@php $tu = auth()->user(); @endphp
<div class="flex items-center justify-between lg:absolute lg:right-[2.1875rem] lg:top-[1.75rem] lg:z-20 lg:justify-end">
  <button id="menu" class="grid size-10 place-items-center rounded-xl bg-white/15 text-white lg:hidden" aria-label="Buka menu">@include('partials.icons', ['icon' => 'rows', 'cls' => 'size-5'])</button>
  <div class="relative flex items-center gap-4 text-white">
    <button id="user-btn" class="flex items-center gap-3 font-pop text-[0.6875rem]" aria-haspopup="true">
      <img src="{{ $tu->fotoUrl() }}" alt="" referrerpolicy="no-referrer" class="size-[2.375rem] rounded-full object-cover ring-2 ring-white/30">
      <span class="hidden max-w-[6.875rem] truncate sm:inline">{{ $tu->name }}</span>@include('partials.icons', ['icon' => 'down', 'cls' => 'size-3'])
    </button>
    <button class="text-white/90 transition hover:scale-110" data-soon="Notifikasi" aria-label="Notifikasi">@include('partials.icons', ['icon' => 'bell', 'cls' => 'size-[1.125rem]'])</button>
    <div id="user-menu" class="invisible absolute right-0 top-[3.25rem] z-30 w-56 origin-top-right scale-90 rounded-2xl bg-white p-4 text-black opacity-0 shadow-2xl transition duration-300">
      <p class="truncate font-pop text-sm font-semibold">{{ $tu->name }}</p>
      <p class="font-pop text-xs text-neutral-500">{{ $tu->roleLabel() }}@if ($tu->anggota) &middot; {{ $tu->anggota->no_anggota }}@endif</p>
      @if ($tu->isAnggota())
        <div class="mt-2 grid grid-cols-3 gap-1 border-y border-neutral-100 py-2 text-center font-pop">
          <div><p class="text-base font-semibold text-ink">{{ $stats['dipinjam'] ?? 0 }}</p><p class="text-[0.5625rem] text-neutral-500">Dipinjam</p></div>
          <div><p class="text-base font-semibold text-ink">{{ $stats['booking'] ?? 0 }}</p><p class="text-[0.5625rem] text-neutral-500">Booking</p></div>
          <div><p class="text-[0.6875rem] font-semibold leading-6 text-amber">Rp {{ number_format($stats['denda'] ?? 0, 0, ',', '.') }}</p><p class="text-[0.5625rem] text-neutral-500">Denda</p></div>
        </div>
      @endif
      <a href="{{ route('profil') }}" class="mt-3 block rounded-xl bg-ink/5 py-2 text-center font-pop text-xs font-semibold text-ink transition hover:bg-ink/10">Profil &amp; kata sandi</a>
      <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="w-full rounded-xl bg-ink/5 py-2 font-pop text-xs font-semibold text-ink transition hover:bg-ink hover:text-white">Log out</button></form>
    </div>
  </div>
</div>
