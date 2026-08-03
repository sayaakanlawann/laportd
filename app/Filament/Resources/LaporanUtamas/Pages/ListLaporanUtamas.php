<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use App\Models\LaporanUtama; // <-- Tambahkan ini untuk memanggil database
use Carbon\Carbon;           // <-- Tambahkan ini untuk tanggal hari ini
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;

class ListLaporanUtamas extends ListRecords
{
    protected static string $resource = LaporanUtamaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_with_shift')
                ->label('+ Buat Laporan Baru')
                ->color('primary')
                ->modalHeading('Pilih Shift Laporan')
                ->modalDescription('Silakan pilih shift tugas terlebih dahulu sebelum mengisi laporan.')
                ->modalSubmitActionLabel('Lanjutkan ke Form')
                ->form([
                    Select::make('shift')
                        ->label('Shift Tugas')
                        ->options([
                            'pagi' => 'Shift Pagi',
                            'sore' => 'Shift Sore',
                        ])
                        ->required()
                ])
                ->action(function (array $data) {
                    // 1. Buat data "draft" di database menggunakan shift yang dipilih dari modal
                    $draft = LaporanUtama::create([
                        'status'        => 'draft',
                        'nama_petugas'  => auth()->user()->name,
                        'tanggal_tugas' => Carbon::today(),
                        'shift'         => $data['shift'],
                        'pdu_nama'      => '-',
                        'tx_petugas_nama' => '-',
                        'pra_kendala' => 0,
                        'kru_lengkap' => 0,
                        'kesimpulan' => '-',

                    ]);

                    // 2. Langsung arahkan ke halaman Edit dengan ID draft yang baru dibuat
                    return redirect(static::getResource()::getUrl('edit', [
                        'record' => $draft->id
                    ]));
                }),
        ];
    }
}