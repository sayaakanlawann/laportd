<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // --- HARD REDIRECT: PAKSA ARAHNYA, ABAIKAN URL SEBELUMNYA ---
            if ($user->role === 'admin' || $user->email === 'noa@dev.id') {
                return redirect('/admin'); // Hapus ->intended()
            }
            
            return redirect('/td'); // Hapus ->intended()
        }

        return back()->withErrors(['email' => 'Email atau password tidak ditemukan.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}