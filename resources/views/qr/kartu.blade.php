<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kartu {{ $a->no_anggota }} · DigiPus</title>
  @vite(['resources/css/app.css', 'resources/js/scan.js'])
  <style>
    :root { --ink:#191163; --royal:#32269e; --amber:#ef7d00; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { margin: 0; background: #f5f4fb; font-family: Inter, Arial, sans-serif; color: var(--ink); }
    .bar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; background: #fff; box-shadow: 0 2px 12px rgba(25,17,99,.08); }
    .bar h1 { margin: 0; font-size: 1.1rem; } .bar p { margin: 0; font-size: .8rem; color: #6b6b80; }
    .wrap { display: flex; flex-wrap: wrap; gap: 8mm; justify-content: center; padding: 14mm 6mm; }
    /* ukuran kartu ATM/KTP: 85,6 x 54 mm */
    .card { position: relative; width: 85.6mm; height: 54mm; border-radius: 3.5mm; overflow: hidden; box-shadow: 0 4px 18px rgba(25,17,99,.2); break-inside: avoid; }
    .front { background: linear-gradient(140deg, var(--royal), var(--ink)); color: #fff; }
    .front .c1 { position: absolute; right: -14mm; top: -16mm; width: 42mm; height: 42mm; border-radius: 50%; background: var(--ink); opacity: .75; }
    .front .c2 { position: absolute; left: -9mm; bottom: -11mm; width: 26mm; height: 26mm; border-radius: 50%; background: var(--amber); }
    .front .brand { position: absolute; left: 6mm; top: 5mm; font: 800 5mm/1 Inter, Arial, sans-serif; }
    .front .tag { position: absolute; right: 6mm; top: 6mm; font: 600 2.2mm/1 Inter, Arial, sans-serif; letter-spacing: .5mm; opacity: .75; }
    .front .qr { position: absolute; right: 6mm; bottom: 6mm; width: 27mm; height: 27mm; background: #fff; border-radius: 2.5mm; padding: 1.6mm; box-sizing: border-box; }
    .front .qr canvas { width: 100%; height: 100%; display: block; }
    .front .info { position: absolute; left: 6mm; top: 17mm; width: 46mm; }
    .front .lbl { font: 500 2mm/1 Inter, Arial, sans-serif; opacity: .65; text-transform: uppercase; letter-spacing: .3mm; }
    .front .val { margin: .8mm 0 2.4mm; font: 700 3.6mm/1.15 Inter, Arial, sans-serif; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .front .id { font: 700 3.6mm/1.1 'Courier New', monospace; letter-spacing: .2mm; }
    .back { background: #fff; border: .3mm solid #d9d6ea; padding: 5mm 6mm; box-sizing: border-box; }
    .back h2 { margin: 0 0 2mm; font: 800 4mm/1 Inter, Arial, sans-serif; color: var(--royal); }
    .back ol { margin: 0; padding-left: 4mm; font: 400 2.6mm/1.55 Inter, Arial, sans-serif; color: #3b3b50; }
    .back .ft { position: absolute; left: 6mm; right: 6mm; bottom: 4.5mm; font: 400 2.1mm/1.4 Inter, Arial, sans-serif; color: #6b6b80; border-top: .3mm solid #e6e4f2; padding-top: 1.6mm; }
    @page { size: A4; margin: 10mm; }
    @media print { body { background: #fff; } .bar { display: none; } .card { box-shadow: none; } .wrap { padding: 0; justify-content: flex-start; } }
  </style>
</head>
<body>
  <div class="bar">
    <div><h1>Kartu Anggota</h1><p>{{ $a->user->name }} &middot; {{ $a->no_anggota }} &middot; cetak di kertas A4 skala 100%, lalu laminasi</p></div>
    <div style="display:flex;gap:.5rem"><a href="javascript:history.back()" class="btn-l">Kembali</a><button onclick="window.print()" class="btn-s">Cetak</button></div>
  </div>
  <div class="wrap">
    <div class="card front">
      <span class="c1"></span><span class="c2"></span>
      <div class="brand">DigiPus.</div><div class="tag">KARTU ANGGOTA</div>
      <div class="info">
        <div class="lbl">Nama anggota</div><div class="val">{{ $a->user->name }}</div>
        <div class="lbl">ID anggota</div><div class="id">{{ $a->no_anggota }}</div>
        @if ($a->berlaku_sampai)<div class="lbl" style="margin-top:2.4mm">Berlaku sampai</div><div style="font:600 3mm/1.2 Inter,Arial,sans-serif">{{ $a->berlaku_sampai->format('d M Y') }}</div>@endif
      </div>
      <div class="qr"><canvas data-code="{{ $a->no_anggota }}"></canvas></div>
    </div>
    <div class="card back">
      <h2>Cara menggunakan</h2>
      <ol><li>Bawa kartu ini (cetak atau di aplikasi) saat datang ke perpustakaan.</li><li>Tunjukkan QR Code ke petugas.</li><li>Petugas memindai untuk mencatat peminjaman dan pengembalian.</li></ol>
      <div class="ft">Kartu ini milik anggota perpustakaan DigiPus dan tidak dapat dipindahtangankan. Jika ditemukan, mohon kembalikan ke perpustakaan.</div>
    </div>
  </div>
</body>
</html>
