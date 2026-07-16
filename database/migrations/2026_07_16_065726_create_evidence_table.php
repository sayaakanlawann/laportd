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
    Schema::create('evidence', function (Blueprint $table) {
        $table->id();
        $table->string('judul'); // Nama dokumen atau deskripsi singkat
        $table->string('file_path'); // Alamat lengkap file
        $table->string('file_id')->nullable(); // ID unik file dari Google Drive
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
