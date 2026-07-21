<?php

namespace App\Filament\Td\Resources\LaporanUtamas\Pages;

use App\Filament\Td\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLaporanUtama extends EditRecord
{
    protected static string $resource = LaporanUtamaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
