@php $max = max(array_merge([1], $d['loans'], $d['returns'])); @endphp
<div class="flex items-center gap-4 text-xs text-neutral-500">
  <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-royal"></span>Peminjaman</span>
  <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-amber"></span>Pengembalian</span>
</div>
<div class="mt-3 flex items-end gap-1.5 overflow-x-auto pb-1" role="img" aria-label="Grafik peminjaman dan pengembalian">
  @foreach ($d['labels'] as $i => $label)
    <div class="flex min-w-[1.6rem] flex-1 flex-col items-center gap-1.5">
      <div class="flex h-40 w-full items-end justify-center gap-0.5">
        <div class="w-full max-w-[0.7rem] rounded-t bg-royal" style="height:{{ max(2, $d['loans'][$i] / $max * 100) }}%" title="{{ $d['loans'][$i] }} pinjam"></div>
        <div class="w-full max-w-[0.7rem] rounded-t bg-amber" style="height:{{ max(2, $d['returns'][$i] / $max * 100) }}%" title="{{ $d['returns'][$i] }} kembali"></div>
      </div>
      <span class="text-[0.6rem] text-neutral-500">{{ $label }}</span>
    </div>
  @endforeach
</div>
