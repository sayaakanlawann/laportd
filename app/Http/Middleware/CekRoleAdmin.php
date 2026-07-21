<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRoleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login, tendang ke halaman login
        if (!auth()->check()) {
            return redirect('/login')->withErrors(['email' => 'Silakan login terlebih dahulu.']);
        }

        // Jika sudah login tapi BUKAN admin, tendang kembali ke Lobi
        if (auth()->user()->role !== 'admin') {
            return redirect('/')->with('error', 'Akses ditolak! Halaman ini khusus Administrator.');
        }

        // Jika dia Admin, silakan masuk
        return $next($request);
    }
}