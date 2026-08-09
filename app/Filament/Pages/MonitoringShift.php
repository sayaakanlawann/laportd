<?php

namespace App\Filament\Pages;

use App\Models\LaporanUtama;
use App\Models\User; // Tambahkan ini untuk mengambil daftar TD
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action; // Tambahkan ini untuk tombol teguran
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class MonitoringShift extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Monitoring Shift';
    protected static ?string $title = 'Pemantauan Log Shift TD';
    protected static string | \UnitEnum | null $navigationGroup = 'Manajemen';
    protected static ?int $navigationSort = 1; 
    protected string $view = 'filament.pages.monitoring-shift';

    public static function canAccess(): bool
    {
        // Pastikan ini sesuai dengan sistem role Abang
        return in_array(auth()->user()->role, ['admin', 'dev']); 
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LaporanUtama::query()
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('MAX(id)')
                              ->from('laporan_utamas')
                              ->groupBy('tanggal_tugas');
                    })
                    ->orderBy('tanggal_tugas', 'desc')
            )
            ->columns([
                TextColumn::make('tanggal_tugas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // --- LOGIKA BARU SHIFT PAGI ---
                TextColumn::make('shift_pagi')
                    ->label('Shift Pagi')
                    ->getStateUsing(function ($record) {
                        $cek = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', 'pagi')
                            ->whereIn('status', ['final', 'alpha']) // Cari yang final atau alpha
                            ->first();
                        
                        if ($cek) {
                            return $cek->status === 'final' 
                                ? $cek->nama_petugas 
                                : 'Belum Input: ' . $cek->nama_petugas;
                        }
                        return 'Kosong';
                    })
                    ->badge()
                    ->color(fn (string $state): string => ($state === 'Kosong' || str_starts_with($state, 'Belum Input:')) ? 'danger' : 'success')
                    ->icon(fn (string $state): string => ($state === 'Kosong' || str_starts_with($state, 'Belum Input:')) ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),

                // --- LOGIKA BARU SHIFT SORE ---
                TextColumn::make('shift_sore')
                    ->label('Shift Sore')
                    ->getStateUsing(function ($record) {
                        $cek = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', 'sore')
                            ->whereIn('status', ['final', 'alpha'])
                            ->first();
                        
                        if ($cek) {
                            return $cek->status === 'final' 
                                ? $cek->nama_petugas 
                                : 'Belum Input: ' . $cek->nama_petugas;
                        }
                        return 'Kosong';
                    })
                    ->badge()
                    ->color(fn (string $state): string => ($state === 'Kosong' || str_starts_with($state, 'Belum Input:')) ? 'danger' : 'success')
                    ->icon(fn (string $state): string => ($state === 'Kosong' || str_starts_with($state, 'Belum Input:')) ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),
            ])
            // --- TOMBOL AKSI TANDAI ALPHA ---
            ->actions([
                Action::make('tandai_alpha')
                    ->label('Tandai TD Alpha')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->button()
                    ->form([
                        Select::make('shift')
                            ->label('Pilih Shift yang Kosong')
                            ->options([
                                'pagi' => 'Shift Pagi',
                                'sore' => 'Shift Sore',
                            ])
                            ->required(),

                        Select::make('nama_petugas')
                            ->label('Nama TD yang Bertugas')
                            // Mengambil data nama dari tabel users (Sesuaikan jika nama Model User Abang berbeda)
                            ->options(User::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        // 1. Cek apakah shift ini sebenarnya sudah diisi (mencegah Admin salah tandai)
                        $sudahAda = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', $data['shift'])
                            ->where('status', 'final')
                            ->exists();

                        if ($sudahAda) {
                            Notification::make()
                                ->title('Gagal: Shift ini sudah diisi laporan final.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // 2. Cek apakah sudah ditandai sebelumnya, jika ya, cukup update namanya
                        $teguranLama = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', $data['shift'])
                            ->where('status', 'alpha')
                            ->first();

                        if ($teguranLama) {
                            $teguranLama->update(['nama_petugas' => $data['nama_petugas']]);
                        } else {
                            // 3. Buat data laporan "bayangan" dengan status 'alpha'
                            LaporanUtama::create([
                                'status'          => 'alpha',
                                'tanggal_tugas'   => $record->tanggal_tugas,
                                'shift'           => $data['shift'],
                                'nama_petugas'    => $data['nama_petugas'],
                                
                                // Isi default bawaan agar database tidak protes
                                'pdu_nama'        => '-',
                                'tx_petugas_nama' => '-',
                                'pra_kendala'     => 0,
                                'kru_lengkap'     => 0,
                                'kesimpulan'      => '-',
                            ]);
                        }

                        Notification::make()
                            ->title('Berhasil menandai TD bersangkutan!')
                            ->success()
                            ->send();
                    })
            ]);
    }
}