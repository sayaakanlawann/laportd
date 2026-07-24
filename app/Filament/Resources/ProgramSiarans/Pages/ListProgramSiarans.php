<?php

namespace App\Filament\Resources\ProgramSiarans\Pages;

use App\Filament\Resources\ProgramSiarans\ProgramSiaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramSiarans extends ListRecords
{
    protected static string $resource = ProgramSiaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('+ Tambah Program Baru'), 
        ];
    }
}
