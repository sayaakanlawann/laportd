<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BouncerAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Jika yang masuk BUKAN Admin dan BUKAN Developer (Noa), seret dia ke /td!
        if ($user && $user->role !== 'admin' && $user->email !== 'noa@dev.id') {
            return redirect('/td');
        }

        // Jika Admin/Noa, silakan lewat
        return $next($request);
    }
}