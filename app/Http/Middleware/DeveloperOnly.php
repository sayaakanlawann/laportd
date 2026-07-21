<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeveloperOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login DAN emailnya adalah email developer
        if (Auth::check() && Auth::user()->email === 'noa@dev.id') {
            return $next($request);
        }

        // Jika bukan developer, tendang dengan pesan Error 403 (Forbidden)
        abort(403, 'Akses Ditolak. Area ini diawasi oleh Anbu! ⚡');
    }
}