<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $guarded = [];

    public $timestamps = false;
    protected $casts = ['waktu' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
