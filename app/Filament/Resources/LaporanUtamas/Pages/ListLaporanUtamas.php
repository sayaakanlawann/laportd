<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaporanUtamas extends ListRecords
{
    protected static string $resource = LaporanUtamaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                // Membajak klik tombol agar pindah ke route /upload buatan Abang
            
                // (Opsional) Abang juga bisa merapikan namanya sekalian
                ->label('+ Buat Laporan Baru'), 
        ];
    }
}
