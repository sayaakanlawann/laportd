<?php

namespace App\Filament\Pages;

use App\Models\LaporanUtama; // Pastikan Model-nya sesuai
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;

class MonitoringShift extends Page implements HasTable
{
    use InteractsWithTable;

    // --- PENGATURAN SIDEBAR ---
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Monitoring Shift';
    protected static ?string $title = 'Monitoring Log Shift TD';
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen'; // Opsional, sesuaikan grup
    protected static ?int $navigationSort = 1; 
    protected string $view = 'filament.pages.monitoring-shift';

    // --- BATASI AKSES (Hanya Admin & Dev) ---
    public static function canAccess(): bool
    {
        // Sesuaikan dengan sistem Role Abang. 
        // Contoh jika pakai kolom 'role' di tabel users:
        return in_array(auth()->user()->role, ['admin', 'dev']); 
        
        // Atau jika pakai Spatie Permission:
        // return auth()->user()->hasRole(['admin', 'dev']);
    }

    // --- LOGIKA TABEL PEMANTAUAN ---
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil daftar tanggal yang unik (tidak dobel) dari database
                LaporanUtama::query()
                    ->whereIn('id', function ($query) {
                        // Mencari satu ID perwakilan untuk setiap tanggal
                        $query->selectRaw('MAX(id)')
                              ->from('laporan_utamas') // Pastikan nama tabel ini sesuai dengan database Abang
                              ->groupBy('tanggal_tugas');
                    })
                    ->orderBy('tanggal_tugas', 'desc')
            )
            ->columns([
                TextColumn::make('tanggal_tugas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // Kolom Cek Shift Pagi
                TextColumn::make('shift_pagi')
                    ->label('Shift Pagi')
                    ->getStateUsing(function ($record) {
                        // Cari data laporan di tanggal ini yang shift-nya pagi
                        $cek = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', 'pagi')
                            ->where('status', 'final') // Hanya hitung yang sudah final (bukan draft)
                            ->first();
                        
                        // Jika ada, tampilkan namanya. Jika tidak, tulis 'Belum Input'
                        return $cek ? $cek->nama_petugas : 'Belum Input';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Belum Input' ? 'danger' : 'success')
                    ->icon(fn (string $state): string => $state === 'Belum Input' ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),

                // Kolom Cek Shift Sore
                TextColumn::make('shift_sore')
                    ->label('Shift Sore')
                    ->getStateUsing(function ($record) {
                        // Cari data laporan di tanggal ini yang shift-nya sore
                        $cek = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', 'sore')
                            ->where('status', 'final') // Hanya hitung yang sudah final
                            ->first();
                        
                        return $cek ? $cek->nama_petugas : 'Belum Input';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Belum Input' ? 'danger' : 'success')
                    ->icon(fn (string $state): string => $state === 'Belum Input' ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),
            ]);
    }
}