<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /** Pemakaian: ->middleware('role:petugas,administrator') */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Kamu tidak punya akses untuk fitur ini.'], 403);
            }
            return redirect()->route($user ? $user->homeRoute() : 'login')->with('toast', 'Kamu tidak punya akses ke halaman itu.');
        }

        return $next($request);
    }
}
