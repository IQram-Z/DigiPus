<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'status_akun', 'provider', 'provider_id', 'foto',
        'pengajuan_at', 'email_verified_at',
    ];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'pengajuan_at' => 'datetime', 'password' => 'hashed'];
    }

    public function anggota() { return $this->hasOne(Anggota::class); }

    /** Username unik dari teks dasar (mis. bagian depan email). */
    public static function usernameUnik(string $base): string
    {
        $b = Str::slug($base, '') ?: 'user';
        $u = $b;
        for ($i = 1; static::where('username', $u)->exists(); $i++) $u = $b . $i;
        return $u;
    }

    public function isAdmin(): bool { return $this->role === 'administrator'; }
    public function isPetugas(): bool { return $this->role === 'petugas'; }
    public function isStaff(): bool { return in_array($this->role, ['petugas', 'administrator'], true); }
    public function isAnggota(): bool { return $this->role === 'anggota' && $this->anggota !== null; }

    public function roleLabel(): string
    {
        return ['pengunjung' => 'Pengunjung', 'anggota' => 'Anggota', 'petugas' => 'Petugas', 'administrator' => 'Administrator'][$this->role] ?? $this->role;
    }

    /** Nama route halaman utama sesuai peran. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'administrator' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            default => 'dashboard',
        };
    }

    public function fotoUrl(): string
    {
        return $this->foto ?: asset('covers/avatar.jpg');
    }
}
