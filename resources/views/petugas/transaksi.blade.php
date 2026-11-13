@extends('layouts.staff')
@section('title', 'Transaksi')
@section('sub', 'Verifikasi anggota dengan scan kartu, lalu scan buku untuk meminjamkan atau menerima pengembalian')
@section('content')
<div id="scan-root" data-staff="1" data-member="0" data-preset="{{ request('member') }}" class="grid items-start gap-6 lg:grid-cols-[minmax(0,25rem)_minmax(0,1fr)]">
  @include('partials.scanner-camera', ['qrLink' => true])
  <div class="space-y-6">
    @include('partials.scanner-result', ['mode' => 'staff'])
  </div>
</div>
@push('scripts') @vite('resources/js/scan.js') @endpush
@endsection
