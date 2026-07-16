<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    // Daftar kolom yang kita izinkan untuk diisi data dari form
    protected $fillable = [
        'judul',
        'file_path',
        'file_id',
    ];
}