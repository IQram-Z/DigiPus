<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cetak QR Buku · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/scan.js'])
  <style>
    :root { --ink:#191163; --royal:#32269e; --amber:#ef7d00; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { margin: 0; background: #f5f4fb; font-family: Inter, Arial, sans-serif; color: var(--ink); }
    .bar { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; background: #fff; box-shadow: 0 2px 12px rgba(25,17,99,.08); }
    .bar h1 { margin: 0; font-size: 1.1rem; } .bar p { margin: 0; font-size: .8rem; color: #6b6b80; }
    .sheet { display: flex; flex-wrap: wrap; gap: 3mm; padding: 6mm; max-width: 210mm; margin: 0 auto; }
    /* ---- label standar 64 x 36 mm ---- */
    .std { width: 64mm; height: 36mm; display: flex; background: #fff; border: .3mm dashed #b9b4d6; border-radius: 3mm; overflow: hidden; break-inside: avoid; }
    .std .l { width: 33mm; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1mm; background: #fff; }
    .std .l canvas { width: 26mm; height: 26mm; border: .4mm solid var(--ink); border-radius: 1.5mm; padding: .6mm; box-sizing: content-box; }
    .std .l b { font: 700 2.3mm/1 'Courier New', monospace; letter-spacing: .1mm; }
    .std .r { flex: 1; background: linear-gradient(160deg, var(--royal), var(--ink)); color: #fff; padding: 2.6mm 2.4mm; display: flex; flex-direction: column; position: relative; overflow: hidden; }
    .std .r::after { content: ''; position: absolute; right: -6mm; bottom: -6mm; width: 14mm; height: 14mm; border-radius: 50%; background: var(--amber); opacity: .9; }
    .std .brand { font: 800 3mm/1 Inter, Arial, sans-serif; letter-spacing: .1mm; }
    .std .ttl { margin-top: 2mm; font: 700 3mm/1.2 Inter, Arial, sans-serif; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .std .by { margin-top: 1mm; font: 400 2.2mm/1.2 Inter, Arial, sans-serif; opacity: .8; }
    .std .rak { position: absolute; left: 2.4mm; bottom: 2.4mm; z-index: 1; background: #fff; color: var(--ink); border-radius: 5mm; padding: .6mm 2mm; font: 700 2.3mm/1.3 Inter, Arial, sans-serif; }
    /* ---- stiker kecil 32 x 40 mm ---- */
    .stk { width: 32mm; height: 40mm; background: #fff; border: .3mm dashed #b9b4d6; border-radius: 3mm; overflow: hidden; display: flex; flex-direction: column; align-items: center; break-inside: avoid; }
    .stk .hd { width: 100%; background: var(--ink); color: #fff; text-align: center; font: 800 2.8mm/1 Inter, Arial, sans-serif; padding: 1.4mm 0; position: relative; }
    .stk .hd::after { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 4mm; background: var(--amber); }
    .stk canvas { width: 24mm; height: 24mm; margin-top: 1.6mm; }
    .stk b { margin-top: 1mm; font: 700 2.4mm/1 'Courier New', monospace; }
    .stk span { margin-top: .6mm; font: 400 2mm/1 Inter, Arial, sans-serif; color: #6b6b80; }
    @page { size: A4; margin: 8mm; }
    @media print { body { background: #fff; } .bar { display: none; } .sheet { padding: 0; max-width: none; } }
  </style>
</head>
<body>
  <div class="bar">
    <div><h1>Cetak QR Buku</h1><p>{{ $items->count() }} label &middot; model {{ $tpl === 'stiker' ? 'stiker kecil' : 'standar' }} &middot; atur skala cetak 100% (tanpa "fit to page")</p></div>
    <div style="display:flex;gap:.5rem">
      <a href="{{ request()->fullUrlWithQuery(['t' => $tpl === 'stiker' ? 'standar' : 'stiker']) }}" class="btn-l">Ganti ke {{ $tpl === 'stiker' ? 'standar' : 'stiker' }}</a>
      <button onclick="window.print()" class="btn-s">Cetak</button>
    </div>
  </div>

  @if ($items->isEmpty())
    <p style="padding:3rem;text-align:center;color:#6b6b80">Tidak ada eksemplar yang dipilih.</p>
  @endif
  <div class="sheet">
    @foreach ($items as $e)
      @if ($tpl === 'stiker')
        <div class="stk"><div class="hd">DigiPus.</div><canvas data-code="{{ $e->kode_eksemplar }}"></canvas><b>{{ $e->kode_eksemplar }}</b><span>Rak {{ $e->buku->lokasi_rak ?: '-' }}</span></div>
      @else
        <div class="std">
          <div class="l"><canvas data-code="{{ $e->kode_eksemplar }}"></canvas><b>{{ $e->kode_eksemplar }}</b></div>
          <div class="r"><div class="brand">DigiPus.</div><div class="ttl">{{ $e->buku->judul }}</div><div class="by">{{ $e->buku->penulis }}</div><div class="rak">Rak {{ $e->buku->lokasi_rak ?: '-' }}</div></div>
        </div>
      @endif
    @endforeach
  </div>
</body>
</html>
