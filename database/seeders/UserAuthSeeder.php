<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;

class UserAuthSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@tvrikalsel.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('tvri'), // Password admin sementara
                'role' => 'admin'
            ]
        );

        // 2. Buat Akun TD (Ambil dari tabel Petugas)
        $tds = Petugas::where('jabatan_utama', 'Technical Director')->get();
        
        foreach ($tds as $td) {
            // Ambil kata pertama dari nama untuk dijadikan email
            $namaDepan = strtolower(explode(' ', trim($td->nama))[0]);
            
            User::updateOrCreate(
                ['email' => $namaDepan . '@tvrikalsel.id'],
                [
                    'name' => $td->nama,
                    'password' => Hash::make('tvri'), // Password seragam
                    'role' => 'td'
                ]
            );
        }

        $this->command->info('Akun Admin dan TD berhasil diciptakan!');
    }
}