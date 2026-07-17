<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanUtama extends Model
{
    protected $fillable = [
        'tanggal_tugas',
        'nama_petugas',
        'pdu_nama',
        'tx_petugas_nama',
        'pra_kendala',
        'pra_ket_kendala',
        'kru_lengkap',
        'kesimpulan',
        'evidence', // Tambahkan field ini
    ];

    // Beri tahu Laravel untuk otomatis mengubah JSON menjadi Array saat dibaca
    protected $casts = [
        'evidence' => 'array', 
    ];
}