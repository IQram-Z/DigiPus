@props(['name', 'label', 'value' => null, 'rows' => 4, 'hint' => null])
<div {{ $attributes->only('class') }}>
  <label for="{{ $name }}" class="fl">{{ $label }}</label>
  <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" class="fld @error($name) !border-rose-400 @enderror">{{ old($name, $value) }}</textarea>
  @if ($hint)<p class="mt-1 text-xs text-neutral-500">{{ $hint }}</p>@endif
  @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
