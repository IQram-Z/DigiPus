<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};

class ProfileController extends Controller
{
    public function show()
    {
        $u = Auth::user();
        $layout = $u->isAdmin() ? 'layouts.admin' : ($u->isPetugas() ? 'layouts.staff' : 'layouts.app');
        return view('profil', ['user' => $u->load('anggota'), 'layout' => $layout]);
    }

    public function update(Request $r)
    {
        $u = Auth::user();
        $data = $r->validate([
            'name' => 'required|string|max:60',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.', 'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if (! empty($data['password'])) {
            // akun biasa wajib memasukkan kata sandi lama; akun Google/Apple boleh langsung membuat kata sandi
            if (! $u->provider && ! Hash::check((string) $r->input('current_password'), $u->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
            }
            $u->password = $data['password'];
        }
        $u->name = $data['name'];
        $u->save();

        return back()->with('toast', 'Profil diperbarui.');
    }
}
