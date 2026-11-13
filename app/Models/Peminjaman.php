<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';
    protected $guarded = [];

    protected $casts = ['tgl_pinjam' => 'date', 'tgl_tenggat' => 'date'];

    public function anggota() { return $this->belongsTo(Anggota::class); }
    public function eksemplar() { return $this->belongsTo(EksemplarBuku::class, 'eksemplar_id'); }
    public function pengembalian() { return $this->hasOne(Pengembalian::class); }
    public function denda() { return $this->hasOne(Denda::class); }
}
