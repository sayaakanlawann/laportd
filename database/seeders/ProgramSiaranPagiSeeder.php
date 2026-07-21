<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramSiaran; // Sesuaikan dengan nama Model Abang jika berbeda

class ProgramSiaranPagiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programPagi = [
            '09:00|09:59' => [
                'Bakunjang', 'Cerdas Ceria', 'Gaya Remaja', 'Geliat Tanah Borneo',
                'Habar Banua', 'Hidup Sehat', 'Inspirasi Indonesia', 'Kalimantan Selatan Hari ini',
                'Kindai Limpuar', 'Ngopi', 'Siroh Protestan', 'Siroh Katolik',
                'Siroh Hindu', 'Siroh Buddha', 'Sekolah Ku Keren', 'Warung Bubuhan', 'Other'
            ],
            '10:00|10:59' => [
                'Hari Yang Berkah', 'Info Terkini', 'Ini Borneo', 'Inspirasi Indonesia',
                'Lensa Olahraga', 'Pesona Indonesia', 'Potensi Banua', 'Sekolah Ku Keren',
                'Zona Tani', 'Other'
            ],
            '11:00|11:59' => [
                'Anak Indonesia', 'Bertani Itu Keren', 'Cahaya Qolbu', 'Ini Borneo',
                'Inspirasi Indonesia', 'Kindai Limpuar', 'Kuliner Indonesia', 'Lensa Olahraga',
                'Pesona Indonesia', 'Saba Desa', 'Tekno Tani', 'Other'
            ],
            '12:00|13:59' => [
                'Klik Indonesia Siang', 'Hari Yang Berkah', "Ramadan Lil Qur'an", 'Other'
            ],
            '14:00|14:59' => [
                'Klik Indonesia Siang', 'Hidup Sehat', 'Music On Studio', 'Program Kerjasama',
                "Ramadan Lil Qur'an", 'Sapa Pemirsa', 'Yuk Mengaji', 'Other'
            ]
        ];

        // Looping untuk memasukkan data ke tabel
        foreach ($programPagi as $jamTayang => $programs) {
            foreach ($programs as $namaProgram) {
                // updateOrCreate sangat aman: tidak akan duplikat kalau dijalankan berulang kali
                ProgramSiaran::updateOrCreate(
                    [
                        'jam_tayang_default' => $jamTayang,
                        'nama_program'       => $namaProgram
                    ],
                    [
                        'is_aktif'           => true
                    ]
                );
            }
        }
        
        $this->command->info('HORE! Daftar acara Shift Pagi berhasil disuntikkan dengan sukses! ☀️');
    }
}