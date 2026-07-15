<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NamaAcaraSeeder extends Seeder
{
    public function run(): void
    {
        $acara = [
            // Slot Jam 15.00
            ['nama_acara' => 'Klik Indonesia Sore', 'jam_slot' => '15'],
            ['nama_acara' => 'Pesona Indonesia', 'jam_slot' => '15'],
            ['nama_acara' => 'Info BMKG Kalsel', 'jam_slot' => '15'],
            
            // Slot Jam 16.00
            ['nama_acara' => 'Berita Daerah Kalsel', 'jam_slot' => '16'],
            ['nama_acara' => 'Dialog Interaktif', 'jam_slot' => '16'],
            ['nama_acara' => 'Lagu Daerah', 'jam_slot' => '16'],
            
            // Slot Jam 17.00
            ['nama_acara' => 'Anak Nusantara', 'jam_slot' => '17'],
            ['nama_acara' => 'Kajian Islam', 'jam_slot' => '17'],
            ['nama_acara' => 'Lintas Benua', 'jam_slot' => '17'],
            
            // Slot Jam 18.00
            ['nama_acara' => 'Klik Indonesia Malam', 'jam_slot' => '18'],
            ['nama_acara' => 'Mimbar Agama', 'jam_slot' => '18'],
            ['nama_acara' => 'Jejak Langkah', 'jam_slot' => '18'],
        ];

        // Menyapu bersih isi tabel sebelum diisi, agar tidak ada data ganda kalau dijalankan 2 kali
        DB::table('nama_acara_master')->truncate(); 
        
        // Memasukkan data ke database
        DB::table('nama_acara_master')->insert($acara);
    }
}