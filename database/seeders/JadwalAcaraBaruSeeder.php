<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramSiaran;
use Illuminate\Support\Facades\DB;

class JadwalAcaraBaruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. BERSIHKAN DATA PROGRAM LAMA
        // Kita gunakan DB statement untuk mematikan foreign key checks sementara jika diperlukan
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProgramSiaran::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. DAFTAR JADWAL ACARA BARU (PAGI & SORE)
        $jadwalProgram = [
            // ==========================================
            // SHIFT PAGI
            // ==========================================
            '09:00|10.00' => ['Habar Banua', 'Kalimantan Selatan Hari ini', 'iYoga'],
            '09:00|09:30' => ['Cerdas Ceria'],
            '09:30|10.00' => ['Bakunjang'],
            
            '10:00|11.00' => ['Ini Borneo', 'Jejak Langkah'],
            '10:00|10:30' => ['Sekolah Ku Keren'],
            '10:30|11.00' => ['Lensa Olahraga', 'Oto Screen'],
            
            '11:00|12.00' => ['Ini Borneo', 'Binian'],
            '11:00|11:30' => ['Dapur Davina'],
            '11:30|12.00' => ['Inspirasi Indonesia - Nusantara / Borneo GTC'],

            // ==========================================
            // SHIFT SORE
            // ==========================================
            '15:00|16.00' => ['Perspektif', 'Hidup Sehat', 'Ngopi', 'Cahaya Qolbu', 'Binian'],
            '15:00|15:30' => ['Remaja Hebat', 'Sinema Banua'],
            '15:30|16.00' => ['Warung Bubuhan', 'Ayo Ke Museum'],
            
            '16:00|17.00' => [
                'Pengen Beken', 'Music Off The Record', 'Dangdut Keliling', 
                'Hari Yang Berkah', 'Siroh Protestan', 'Siroh Hindu', 
                'Siroh Katolik', 'Siroh Buddha', 'Siroh Konghuchu'
            ],
            '16:00|16:30' => ['Sekolah Ku Keren', 'Remaja Hebat', 'Inspirasi Tani / Dinamika', 'Kindai Limpuar / Dinamika', 'Cerdas Ceria'],
            '16:30|17.00' => ['Bakunjang', 'Lensa Olahraga', 'Oto Screen'],
            
            '17:00|18.00' => ['Kalimantan Selatan Hari Ini', 'Habar Banua'],
            
            '18:00|18:30' => ['Kajian Tauhid', 'Fiqih Wanita', 'Mutiara Hadis'],
            '18:30|19.00' => [
                'Inspirasi Indonesia Kalsel', 'Pesona Indonesia Kalsel', 
                'Jejak Islam', 'Anak Indonesia', 'Pesona Indonesia - Nusantara', 'PKS Antara'
            ],
        ];

        // 3. MASUKKAN DATA BARU KE DATABASE
        foreach ($jadwalProgram as $jamTayang => $programs) {
            foreach ($programs as $namaProgram) {
                ProgramSiaran::create([
                    'jam_tayang_default' => $jamTayang,
                    'nama_program'       => $namaProgram,
                    'is_aktif'           => true
                ]);
            }
            
            // Tambahkan pilihan "Other" otomatis di setiap slot jam
            ProgramSiaran::create([
                'jam_tayang_default' => $jamTayang,
                'nama_program'       => 'Other',
                'is_aktif'           => true
            ]);
        }
        
        $this->command->info('BERHASIL! Data program lama dibersihkan, dan jadwal baru sukses disuntikkan! 🚀');
    }
}