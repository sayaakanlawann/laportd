<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
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
                    // Setelah user pilih shift dan klik submit, arahkan ke halaman create 
                    // dengan membawa parameter shift di URL (contoh: /laporan-utamas/create?shift=pagi)
                    return redirect()->route('filament.admin.resources.laporan-utamas.create', [
                        'shift' => $data['shift']
                    ]);
                }),
        ];
    }
}