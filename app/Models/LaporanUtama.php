<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanUtama extends Model
{
    protected $fillable = [
        'shift',
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
        'tx_petugas_nama' => 'array',
        'evidence_sebelum_siaran' => 'array',
        'ev_alat_studio' => 'array',
        'ev_jaringan' => 'array',
        'ev_jalur_av' => 'array',
        'pra_ev_kendala' => 'array', 
    ];
    
    public function siarans()
    {
        return $this->hasMany(LaporanSiaran::class);
    }
}