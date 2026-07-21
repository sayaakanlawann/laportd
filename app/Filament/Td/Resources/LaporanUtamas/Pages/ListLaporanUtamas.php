<?php

namespace App\Filament\Td\Resources\LaporanUtamas\Pages;

use App\Filament\Td\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaporanUtamas extends ListRecords
{
    protected static string $resource = LaporanUtamaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
