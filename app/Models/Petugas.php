<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    use HasFactory;

    // --- TAMBAHKAN BARIS INI ---
    protected $fillable = [
        'nama', 
        'jabatan', 
        'jabatan_utama', 
        'is_aktif'
    ];
}