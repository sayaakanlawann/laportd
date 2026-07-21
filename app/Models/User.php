<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser; // <-- Tambahkan ini
use Filament\Panel; // <-- Tambahkan ini
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Tentukan siapa Admin/Dev
        $isAdminOrDev = $this->role === 'admin' || $this->email === 'noa@dev.id';

        // 1. Pintu /admin: HANYA untuk Admin dan Dev (Noa)
        if ($panel->getId() === 'admin') {
            return $isAdminOrDev;
        }

        // 2. Pintu /td: HANYA untuk user TD biasa
        if ($panel->getId() === 'td') {
            return !$isAdminOrDev; 
        }

        return false;
    }
}