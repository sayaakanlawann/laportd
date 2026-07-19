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
    Schema::create('program_siarans', function (Blueprint $table) {
        $table->id();
        $table->string('nama_program');
        // Jam tayang default (opsional), sangat membantu agar kru tidak perlu 
        // mengingat jam acara saat mengisi form.
        $table->string('jam_tayang_default')->nullable(); 
        $table->boolean('is_aktif')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_siarans');
    }
};
