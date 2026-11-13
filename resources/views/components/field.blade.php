@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'placeholder' => '', 'required' => false])
<div {{ $attributes->only('class') }}>
  <label for="{{ $name }}" class="fl">{{ $label }}@if ($required) <span class="text-rose-500">*</span>@endif</label>
  <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if ($type !== 'password') value="{{ old($name, $value) }}" @endif placeholder="{{ $placeholder }}" class="fld @error($name) !border-rose-400 @enderror" {{ $attributes->except('class') }}>
  @if ($hint)<p class="mt-1 text-xs text-neutral-500">{{ $hint }}</p>@endif
  @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
