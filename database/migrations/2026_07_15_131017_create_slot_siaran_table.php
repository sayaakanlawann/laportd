<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_siaran', function (Blueprint $table) {
            $table->id();
            
            // Ini tali pengikat ke tabel laporan
            $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
            
            $table->enum('jam_mulai', ['15', '16', '17', '18']);
            $table->json('nama_acara');
            $table->enum('format_acara', ['Live Studio 1', 'Live Studio 2', 'Live Studio 3', 'Playback', 'Relay', 'Record']);
            
            $table->boolean('kendala')->default(false);
            $table->text('keterangan_kendala')->nullable();
            $table->string('evidence_kendala')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_siaran');
    }
};