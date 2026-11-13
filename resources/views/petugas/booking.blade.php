@extends('layouts.staff')
@section('title', 'Booking')
@section('sub', 'Siapkan buku untuk anggota yang melakukan booking')
@section('content')
<div class="card">
  @include('partials.tabs', ['route' => 'petugas.booking', 'tab' => $tab, 'q' => null, 'tabs' => [['menunggu', 'Menunggu', $n_menunggu], ['siap', 'Siap diambil', $n_siap], ['selesai', 'Selesai', null]]])

  <div class="mt-4 overflow-x-auto">
    <table class="tbl">
      <thead><tr><th>Anggota</th><th>Buku</th><th>Tgl booking</th><th>Status</th><th>Eksemplar</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse ($items as $b)
          <tr>
            <td><p class="font-semibold">{{ $b->anggota->user->name }}</p><p class="text-xs text-neutral-500">{{ $b->anggota->no_anggota }}</p></td>
            <td><p class="max-w-[16rem] truncate font-semibold">{{ $b->buku->judul }}</p><p class="text-xs text-neutral-500">Rak {{ $b->buku->lokasi_rak }}</p></td>
            <td>{{ $b->tgl_booking->format('d/m/Y H:i') }}</td>
            <td>
              @php $tone = ['menunggu' => 'amber', 'antre' => 'red', 'disiapkan' => 'green', 'diambil' => 'blue', 'dibatalkan' => 'gray'][$b->status_booking] ?? 'gray'; @endphp
              <x-badge :tone="$tone">{{ ucfirst($b->status_booking) }}</x-badge>
            </td>
            <td class="font-mono text-xs">{{ $b->eksemplar?->kode_eksemplar ?? '-' }}</td>
            <td class="text-right">
              <div class="flex justify-end gap-2">
                @if (in_array($b->status_booking, ['menunggu', 'antre'], true))
                  <form method="POST" action="{{ route('petugas.siapkan', $b) }}">@csrf<button class="btn-s sm">Siapkan</button></form>
                @endif
                @if (in_array($b->status_booking, ['menunggu', 'antre', 'disiapkan'], true))
                  <form method="POST" action="{{ route('petugas.batal', $b) }}" data-confirm="Batalkan booking ini?">@csrf<button class="btn-l sm">Batalkan</button></form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-neutral-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links('pagination.digipus') }}
  <p class="mt-3 text-xs text-neutral-500">Saat anggota datang, scan kartunya lalu scan buku di menu Transaksi. Booking terkait otomatis menjadi "Diambil".</p>
</div>
@endsection
