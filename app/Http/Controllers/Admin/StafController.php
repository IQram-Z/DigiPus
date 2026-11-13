<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Sirkulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** Pengelolaan akun & hak akses (SRS DGP-020): akun petugas/administrator dan reset kata sandi. */
class StafController extends Controller
{
    public function index()
    {
        return view('admin.staf', ['items' => User::whereIn('role', ['petugas', 'administrator'])->orderBy('role')->orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:60', 'username' => 'required|alpha_dash|max:30|unique:users,username',
            'email' => 'required|email|max:120|unique:users,email', 'password' => 'required|min:6', 'role' => 'required|in:petugas,administrator',
        ]);
        $d['email_verified_at'] = now();
        User::create($d);
        Sirkulasi::log(Auth::user(), "Tambah akun {$d['role']}: {$d['username']}");
        return back()->with('toast', "Akun {$d['role']} \"{$d['username']}\" dibuat.");
    }

    public function status(User $user)
    {
        if ($user->id === Auth::id()) return back()->with('toast', 'Kamu tidak bisa menonaktifkan akunmu sendiri.');
        $user->update(['status_akun' => $user->status_akun === 'aktif' ? 'nonaktif' : 'aktif']);
        Sirkulasi::log(Auth::user(), "Akun {$user->username}: {$user->status_akun}");
        return back()->with('toast', "Akun {$user->username} sekarang {$user->status_akun}.");
    }

    public function role(Request $r, User $user)
    {
        if ($user->id === Auth::id()) return back()->with('toast', 'Kamu tidak bisa mengubah peranmu sendiri.');
        abort_unless(in_array($user->role, ['petugas', 'administrator'], true), 404);
        $user->update(['role' => $r->validate(['role' => 'required|in:petugas,administrator'])['role']]);
        Sirkulasi::log(Auth::user(), "Ubah peran {$user->username}: {$user->role}");
        return back()->with('toast', "Peran {$user->username} menjadi {$user->role}.");
    }

    /** Reset kata sandi akun mana pun; kata sandi sementara ditampilkan sekali. */
    public function reset(User $user)
    {
        $pw = Str::lower(Str::random(8));
        $user->update(['password' => $pw]);
        Sirkulasi::log(Auth::user(), "Reset kata sandi: {$user->username}");
        return back()->with('toast', "Kata sandi sementara untuk {$user->username}: $pw (catat sekarang).");
    }
}
