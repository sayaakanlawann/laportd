<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();
        
        if ($user->role === 'admin' || $user->email === 'noa@dev.id') {
            return redirect()->to('/admin');
        }
        
        return redirect()->to('/td'); // Tendang TD nyasar ke sini!
    }
}