<?php

namespace App\Filament\Pages;

use App\Models\LaporanUtama;
use App\Models\User; 
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action; // <-- Ini yang benar untuk aksi Tabel
use Filament\Forms\Components\DatePicker;
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

                TextColumn::make('shift_pagi')
                    ->label('Shift Pagi')
                    ->getStateUsing(function ($record) {
                        $cek = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', 'pagi')
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

            // 🔥 TOMBOL TEGURAN MANUAL DI POJOK KANAN ATAS TABEL (Untuk tanggal yang blank total) 🔥
            ->headerActions([
                Action::make('teguran_manual_header')
                    ->label('Buat Teguran Manual')
                    ->icon('heroicon-o-plus-circle')
                    ->color('danger')
                    ->form([
                        DatePicker::make('tanggal_tugas')
                            ->label('Tanggal Laporan (Yang Kosong)')
                            ->required()
                            ->maxDate(now())
                            ->default(now()),

                        Select::make('shift')
                            ->label('Pilih Shift')
                            ->options([
                                'pagi' => 'Shift Pagi',
                                'sore' => 'Shift Sore',
                            ])
                            ->required(),

                        Select::make('nama_petugas')
                            ->label('Nama TD yang Bolos')
                            ->options(User::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $sudahAda = LaporanUtama::where('tanggal_tugas', $data['tanggal_tugas'])
                            ->where('shift', $data['shift'])
                            ->first();

                        if ($sudahAda) {
                            if ($sudahAda->status === 'final') {
                                Notification::make()
                                    ->title('Gagal: Shift ini sudah diisi laporan final!')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                $sudahAda->update(['nama_petugas' => $data['nama_petugas']]);
                            }
                        } else {
                            LaporanUtama::create([
                                'status'          => 'alpha',
                                'tanggal_tugas'   => $data['tanggal_tugas'],
                                'shift'           => $data['shift'],
                                'nama_petugas'    => $data['nama_petugas'],
                                
                                'pdu_nama'        => '-',
                                'tx_petugas_nama' => '-',
                                'pra_kendala'     => 0,
                                'kru_lengkap'     => 0,
                                'kesimpulan'      => '-',
                            ]);
                        }

                        Notification::make()
                            ->title('Teguran berhasil dikirim & baris baru ditambahkan!')
                            ->success()
                            ->send();
                    })
            ])

            // --- TOMBOL AKSI DI DALAM BARIS TABEL (Untuk shift yang 1 isi, 1 kosong) ---
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
                            ->options(User::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
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

                        $teguranLama = LaporanUtama::where('tanggal_tugas', $record->tanggal_tugas)
                            ->where('shift', $data['shift'])
                            ->where('status', 'alpha')
                            ->first();

                        if ($teguranLama) {
                            $teguranLama->update(['nama_petugas' => $data['nama_petugas']]);
                        } else {
                            LaporanUtama::create([
                                'status'          => 'alpha',
                                'tanggal_tugas'   => $record->tanggal_tugas,
                                'shift'           => $data['shift'],
                                'nama_petugas'    => $data['nama_petugas'],
                                
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