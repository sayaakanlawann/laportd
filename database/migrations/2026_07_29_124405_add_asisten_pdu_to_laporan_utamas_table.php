<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            // Menambahkan kolom asisten_pdu setelah pdu_nama, boleh kosong (nullable)
            $table->string('asisten_pdu')->nullable()->after('pdu_nama');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            $table->dropColumn('asisten_pdu');
        });
    }
};