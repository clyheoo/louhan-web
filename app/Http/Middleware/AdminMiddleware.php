<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user bukan admin, tendang ke halaman login dengan pesan error
        if (!$request->user() || $request->user()->role !== 'admin') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akses ditolak. Hanya Admin yang boleh masuk.']);
        }

        return $next($request);
    }
}