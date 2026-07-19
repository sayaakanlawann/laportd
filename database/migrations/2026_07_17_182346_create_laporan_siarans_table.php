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
    Schema::create('laporan_siarans', function (Blueprint $table) {
        $table->id();
        
        // Tali pengikat ke tabel Induk. 'cascadeOnDelete' memastikan jika laporan induk dihapus, anak-anaknya otomatis ikut musnah.
        $table->foreignId('laporan_utama_id')->constrained('laporan_utamas')->cascadeOnDelete();
        
        // Kolom untuk form repeater
        $table->time('jam_tayang');
        $table->time('jam_selesai'); // <-- Tambahan Baru
        $table->string('nama_program');
        $table->string('jenis_acara'); // <-- Tambahan Baru
        $table->string('status_siaran'); // Contoh: "Aman", "Kendala Audio", "Kendala Video"
        $table->text('catatan_kendala')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_siarans');
    }
};
