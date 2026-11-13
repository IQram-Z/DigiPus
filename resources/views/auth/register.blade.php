@extends('layouts.auth')
@section('title', 'Daftar')
@section('form')
<h2 class="mb-1 text-center text-[1.625rem] font-bold text-ink">Create an account</h2>
<form method="POST" action="{{ route('register') }}" novalidate>
  @csrf
  @foreach ([['name','Full Name','text','Your full name','name'],['email','Email Address','email','name@email.com','email'],['password','Password','password','At least 6 characters','new-password']] as [$n,$l,$t,$p,$ac])
    <label class="lbl" for="{{ $n }}">{{ $l }}</label>
    <div class="relative">
      <input id="{{ $n }}" name="{{ $n }}" type="{{ $t }}" placeholder="{{ $p }}" autocomplete="{{ $ac }}" @if($t !== 'password') value="{{ old($n) }}" @endif class="inp {{ $t === 'password' ? 'pr-14' : '' }} {{ $errors->has($n) ? 'err' : '' }}">
      @if ($t === 'password')
        <button type="button" data-eye class="absolute right-4 top-1/2 -translate-y-1/2 text-black" aria-label="Tampilkan kata sandi">@include('partials.icons', ['icon' => 'eye', 'cls' => 'size-6'])</button>
      @endif
    </div>
    @error($n)<p class="mt-1.5 pl-1 font-pop text-xs text-rose-600" role="alert">{{ $message }}</p>@enderror
  @endforeach
  <button class="btn-o mt-6">Submit</button>
</form>
<div class="mt-4">@include('partials.social', ['layout' => 'row'])</div>
<p class="mt-5 text-center font-pop text-[0.8125rem] font-semibold text-neutral-500">Already have an account? <a class="text-chip underline underline-offset-2" href="{{ route('login') }}">Login</a></p>
@endsection
