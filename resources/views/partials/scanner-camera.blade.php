<section class="rise rounded-3xl bg-white p-4 shadow-[.75rem_.75rem_0_rgba(0,0,0,.14)] ring-1 ring-neutral-200" style="--i:2">
  <div class="relative aspect-square overflow-hidden rounded-2xl bg-ink">
    <video id="cam" playsinline muted class="h-full w-full object-cover"></video>
    <div id="cam-off" class="absolute inset-0 grid place-items-center p-6 text-center text-sm text-white/80">Kamera belum menyala. Tekan tombol di bawah untuk mulai memindai.</div>
    <div id="cam-frame" class="pointer-events-none absolute inset-[12%] hidden">
      <span class="absolute left-0 top-0 size-8 rounded-tl-xl border-l-4 border-t-4 border-amber"></span>
      <span class="absolute right-0 top-0 size-8 rounded-tr-xl border-r-4 border-t-4 border-amber"></span>
      <span class="absolute bottom-0 left-0 size-8 rounded-bl-xl border-b-4 border-l-4 border-amber"></span>
      <span class="absolute bottom-0 right-0 size-8 rounded-br-xl border-b-4 border-r-4 border-amber"></span>
      <div class="scan-sweep absolute inset-0"><div class="h-0.5 w-full bg-amber shadow-[0_0_.75rem_.2rem_rgba(239,125,0,.6)]"></div></div>
    </div>
  </div>
  <canvas id="cv" class="hidden"></canvas>

  <button id="cam-toggle" type="button" class="btn-o mt-4 !h-[2.9rem] !text-[0.95rem]">Nyalakan kamera</button>
  <div class="mt-3 grid {{ ($qrLink ?? false) ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
    <label class="btn-line !h-[2.6rem] cursor-pointer !text-[0.8rem]">Unggah gambar QR<input id="qr-file" type="file" accept="image/*" class="hidden"></label>
    @if ($qrLink ?? false)<a href="{{ route('qr.index') }}" class="btn-line !h-[2.6rem] !text-[0.8rem]">Cetak QR</a>@endif
  </div>
  <form id="manual" class="mt-3 flex gap-2">
    <input id="manual-code" class="inp !h-[2.6rem] !rounded-xl !text-[0.8rem]" placeholder="Ketik kode: DGP-001-01 atau DP-2026-0142" autocomplete="off">
    <button class="btn-line !h-[2.6rem] !w-auto !rounded-xl px-4 !text-[0.8rem]">Cari</button>
  </form>
</section>
