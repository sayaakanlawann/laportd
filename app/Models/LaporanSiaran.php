<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanSiaran extends Model
{
    protected $fillable = [
        'laporan_utama_id',
        'jam_tayang',
        'jam_selesai',   
        'nama_program',
        'jenis_acara',   
        'status_siaran',
        'catatan_kendala'
    ];

    // Fungsi Relasi: Anak ini milik siapa?
    public function laporanUtama()
    {
        return $this->belongsTo(LaporanUtama::class);
    }
}