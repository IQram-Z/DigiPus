@props(['label', 'value', 'icon' => 'chart', 'tone' => 'royal', 'hint' => null, 'href' => null])
@php
  $tones = ['royal' => 'bg-royal/10 text-royal', 'amber' => 'bg-amber/15 text-amber', 'green' => 'bg-emerald-100 text-emerald-700', 'red' => 'bg-rose-100 text-rose-600'];
@endphp
<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif class="kpi block {{ $href ? 'transition hover:-translate-y-0.5' : '' }}">
  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0">
      <p class="text-xs font-medium text-neutral-500">{{ $label }}</p>
      <p class="mt-1 truncate text-2xl font-bold text-ink">{{ $value }}</p>
      @if ($hint)<p class="mt-0.5 text-xs text-neutral-500">{{ $hint }}</p>@endif
    </div>
    <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['royal'] }}">@include('partials.icons', ['icon' => $icon, 'cls' => 'size-5'])</span>
  </div>
</{{ $href ? 'a' : 'div' }}>
