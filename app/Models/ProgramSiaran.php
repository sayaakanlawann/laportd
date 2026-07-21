<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramSiaran extends Model
{
    use HasFactory;

    // --- TAMBAHKAN BARIS INI ---
    protected $fillable = [
        'jam_tayang_default', 
        'nama_program', 
        'is_aktif'
    ];
}