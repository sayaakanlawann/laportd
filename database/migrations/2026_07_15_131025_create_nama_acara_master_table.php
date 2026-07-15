<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nama_acara_master', function (Blueprint $table) {
            $table->id();
            $table->string('nama_acara');
            $table->enum('jam_slot', ['15', '16', '17', '18']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nama_acara_master');
    }
};