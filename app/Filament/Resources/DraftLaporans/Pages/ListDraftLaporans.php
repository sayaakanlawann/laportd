<?php

namespace App\Filament\Resources\DraftLaporans\Pages;

use App\Filament\Resources\DraftLaporans\DraftLaporanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDraftLaporans extends ListRecords
{
    protected static string $resource = DraftLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
