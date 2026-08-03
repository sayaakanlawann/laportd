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
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url', // 3. JANGAN LUPA TAMBAHKAN INI AGAR BISA DISIMPAN
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getFilamentAvatarUrl(): ?string
    {
        // return null;
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // 1. PINTU LOBI UTAMA (/admin): Biarkan SEMUA USER lolos di pintu depan ini
        // (Nanti TD yang nyasar akan ditangkap oleh Middleware Satpam di Langkah 3)
        if ($panel->getId() === 'admin') {
            return true; 
        }

        // 2. PINTU /td: Tetap kunci HANYA untuk TD biasa
        if ($panel->getId() === 'td') {
            $isAdminOrDev = $this->role === 'admin' || $this->email === 'noa@dev.id';
            return !$isAdminOrDev; 
        }

        return false;
    }
}