<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Session;

class CreateLaporanUtama extends CreateRecord
{
    protected static string $resource = LaporanUtamaResource::class;

    #[Session]
    public ?array $data = [];

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $shift = request()->query('shift');

        if (!empty($this->data)) {
            if ($shift) {
                $this->data['shift'] = $shift;
            }
            $this->form->fill($this->data);
        } else {
            $this->form->fill([
                'shift' => $shift,
            ]);
        }

        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['shift'] = request()->query('shift') ?? ($data['shift'] ?? 'pagi');
        
        // 1. Paksa parsing format Y-m-d agar akurat
        $tanggalRaw = $data['tanggal_tugas'] ?? date('Y-m-d');
        $tanggal = \Carbon\Carbon::parse($tanggalRaw)->format('Y-m-d');
        
        // 2. Pakai absolute path (\App\Models\) dan whereDate
        $existingReport = \App\Models\LaporanUtama::whereDate('tanggal_tugas', $tanggal)
            ->where('shift', $data['shift'])
            ->first();

        if ($existingReport) {
            if ($existingReport->status === 'final') {
                \Filament\Notifications\Notification::make()
                    ->danger()
                    ->title('Gagal Menyimpan')
                    ->body('Laporan untuk Tanggal dan Shift ini sudah ada!')
                    ->send();

                $this->halt(); 
            } elseif ($existingReport->status === 'alpha') {
                // HAPUS PERMANEN TANPA AMPUN
                $existingReport->forceDelete();
            }
        }

        // 3. SUNTIK PAKSA DATA 
        $data['status'] = 'final'; 
        $data['nama_petugas'] = auth()->user()->name; 

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->reset('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}