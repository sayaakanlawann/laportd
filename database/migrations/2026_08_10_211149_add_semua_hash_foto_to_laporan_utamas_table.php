<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            // Kolom text panjang untuk menampung gabungan banyak sidik jari (Hash)
            $table->text('semua_hash_foto')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            $table->dropColumn('semua_hash_foto');
        });
    }
};