<div class="flex flex-wrap items-center gap-1.5">
  @foreach ($tabs as [$key, $label, $count])
    <a href="{{ route($route, ['tab' => $key] + (! empty($q) ? ['q' => $q] : [])) }}" class="tab-i {{ $tab === $key ? 'on' : '' }}">{{ $label }}@if ($count !== null) <span class="rounded-full px-1.5 text-[0.7rem] {{ $tab === $key ? 'bg-white/20' : 'bg-neutral-200 text-neutral-700' }}">{{ $count }}</span>@endif</a>
  @endforeach
</div>
