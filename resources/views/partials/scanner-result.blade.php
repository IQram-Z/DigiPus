<div id="active" class="hidden items-center justify-between gap-3 rounded-2xl bg-ink px-4 py-3 text-white">
  <p class="min-w-0 truncate text-sm"><span class="text-white/60">Anggota aktif:</span> <b id="active-name"></b> <span id="active-no" class="text-white/60"></span></p>
  <button id="active-clear" type="button" class="shrink-0 rounded-lg bg-white/15 px-3 py-1 text-xs font-semibold transition hover:bg-white/30">Selesai</button>
</div>

<section id="result" aria-live="polite" class="rise rounded-3xl bg-neutral-50 p-6 text-sm text-neutral-600 ring-1 ring-neutral-200" style="--i:3">
  <p class="font-semibold text-ink">Belum ada hasil pindai</p>
  <p class="mt-1">Arahkan kamera ke QR pada buku (kode <b>DGP-...</b>) atau kartu anggota (kode <b>DP-...</b>).</p>
  @if (($mode ?? 'member') === 'staff')
    <ol class="mt-3 list-decimal space-y-1 pl-5">
      <li>Pindai kartu anggota untuk memverifikasi dan memulai transaksi.</li>
      <li>Pindai buku untuk meminjamkan ke anggota itu (batas buku, denda, dan masa berlaku kartu dicek otomatis).</li>
      <li>Pindai buku yang dikembalikan untuk mencatat pengembalian. Denda dihitung otomatis.</li>
    </ol>
  @elseif ($mode === 'visitor')
    <p class="mt-2">Pindai QR buku untuk melihat detail dan ketersediaannya. Booking khusus anggota.</p>
  @else
    <p class="mt-2">Pindai QR buku untuk melihat detail dan melakukan booking.</p>
  @endif
</section>
