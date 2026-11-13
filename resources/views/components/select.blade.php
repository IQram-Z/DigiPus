@props(['name', 'label', 'options' => [], 'value' => null, 'blank' => null])
<div {{ $attributes->only('class') }}>
  <label for="{{ $name }}" class="fl">{{ $label }}</label>
  <select id="{{ $name }}" name="{{ $name }}" class="fld @error($name) !border-rose-400 @enderror" {{ $attributes->except('class') }}>
    @if ($blank !== null)<option value="">{{ $blank }}</option>@endif
    @foreach ($options as $k => $v)
      <option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>
    @endforeach
  </select>
  @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
