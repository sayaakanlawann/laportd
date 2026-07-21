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
    Schema::create('laporan_utamas', function (Blueprint $table) {
        $table->id(); 
        $table->date('tanggal_tugas'); 
        $table->string('shift');
        $table->string('nama_petugas'); 
        $table->string('pdu_nama'); 
        $table->string('tx_petugas_nama'); 
        
        $table->boolean('pra_kendala'); 
        $table->text('pra_ket_kendala')->nullable(); 
        $table->boolean('kru_lengkap'); 
        $table->text('kesimpulan'); 
        
        // INI DIA FIELD BARUNYA: Menyimpan semua evidence dalam 1 keranjang JSON
        $table->json('evidence')->nullable(); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_utamas');
    }
};
