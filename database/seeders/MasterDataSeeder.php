<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Bersihkan data petugas & program lama agar tidak dobel
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('petugas')->truncate();
        // DB::table('program_siarans')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. DATA KRU ASLI TVRI KALSEL
        $petugas = [
            // --- TECHNICAL DIRECTOR ---
            ['nama' => 'Ahmad Fauziansyah', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Embonk Yani', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Aditya W', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Arie Fajar P', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ary Priyanto', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Bagus N', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Cahyadi S', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Dian M', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Fitro Borney', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ihda M', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Jayadi S', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lilik S', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Akbar K', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Nazib F', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Rahman Sidiq', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Reditya S', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Supriyanto', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Surya R', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Yulius N', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Yusuf Haikal', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ucup S', 'jabatan_utama' => 'Technical Director', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],

            // --- PDU ---
            ['nama' => 'Aisiyani', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Arie Vidya', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Edward', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Kafsah Hanafiah', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => "M. Zar'an", 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Nurul Nada', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Putri Wening', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Susiana Dika', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Wirianto Hadi', 'jabatan_utama' => 'PDU', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],

            // --- TRANSMISI ---
            ['nama' => 'Ainina', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Anugerah', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Alya Soraya', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Arif Junaidi', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Chiesa Gymnastiar', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Dodik Pramono', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Doni Martien', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Hasbullah', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ika Septia', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Maryanto', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Mella M', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'M. Aini', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'M. Kholil', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => "Rahmad Syafi'ie", 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Raihan Nafis', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Rizqi Akbar', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ronny AS', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ruslim J', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Ryan Nur Hidayat', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Savira Aulia', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Sunaryo', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Syifa Davina', 'jabatan_utama' => 'Transmisi', 'is_aktif' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('petugas')->insert($petugas);

        

        
    }
}