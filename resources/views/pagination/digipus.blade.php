@if ($paginator->hasPages())
<nav class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm" role="navigation" aria-label="Halaman">
  <p class="text-neutral-500">{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }}</p>
  <div class="flex items-center gap-1">
    @if ($paginator->onFirstPage())
      <span class="rounded-lg px-3 py-1.5 text-neutral-300">&lsaquo;</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg px-3 py-1.5 hover:bg-neutral-100">&lsaquo;</a>
    @endif
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="px-2 text-neutral-400">{{ $element }}</span>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="rounded-lg bg-ink px-3 py-1.5 font-semibold text-white">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="rounded-lg px-3 py-1.5 hover:bg-neutral-100">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg px-3 py-1.5 hover:bg-neutral-100">&rsaquo;</a>
    @else
      <span class="rounded-lg px-3 py-1.5 text-neutral-300">&rsaquo;</span>
    @endif
  </div>
</nav>
@endif
