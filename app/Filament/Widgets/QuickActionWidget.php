<?php

namespace App\Filament\Widgets;

use App\Models\LaporanUtama; // <-- Wajib ditambahkan
use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource; // <-- Wajib ditambahkan
use Carbon\Carbon; // <-- Wajib ditambahkan
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
        // Nama Action disamakan persis dengan nama function tanpa kata 'Action'
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
                ->action(function (array $data, Action $action) {
                    // 1. Buat data "draft"
                    $draft = LaporanUtama::create([
                        'status'          => 'draft',
                        'nama_petugas'    => auth()->user()->name,
                        'tanggal_tugas'   => Carbon::today(),
                        'shift'           => $data['shift'],
                        'pdu_nama'        => '-',
                        'tx_petugas_nama' => '-',
                        'pra_kendala'     => 0,
                        'kru_lengkap'     => 0,
                        'kesimpulan'      => '-',
                    ]);

                    // 2. Redirect paksa memanggil langsung LaporanUtamaResource
                    $action->redirect(LaporanUtamaResource::getUrl('edit', [
                        'record' => $draft->id
                    ]));
                });
    }
}