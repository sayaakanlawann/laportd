<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            $table->json('evidence_sebelum_siaran')->nullable();
            $table->json('ev_alat_studio')->nullable();
            $table->json('ev_jaringan')->nullable();
            $table->json('ev_jalur_av')->nullable();
            $table->json('pra_ev_kendala')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_utamas', function (Blueprint $table) {
            $table->dropColumn([
                'evidence_sebelum_siaran',
                'ev_alat_studio',
                'ev_jaringan',
                'ev_jalur_av',
                'pra_ev_kendala'
            ]);
        });
    }
};