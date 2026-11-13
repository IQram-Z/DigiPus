@extends('layouts.app')
@section('title', 'Discover')
@section('content')
<div class="relative overflow-x-clip">
  <div class="absolute inset-x-0 top-0 h-[27rem] overflow-hidden rounded-bl-[4.375rem] bg-royal lg:h-[24.6875rem] lg:rounded-bl-[5.625rem]">
    <div class="absolute inset-y-0 right-0 hidden w-[24%] bg-ink lg:block"></div>
  </div>

  <div class="relative px-5 pb-16 pt-5 lg:pl-[5.125rem] lg:pr-[2.1875rem] lg:pt-[3.625rem]">
    @include('partials.topbar')

    <h1 class="rise mt-7 text-[2.125rem] font-semibold leading-none text-white lg:mt-0">{{ $set->judul_dashboard }}</h1>
    @unless ($isMember)
      <a href="{{ route('member.join') }}" class="rise mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-medium text-white transition hover:bg-white/25" style="--i:1">Kamu belum jadi anggota &middot; ajukan keanggotaan untuk booking &amp; kartu digital &rsaquo;</a>
    @endunless

    <form id="search" class="rise mt-6 flex h-[2.6875rem] w-full max-w-[25.1875rem] items-center rounded-[0.625rem] bg-white pl-4 pr-[0.4375rem] font-pop text-[0.75rem] shadow-lg lg:mt-[2.125rem]" style="--i:1">
      <label class="relative flex items-center">
        <select id="cat" class="w-[6rem] appearance-none bg-transparent pr-4 text-neutral-400 outline-none" aria-label="Kategori">
          <option value="">All Categories</option>
          @foreach ($categories as $c)<option>{{ $c['name'] }}</option>@endforeach
        </select>
        @include('partials.icons', ['icon' => 'down', 'cls' => 'pointer-events-none absolute right-0 size-3 text-neutral-400'])
      </label>
      <span class="mx-3 h-6 w-px bg-neutral-300"></span>
      @include('partials.icons', ['icon' => 'search', 'cls' => 'size-4 shrink-0 text-neutral-500'])
      <input id="q" type="search" placeholder="find the book you like..." class="mx-3 min-w-0 flex-1 bg-transparent text-neutral-700 outline-none placeholder:text-neutral-400" aria-label="Cari buku">
      <button class="h-[1.8125rem] rounded-md bg-amber px-4 text-[0.6875rem] font-medium text-white transition hover:brightness-110 active:scale-95">Search</button>
    </form>

    <div class="rise mt-10 flex items-center justify-between lg:mt-[3.25rem]" style="--i:2">
      <h2 class="text-[1.375rem] font-normal text-white lg:text-[1.625rem]">{{ $set->judul_rekomendasi }}</h2>
      <div class="flex items-center gap-2">
        <button data-rail="prev" class="hidden size-[1.875rem] place-items-center rounded-full bg-white/15 text-white transition hover:bg-white/30 active:scale-90 disabled:opacity-30 sm:grid" aria-label="Geser ke kiri">@include('partials.icons', ['icon' => 'left', 'cls' => 'size-4'])</button>
        <button data-rail="next" class="hidden size-[1.875rem] place-items-center rounded-full bg-white/15 text-white transition hover:bg-white/30 active:scale-90 disabled:opacity-30 sm:grid" aria-label="Geser ke kanan">@include('partials.icons', ['icon' => 'right', 'cls' => 'size-4'])</button>
        <button data-viewall class="inline-flex h-[1.875rem] items-center gap-1.5 rounded-lg bg-white pl-4 pr-3 font-pop text-[0.8125rem] font-medium text-black shadow transition hover:-translate-y-0.5 hover:shadow-lg active:scale-95">View all @include('partials.icons', ['icon' => 'right', 'cls' => 'size-4'])</button>
      </div>
    </div>

    <div id="rail" class="rail no-sb -mr-5 mt-6 flex overflow-x-auto pb-7 pr-8 pt-3 lg:mr-0 lg:pr-3">
      @foreach ($featured as $b)
        <button type="button" data-open="{{ $b['id'] }}" style="--i:{{ $loop->index }}" class="rail-item lift shelf-in snap-start text-left" aria-label="Lihat detail {{ $b['title'] }}">
          @include('partials.cover', ['cvsrc' => $b['cover'], 'cvalt' => 'Sampul '.$b['title'], 'cvsd' => '1.1rem'])
        </button>
      @endforeach
    </div>

    <div class="mt-14 flex items-center justify-between lg:mt-24">
      <h2 class="text-[1.5rem] font-medium text-neutral-500 lg:text-[1.75rem]">{{ $set->judul_kategori }}</h2>
      <button data-morecat class="grid size-[2.125rem] place-items-center rounded-lg border border-neutral-400 text-neutral-600 transition hover:bg-neutral-100 active:scale-90" aria-label="Tampilkan semua kategori" aria-expanded="false">@include('partials.icons', ['icon' => 'rows', 'cls' => 'size-5'])</button>
    </div>
    <div class="mt-12 grid grid-cols-2 gap-x-5 gap-y-12 sm:grid-cols-4">
      @foreach ($categories as $c)
        <button type="button" data-cat="{{ $c['name'] }}" style="--i:{{ $loop->index }}" class="tile-cat {{ $loop->index >= 4 ? 'hidden extra' : '' }} text-center" aria-label="Kategori {{ $c['name'] }}">
          <div class="relative mx-auto h-[10.0625rem] w-full max-w-[11.0625rem]">
            <div class="plate absolute inset-x-0 bottom-0 h-[4.25rem] origin-bottom rounded-xl bg-plate"></div>
            <div class="tile-back absolute bottom-0 left-1/2 aspect-[2/3] w-[6.9rem] -translate-x-1/2 rounded-[0.2rem]"></div>
            <div class="tile-lift absolute bottom-0 left-1/2 -translate-x-1/2 shadow-md">@include('partials.cover', ['cvsrc' => $c['book']['cover'], 'cvalt' => '', 'cvw' => '6.9rem', 'cvsd' => '0px'])</div>
          </div>
          <p class="mt-4 text-[1.0625rem] font-bold">{{ $c['name'] }}</p>
        </button>
      @endforeach
    </div>

    <div id="collection" class="scroll-mt-6">
      <div>
        <div class="flex flex-wrap items-end justify-between gap-2 pt-12">
          <h2 class="text-[1.75rem] font-bold text-ink lg:text-[2rem]">{{ $set->judul_koleksi }}</h2>
          <button data-clear class="invisible font-pop text-xs font-semibold text-chip hover:underline">Reset filter ✕</button>
        </div>
        <p id="info" class="mt-1 font-pop text-xs text-neutral-500"></p>
        <div class="mt-6 grid grid-cols-2 gap-x-6 gap-y-9 pb-6 pr-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
          @foreach ($books as $b)
            <button type="button" data-open="{{ $b['id'] }}" data-cat="{{ $b['cat'] }}" data-title="{{ mb_strtolower($b['title'].' '.$b['author']) }}" class="book-card lift w-full max-w-[11rem] text-left" aria-label="Lihat detail {{ $b['title'] }}">
              @include('partials.cover', ['cvsrc' => $b['cover'], 'cvalt' => 'Sampul '.$b['title'], 'cvsd' => '.5rem', 'cvlazy' => true])
              <div class="mt-3"><p class="line-clamp-2 min-h-[2.1em] text-[0.8125rem] font-bold leading-tight">{{ $b['title'] }}</p><p class="mt-0.5 truncate text-[0.6875rem] text-neutral-600">{{ $b['author'] }}</p></div>
            </button>
          @endforeach
        </div>
        <p id="empty" class="hidden pb-10 text-center font-pop text-sm text-neutral-500">Buku tidak ditemukan. Coba kata kunci lain atau reset filter.</p>
      </div>
    </div>
  </div>
</div>

{{-- Pop-up detail buku (tampilan mengikuti frame "Detail Buku" di Figma) --}}
<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-8" role="dialog" aria-modal="true" aria-labelledby="m-title">
  <div id="m-back" class="absolute inset-0 bg-ink/85"></div>
  <div class="relative flex max-h-full w-full max-w-[62.5rem] flex-col items-center overflow-y-auto p-3 md:flex-row md:overflow-visible">
    <div id="m-cover" class="hard relative z-10 w-[9.375rem] shrink-0 border-[0.4375rem] border-ink bg-ink md:w-[16.375rem]"><img id="m-img" alt="" decoding="async" class="block aspect-[2/3] w-full object-cover"></div>
    <div id="m-info" class="-mt-3 w-full flex-1 rounded-[1.75rem] bg-[#f7f7f7] p-6 pt-8 shadow-[.75rem_.75rem_0_rgba(0,0,0,.28)] md:-ml-5 md:mt-0 md:p-8 md:pl-12">
      <div data-s id="m-tags" class="flex flex-wrap gap-3"></div>
      <h3 data-s id="m-title" class="mt-3 text-[2rem] font-bold leading-tight text-ink md:text-[2.5rem]"></h3>
      <p data-s id="m-author" class="mt-1 text-[1.1875rem]"></p>
      <div data-s class="mt-2 flex items-center gap-1" id="m-stars"></div>
      <div data-s class="mt-3 flex flex-wrap items-center gap-3">
        <span id="m-avail" class="inline-block rounded-full border-2 border-ink px-4 py-1 text-[0.75rem] font-bold text-ink"></span>
        <span id="m-shelf" class="font-pop text-xs text-neutral-500"></span>
      </div>
      <p data-s id="m-syn" class="syn mt-4 h-[9.5rem] overflow-y-auto rounded-[1.375rem] bg-[#efefef] p-5 text-justify text-[0.875rem] leading-relaxed"></p>
      <div data-s class="mt-5 flex flex-wrap gap-3">
        <button id="m-book" class="btn-o !h-[2.875rem] !w-auto !rounded-xl px-7 !text-[0.9375rem]"></button>
        <button id="m-close" class="btn-line !h-[2.875rem] !w-auto !rounded-xl px-7">Tutup</button>
      </div>
    </div>
  </div>
</div>
<script id="books-data" type="application/json" data-member="{{ $isMember ? 1 : 0 }}" data-join="{{ route('member.join') }}">@json($books)</script>
@endsection
