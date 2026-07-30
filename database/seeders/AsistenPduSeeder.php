<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Petugas; // <-- Wajib panggil model Petugas

class AsistenPduSeeder extends Seeder
{
    public function run(): void
    {
        $asistenList = [
            'Rizka Annisa',
            'Tasya Nadita',
            'Dominicha',
            'Arini Putri',
            'Khaireza Fikri',
            'Anggun Riana',
            'Ahmadi Noor'
        ];

        foreach ($asistenList as $nama) {
            // updateOrCreate akan mengecek: jika nama sudah ada, jangan diduplikat.
            // Jika belum ada, maka buat baru dengan role asisten_pdu.
            Petugas::updateOrCreate(
                ['nama' => $nama], // <-- Asumsi nama kolomnya adalah 'nama'
                [
                    'jabatan_utama' => 'asisten_pdu', 
                ]
            );
        }
    }
}