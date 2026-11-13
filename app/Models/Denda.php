<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    protected $table = 'denda';
    protected $guarded = [];

    protected $casts = ['tgl_bayar' => 'date'];

    public function peminjaman() { return $this->belongsTo(Peminjaman::class); }
}
