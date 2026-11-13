@extends('layouts.app')
@section('content')
<div class="relative min-h-screen overflow-x-clip">
  <div class="absolute inset-x-0 top-0 h-[16rem] overflow-hidden rounded-bl-[4.4rem] bg-royal">
    <div class="absolute inset-y-0 right-0 hidden w-[24%] bg-ink lg:block"></div>
  </div>
  <div class="relative px-5 pb-16 pt-5 lg:pl-[5.125rem] lg:pr-[2.1875rem] lg:pt-[3.625rem]">
    @include('partials.topbar')
    <h1 class="rise mt-7 text-[2.125rem] font-semibold leading-none text-white lg:mt-0">@yield('heading')</h1>
    <p class="rise mt-2 text-sm text-white/70" style="--i:1">@yield('sub')</p>
    <div class="mt-8">
      @include('partials.alerts')
      @yield('body')
    </div>
  </div>
</div>
@endsection
