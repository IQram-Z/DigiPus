<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksemplarBuku extends Model
{
    protected $table = 'eksemplar_buku';
    protected $guarded = [];

    public function buku() { return $this->belongsTo(Buku::class); }
}
