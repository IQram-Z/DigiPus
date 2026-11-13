<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Anggota extends Model
{
    protected $table = 'anggota';
    protected $guarded = [];
    protected $casts = ['tgl_daftar' => 'date', 'berlaku_sampai' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function peminjaman() { return $this->hasMany(Peminjaman::class); }
    public function booking() { return $this->hasMany(Booking::class); }

    /** Nomor anggota unik berikutnya: DP-{tahun}-{urut 4 digit}. */
    public static function nomorBaru(): string
    {
        $prefix = 'DP-' . date('Y') . '-';
        $last = static::where('no_anggota', 'like', $prefix . '%')->orderByDesc('no_anggota')->value('no_anggota');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /** Menjadikan akun (pengunjung) sebagai anggota: buat data anggota + nomor + masa berlaku. */
    public static function daftarkan(User $user, ?User $oleh = null): self
    {
        $a = static::create([
            'user_id' => $user->id, 'no_anggota' => static::nomorBaru(), 'status_keanggotaan' => 'aktif',
            'tgl_daftar' => Carbon::today(), 'berlaku_sampai' => Carbon::today()->addYears(2), 'didaftarkan_oleh' => $oleh?->id,
        ]);
        $user->update(['role' => 'anggota', 'pengajuan_at' => null]);
        return $a;
    }
}
