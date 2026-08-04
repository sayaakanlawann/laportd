<?php

namespace App\Filament\Resources\LaporanUtamas\Pages;

use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanUtama extends EditRecord
{
    protected static string $resource = LaporanUtamaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // PERBAIKAN DI SINI: Tambahkan Actions\ di depannya
            Actions\DeleteAction::make(),
        ];
    }

    // 1. Bersihkan tombol bawah, sisakan tombol Submit Final saja
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Submit Laporan (Final)')
                ->color('success'),

            $this->getCancelFormAction(),
        ];
    }

    // 2. Karena ini HANYA dieksekusi saat tombol Final diklik,
    // kita paksa statusnya berubah menjadi final di sini.
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = 'final';
        return $data;
    }

    // 3. Langsung kembali ke halaman tabel setelah klik Final
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}