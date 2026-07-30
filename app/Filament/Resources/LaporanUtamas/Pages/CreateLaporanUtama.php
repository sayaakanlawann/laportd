<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanUtama extends CreateRecord
{
    protected static string $resource = LaporanUtamaResource::class;

    // 1. Tangkap shift dari URL saat halaman pertama kali dibuka
    public function mount(): void
    {
        parent::mount();

        // Simpan nilai shift dari query string (?shift=pagi / sore) ke state form
        $shift = request()->query('shift');
        if ($shift) {
            $this->form->fill([
                'shift' => $shift,
            ]);
        }
    }

    // 2. Pastikan data shift ikut masuk saat form disubmit ke database
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['shift'] = request()->query('shift') ?? ($data['shift'] ?? 'pagi');
        return $data;
    }
}