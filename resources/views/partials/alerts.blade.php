@if ($errors->any())
  <div class="mb-5 rounded-2xl bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200" role="alert">
    <p class="font-semibold">Periksa kembali isian kamu:</p>
    <ul class="mt-1 list-disc space-y-0.5 pl-5">
      @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif
