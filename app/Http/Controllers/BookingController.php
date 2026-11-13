<?php

namespace App\Http\Controllers;

use App\Models\{Booking, Buku};
use App\Support\Sirkulasi;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function store(Buku $buku)
    {
        $user = Auth::user();
        if (! $user->isAnggota()) {
            return response()->json(['message' => 'Booking khusus anggota perpustakaan. Ajukan keanggotaan lewat menu "Jadi Anggota".'], 403);
        }
        $r = Sirkulasi::booking($user->anggota, $buku);
        return response()->json(['message' => $r['message']], $r['status']);
    }

    /** Anggota membatalkan booking miliknya sendiri. */
    public function destroy(Booking $booking)
    {
        $user = Auth::user();
        abort_unless($user->anggota && $booking->anggota_id === $user->anggota->id, 403);
        $r = Sirkulasi::batalBooking($booking, $user);
        return back()->with('toast', $r['message']);
    }
}
