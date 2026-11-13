<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $guarded = [];

    public function buku() { return $this->hasMany(Buku::class); }
    public function wakil() { return $this->belongsTo(Buku::class, 'buku_wakil_id'); }
}
