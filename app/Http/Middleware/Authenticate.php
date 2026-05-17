<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // ★ TAMBAHKAN FALLBACK: Jika Auth::id() ada, anggap sudah login
            if (Auth::guard($guard)->check() || Auth::id() !== null) {
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
    }
}