<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class QuickActionWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?int $sort = -2;
    protected string $view = 'filament.widgets.quick-action-widget';

    // Nama function: createLaporanAction
    public function createLaporanAction(): Action
    {
        // MAKA NAMA DI SINI WAJIB: 'createLaporan' (Harus persis sama)
        return Action::make('createLaporan') 
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
                return redirect(\App\Filament\Resources\LaporanUtamas\LaporanUtamaResource::getUrl('create', [
                    'shift' => $data['shift']
                ]));
            });
    }
}