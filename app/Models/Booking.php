<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';
    protected $guarded = [];

    protected $casts = ['tgl_booking' => 'datetime'];

    public function anggota() { return $this->belongsTo(Anggota::class); }
    public function buku() { return $this->belongsTo(Buku::class); }
    public function eksemplar() { return $this->belongsTo(EksemplarBuku::class, 'eksemplar_id'); }
}
