<?php

namespace App\Filament\Resources\DraftLaporans\Pages;

use App\Filament\Resources\DraftLaporans\DraftLaporanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDraftLaporan extends EditRecord
{
    protected static string $resource = DraftLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
