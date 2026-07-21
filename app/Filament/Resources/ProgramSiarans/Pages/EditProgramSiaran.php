<?php

namespace App\Filament\Resources\ProgramSiarans\Pages;

use App\Filament\Resources\ProgramSiarans\ProgramSiaranResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramSiaran extends EditRecord
{
    protected static string $resource = ProgramSiaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
