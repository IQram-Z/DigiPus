<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPerpustakaan extends Model
{
    protected $table = 'pengaturan_perpustakaan';
    protected $guarded = [];
    protected $casts = ['blokir_pinjam_jika_denda' => 'boolean'];

    /** Satu baris pengaturan (dibuat otomatis dengan nilai bawaan bila belum ada). */
    public static function cfg(): self
    {
        return static::first() ?? static::create([])->fresh();
    }
}
