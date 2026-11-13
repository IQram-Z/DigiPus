@extends('layouts.staff')
@section('title', 'Peminjaman')
@section('sub', 'Daftar peminjaman dan penerimaan pengembalian')
@section('content')
<div class="card">
  <div class="flex flex-wrap items-center justify-between gap-3">
    @include('partials.tabs', ['route' => 'petugas.peminjaman', 'tab' => $tab, 'q' => $q, 'tabs' => [['aktif', 'Aktif', $n_aktif], ['terlambat', 'Terlambat', $n_telat], ['selesai', 'Selesai', null]]])
    @include('partials.search', ['action' => route('petugas.peminjaman'), 'q' => $q, 'hidden' => ['tab' => $tab], 'placeholder' => 'Cari anggota, buku, atau kode...'])
  </div>

  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Peminjam</th><th>Buku</th><th>Pinjam</th><th>Tenggat</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $p)
          @php $hari = $p->status_peminjaman === 'dipinjam' ? \App\Support\Sirkulasi::hariTerlambat($p) : 0; @endphp
          <tr>
            <td><p class="font-semibold">{{ $p->anggota->user->name }}</p><p class="text-xs text-neutral-500">{{ $p->anggota->no_anggota }}</p></td>
            <td><p class="max-w-[16rem] truncate font-semibold">{{ $p->eksemplar->buku->judul }}</p><p class="font-mono text-xs text-neutral-500">{{ $p->eksemplar->kode_eksemplar }}</p></td>
            <td>{{ $p->tgl_pinjam->format('d/m/Y') }}</td>
            <td>{{ $p->tgl_tenggat->format('d/m/Y') }}</td>
            <td>
              @if ($p->status_peminjaman !== 'dipinjam')<x-badge>Selesai</x-badge>
              @elseif ($hari > 0)<x-badge tone="red">Terlambat {{ $hari }} hari &middot; Rp {{ number_format($hari * $tarif, 0, ',', '.') }}</x-badge>
              @else<x-badge tone="green">Dipinjam</x-badge>@endif
            </td>
            <td class="text-right">
              @if ($p->status_peminjaman === 'dipinjam')
                <form method="POST" action="{{ route('petugas.kembali', $p) }}" data-confirm="Terima pengembalian buku ini?">@csrf<button class="btn-r sm">Terima kembali</button></form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
</div>
@endsection
