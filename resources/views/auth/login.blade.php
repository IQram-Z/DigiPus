@extends('layouts.auth')
@section('title', 'Masuk')
@section('form')
@php $start = ($errors->any() && old('login')) ? 2 : 1; @endphp
<h2 class="text-center text-[1.625rem] font-bold text-ink">Welcome back</h2>
<form id="login-form" method="POST" action="{{ route('login') }}" novalidate data-start="{{ $start }}">
  @csrf
  <div data-pane="1" class="{{ $start === 2 ? 'hidden' : '' }}">
    <p class="mb-4 mt-4 text-center font-pop text-[1.1875rem] leading-snug">What’s your username or email?</p>
    <input id="login" name="login" value="{{ old('login') }}" class="inp {{ $errors->has('login') && $start === 1 ? 'err' : '' }}" placeholder="Enter your username or email" autocomplete="username" autofocus>
    <p data-err class="mt-1.5 pl-1 font-pop text-xs text-rose-600" role="alert">{{ $start === 1 ? $errors->first('login') : '' }}</p>
    <button type="button" data-next class="btn-o mt-4">Continue</button>
  </div>
  <div data-pane="2" class="{{ $start === 2 ? '' : 'hidden' }}">
    <p class="mb-4 mt-4 text-center font-pop text-[1.1875rem] leading-snug">Enter your password</p>
    <div class="relative">
      <input id="password" type="password" name="password" class="inp pr-14 {{ $errors->any() && $start === 2 ? 'err' : '' }}" placeholder="Your password" autocomplete="current-password">
      <button type="button" data-eye class="absolute right-4 top-1/2 -translate-y-1/2 text-black" aria-label="Tampilkan kata sandi">@include('partials.icons', ['icon' => 'eye', 'cls' => 'size-6'])</button>
    </div>
    <p class="mt-1.5 pl-1 font-pop text-xs text-rose-600" role="alert">{{ $start === 2 ? ($errors->first('login') ?: $errors->first('password')) : '' }}</p>
    <button class="btn-o mt-4">Continue</button>
    <button type="button" data-back class="mx-auto mt-3 block font-pop text-xs font-medium text-neutral-500 hover:text-ink">← Use a different username or email</button>
  </div>
</form>
<div class="my-4 flex items-center gap-3 font-pop text-xs font-semibold text-neutral-600"><span class="h-px flex-1 bg-neutral-500"></span>or<span class="h-px flex-1 bg-neutral-500"></span></div>
@include('partials.social', ['layout' => 'stack'])
<p class="mt-5 text-center font-pop text-[0.8125rem] font-semibold text-neutral-500">Don’t have an account? <a class="text-chip underline underline-offset-2" href="{{ route('register') }}">Register</a></p>
<p class="mt-3 rounded-xl bg-amber/10 px-3 py-2 text-center font-pop text-[0.6875rem] leading-relaxed text-amber-800">Akun uji: <b>demo</b> (anggota), <b>tamu</b> (pengunjung), <b>petugas</b>, <b>admin</b><br>kata sandi <b>digipus123</b></p>
@endsection
