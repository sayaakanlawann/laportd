<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Session;

class CreateLaporanUtama extends CreateRecord
{
    protected static string $resource = LaporanUtamaResource::class;

    // 1. Tambahkan Session untuk menyimpan draft memori ketikan
    #[Session]
    public ?array $data = [];

    // 2. Gabungkan logika "Menangkap Shift" dan "Membaca Memori" di dalam fillForm
    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        // Tangkap parameter shift dari URL
        $shift = request()->query('shift');

        if (!empty($this->data)) {
            // SKENARIO A: User habis refresh (Ada memori tersimpan)
            // Pastikan shift-nya tetap dipaksa sesuai dengan URL agar tidak meleset
            if ($shift) {
                $this->data['shift'] = $shift;
            }
            $this->form->fill($this->data);
        } else {
            // SKENARIO B: Pertama kali buka halaman (Memori kosong)
            // Isi form dengan shift dari URL saja
            $this->form->fill([
                'shift' => $shift,
            ]);
        }

        $this->callHook('afterFill');
    }

    // 3. Logika bawaan Abang tetap dipertahankan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['shift'] = request()->query('shift') ?? ($data['shift'] ?? 'pagi');
        return $data;
    }

    // 4. Wajib ditambahkan: Bersihkan brankas memori ketika Laporan BERHASIL disimpan
    protected function afterCreate(): void
    {
        $this->reset('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(), // Menampilkan tombol "Simpan" biasa
            $this->getCancelFormAction(), // Menampilkan tombol "Batal"
            // Kita sengaja tidak memanggil $this->getCreateAnotherFormAction() di sini
            // agar tombol "Create & Create Another" lenyap!
        ];
    }
}