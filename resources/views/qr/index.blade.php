@extends($layout)
@section('title', 'Cetak QR')
@section('sub', 'Pilih eksemplar untuk dicetak sebagai label QR. Kartu anggota dicetak dari menu Anggota.')
@section('content')
<form method="GET" action="{{ route('qr.labels') }}" target="_blank" class="space-y-6">
  <section class="card">
    <h2 class="ttl">Model label</h2>
    <div class="mt-3 grid gap-3 sm:grid-cols-2">
      <label class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-neutral-200 p-4 has-[:checked]:border-royal has-[:checked]:bg-royal/5">
        <input type="radio" name="t" value="standar" class="accent-[#32269e]" checked>
        <span><b class="text-ink">Label standar</b><br><span class="text-xs text-neutral-500">64 x 36 mm: QR + judul, penulis, kode, rak. Untuk punggung/sampul buku.</span></span>
      </label>
      <label class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-neutral-200 p-4 has-[:checked]:border-royal has-[:checked]:bg-royal/5">
        <input type="radio" name="t" value="stiker" class="accent-[#32269e]">
        <span><b class="text-ink">Stiker kecil</b><br><span class="text-xs text-neutral-500">32 x 40 mm: QR + kode. Untuk buku tipis atau rak.</span></span>
      </label>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
      <button class="btn-s">@include('partials.icons', ['icon' => 'printer', 'cls' => 'size-4']) Cetak yang dipilih</button>
      <button name="all" value="1" class="btn-r">Cetak semua eksemplar</button>
      <button type="button" data-check-all="1" class="btn-l">Pilih semua</button>
      <button type="button" data-check-all="0" class="btn-l">Kosongkan</button>
    </div>
  </section>

  <section class="card">
    <h2 class="ttl">Pilih eksemplar</h2>
    <div class="mt-3 grid gap-3 lg:grid-cols-2">
      @foreach ($buku as $b)
        <div class="rounded-2xl bg-neutral-50 p-3">
          <label class="flex items-center gap-3 font-semibold"><input type="checkbox" data-book-all="{{ $b->id }}" class="size-4 accent-[#32269e]"><img src="{{ $b->coverUrl() }}" alt="" class="h-10 w-7 rounded object-cover"><span class="min-w-0 flex-1 truncate">{{ $b->judul }}</span><x-badge tone="blue">{{ $b->eksemplar->count() }}</x-badge></label>
          <div class="mt-2 flex flex-wrap gap-2 pl-7">
            @foreach ($b->eksemplar as $e)
              <label class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2 py-1 font-mono text-xs ring-1 ring-neutral-200"><input type="checkbox" name="ids[]" value="{{ $e->id }}" data-eks data-book="{{ $b->id }}" class="accent-[#32269e]">{{ $e->kode_eksemplar }}</label>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </section>
</form>
@endsection
