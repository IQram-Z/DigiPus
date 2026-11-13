<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tema.css') }}?v={{ @filemtime(public_path('css/tema.css')) }}">
</head>
<body class="min-h-screen bg-white font-sans text-black lg:flex">
  <aside class="relative isolate flex flex-col overflow-hidden rounded-b-[3.5rem] bg-royal px-7 pb-10 pt-8 text-white lg:min-h-screen lg:w-[53%] lg:shrink-0 lg:rounded-b-none lg:rounded-br-[14.375rem] lg:px-0 lg:pb-0 lg:pt-0">
    <span class="absolute -top-28 left-[38%] -z-10 size-[16.25rem] rounded-full bg-ink lg:size-[21.875rem]"></span>
    <p class="text-[1.75rem] font-bold lg:ml-16 lg:mt-[5.75rem] lg:text-[2.25rem]">DigiPus.</p>

    <div class="relative mx-auto mt-4 h-[13.125rem] w-full lg:mt-[4.375rem] lg:h-[20.625rem]">
      <div class="absolute left-1/2 top-0 h-[20rem] w-[29.375rem] origin-top -translate-x-1/2 scale-[.62] lg:scale-100">
        <div class="float absolute left-0 top-[4.375rem] h-[14.5rem] w-[12.25rem] overflow-hidden rounded-[1.375rem] bg-[#f9d3b3] text-black" style="--r:-12deg;--d:0s">
          <span class="absolute -top-6 left-[4.125rem] size-[6rem] rounded-full bg-royal"></span>
          <p class="absolute bottom-[2.375rem] left-6 text-[1.375rem] font-bold leading-tight">Journal</p><p class="absolute bottom-[0.875rem] left-6 text-[1.1875rem] font-light">lorem ipsum</p>
        </div>
        <div class="float absolute left-[9.5625rem] top-0 h-[14.75rem] w-[11.125rem] overflow-hidden rounded-[1.25rem] bg-[#efcb53] text-black" style="--r:0deg;--d:.9s">
          <span class="absolute -right-1 -top-1 size-[7rem] rounded-full bg-amber"></span>
          <p class="absolute bottom-[3.875rem] left-5 text-[1.375rem] font-bold leading-tight">Learning</p><p class="absolute bottom-[2.375rem] left-5 text-[1.1875rem] font-light">lorem ipsum</p>
        </div>
        <div class="float absolute left-[15.375rem] top-[3.625rem] h-[14.25rem] w-[11.625rem] overflow-hidden rounded-[1.375rem] bg-[#75a1ff] text-white" style="--r:14deg;--d:1.7s">
          <span class="absolute -right-3 top-4 size-[7.25rem] rounded-full bg-[#efcb53]"></span>
          <p class="absolute bottom-[3.375rem] left-5 text-[1.375rem] font-bold leading-tight">Novel</p><p class="absolute bottom-[1.875rem] left-5 text-[1.1875rem] font-light">lorem ipsum</p>
        </div>
      </div>
    </div>

    <div class="mt-2 lg:ml-[5.625rem] lg:mt-[3rem] lg:pb-24">
      <h1 class="text-[1.875rem] font-bold leading-tight lg:text-[2.25rem]">Perpustakaan<br>di genggamanmu.</h1>
      <p class="mt-3 text-[0.9375rem] lg:ml-0.5 lg:text-[1.125rem]">cari buku, cek ketersediaan, booking tempat</p>
    </div>
  </aside>

  <main class="flex flex-1 items-center justify-center px-5 py-10 lg:px-10">
    <div class="rise auth-card w-full max-w-[27.375rem] rounded-[2.75rem] border border-neutral-300 bg-white px-[2.125rem] py-9 sm:px-[2.75rem]">
      @yield('form')
    </div>
  </main>
  <div id="toast" class="pointer-events-none fixed bottom-6 left-1/2 z-[60] -translate-x-1/2 translate-y-24 rounded-full bg-ink px-5 py-3 font-pop text-sm font-medium text-white opacity-0 shadow-2xl transition duration-500" role="status"></div>
</body>
</html>
