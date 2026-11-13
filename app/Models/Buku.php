<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    protected $table = 'buku';
    protected $guarded = [];

    protected $casts = ['rekomendasi' => 'boolean'];

    public function kategori() { return $this->belongsTo(Kategori::class); }
    public function eksemplar() { return $this->hasMany(EksemplarBuku::class); }

    public function coverUrl(): string { return asset($this->sampul ?: 'covers/placeholder.svg'); }
    public function tagList(): array { return array_values(array_filter(array_map('trim', explode(',', (string) $this->tags)))); }
}
