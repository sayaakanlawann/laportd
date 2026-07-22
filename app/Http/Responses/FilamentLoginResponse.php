<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponse implements LoginResponse
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse | \Livewire\Features\SupportRedirects\Redirector
    {
        $user = auth()->user();
        
        if ($user->role === 'admin' || $user->email === 'noa@dev.id') {
            return redirect()->to('/admin'); // <-- Kembali ke admin biasa
        }
        
        return redirect()->to('/td'); // <-- Kembali ke td biasa
    }
}