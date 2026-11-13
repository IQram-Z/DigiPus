@extends('layouts.app')
@section('title', 'QR Scan')
@section('content')
<div id="scan-root" data-staff="0" data-member="{{ $member ? 1 : 0 }}" data-join="{{ route('member.join') }}" class="relative min-h-screen overflow-x-clip">
  <div class="absolute inset-x-0 top-0 h-[17rem] overflow-hidden rounded-bl-[4.4rem] bg-royal">
    <div class="absolute inset-y-0 right-0 hidden w-[24%] bg-ink lg:block"></div>
  </div>

  <div class="relative px-5 pb-16 pt-5 lg:pl-[5.125rem] lg:pr-[2.1875rem] lg:pt-[3.625rem]">
    @include('partials.topbar')
    <h1 class="rise mt-7 text-[2.125rem] font-semibold leading-none text-white lg:mt-0">QR Scan</h1>
    <p class="rise mt-2 text-sm text-white/70" style="--i:1">Pindai QR buku{{ $member ? ' atau kartu anggota kamu' : '' }}</p>

    <div class="mt-8 grid items-start gap-7 lg:grid-cols-[minmax(0,25rem)_minmax(0,1fr)]">
      @include('partials.scanner-camera', ['qrLink' => false])

      <div class="space-y-6">
        @include('partials.scanner-result', ['mode' => $member ? 'member' : 'visitor'])

        <section class="rise rounded-3xl bg-ink p-5 text-white shadow-[.75rem_.75rem_0_rgba(0,0,0,.14)]" style="--i:4">
          <div class="flex items-center justify-between"><p class="text-lg font-bold">DigiPus.</p><p class="text-[0.65rem] tracking-widest text-white/60">KARTU ANGGOTA</p></div>
          @if ($member)
            <div class="mt-4 flex flex-wrap items-center gap-5">
              <div class="rounded-xl bg-white p-2"><canvas data-code="{{ $noAnggota }}" class="size-[8.5rem]"></canvas></div>
              <div class="min-w-0">
                <p class="text-[0.65rem] text-white/60">Nama Anggota</p><p class="truncate text-lg font-semibold">{{ $nama }}</p>
                <p class="mt-2 text-[0.65rem] text-white/60">ID Anggota</p><p class="font-semibold tracking-wide">{{ $noAnggota }}</p>
                @if ($berlaku)<p class="mt-2 text-[0.65rem] text-white/60">Berlaku sampai</p><p class="text-sm">{{ $berlaku->format('d M Y') }}</p>@endif
              </div>
            </div>
            <ol class="mt-4 list-decimal space-y-1 pl-5 text-xs text-white/75">
              <li>Buka kartu ini saat datang ke perpustakaan.</li>
              <li>Tunjukkan QR Code ke petugas.</li>
              <li>Petugas memindai untuk mencatat transaksi.</li>
            </ol>
          @else
            <p class="mt-3 text-sm text-white/75">Kamu masih berstatus pengunjung. Kartu anggota digital (dengan QR) dibuat setelah akunmu diverifikasi dan didaftarkan sebagai anggota.</p>
            <a href="{{ route('member.join') }}" class="btn-o mt-4 !h-[2.6rem] !w-auto px-5 !text-sm">Ajukan keanggotaan</a>
          @endif
        </section>
      </div>
    </div>
  </div>
</div>
@push('scripts') @vite('resources/js/scan.js') @endpush
@endsection
