<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('petugas', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        // Kita siapkan kolom jabatan_utama (misal: "TD", "Audio", "Kamera"), 
        // walau praktiknya kru TV sering dirotasi.
        $table->string('jabatan_utama')->nullable(); 
        // Kolom ini berguna jika suatu saat ada kru yang *resign*, 
        // namanya bisa disembunyikan tanpa menghapus data lama.
        $table->boolean('is_aktif')->default(true); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
