<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_petugas');
            $table->string('nama_pdu');
            $table->string('nama_petugas_transmisi');
            
            // Kolom foto/evidence dibolehkan kosong (nullable)
            $table->string('evidence_hidupkan_peralatan')->nullable();
            $table->string('evidence_cek_jaringan')->nullable();
            $table->string('evidence_jalur_audio_video')->nullable();
            
            // Status kendala, defaultnya false (tidak ada kendala)
            $table->boolean('kendala_sebelum_siaran')->default(false);
            $table->text('keterangan_kendala_sebelum_siaran')->nullable();
            $table->string('evidence_kendala_sebelum_siaran')->nullable();
            
            $table->boolean('kru_lengkap')->default(true);
            $table->text('kesimpulan')->nullable();
            
            $table->timestamps(); // Otomatis membuat kolom created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};