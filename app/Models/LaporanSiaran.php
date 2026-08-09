<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanSiaran extends Model
{
    // 1. Cukup gunakan ini untuk mengizinkan semua inputan masuk
    protected $guarded = []; 

    // 2. Aksesori tambahan untuk input form
    protected $appends = ['nama_program_custom'];

    public function getNamaProgramCustomAttribute()
    {
        return $this->attributes['nama_program_custom'] ?? null;
    }

    public function setNamaProgramCustomAttribute($value)
    {
        $this->attributes['nama_program_custom'] = $value;
    }

    // ❌ HAPUS BLOK $fillable INI SEPENUHNYA! ❌
    // protected $fillable = [ ... ];

    // Fungsi Relasi: Anak ini milik siapa?
    public function laporanUtama()
    {
        return $this->belongsTo(LaporanUtama::class, 'laporan_utama_id');
    }
}