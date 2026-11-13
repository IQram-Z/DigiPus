<form method="GET" action="{{ $action }}" class="flex w-full max-w-md items-center gap-2">
  @foreach (($hidden ?? []) as $k => $v)@if ($v !== null && $v !== '')<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
  <input name="q" value="{{ $q ?? '' }}" placeholder="{{ $placeholder ?? 'Cari...' }}" class="fld" aria-label="Cari">
  <button class="btn-r">Cari</button>
</form>
