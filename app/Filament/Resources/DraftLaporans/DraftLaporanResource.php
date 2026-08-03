<?php

namespace App\Filament\Resources\DraftLaporans;

use App\Models\LaporanUtama;
use App\Filament\Resources\DraftLaporans\Pages;
use App\Filament\Resources\LaporanUtamas\LaporanUtamaResource; // Memanggil resource utama
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
class DraftLaporanResource extends Resource
{
    protected static ?string $model = LaporanUtama::class;

    // Ikon kertas untuk melambangkan draft
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Harian';
    protected static ?string $navigationLabel = 'Draft Laporan';

    protected static ?string $slug = 'draft-laporan';

    // Mengatur posisi menu agar berada di bawah Riwayat Laporan (Ubah angkanya jika perlu)
    protected static ?int $navigationSort = 2; 

    // Jika Riwayat Laporan Abang punya Grup (misal: 'Menu Laporan'), hilangkan tanda // di bawah ini:
    // protected static ?string $navigationGroup = 'Menu Laporan';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                // 1. WAJIB: Hanya tampilkan data yang berstatus draft
                $query->where('status', 'draft');

                // 2. ATURAN AKSES: Jika Admin/Dev, tampilkan semua. Jika TD, hanya miliknya.
                if ($user->role !== 'admin' && $user->email !== 'noa@dev.id') {
                    $query->where('nama_petugas', $user->name);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('nama_petugas')
                    ->label('Nama Petugas')
                    ->searchable(),
                TextColumn::make('tanggal_tugas')
                    ->label('Tanggal')
                    ->date('d M Y'),
                TextColumn::make('shift')
                    ->label('Shift')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Disimpan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->actions([
                // Aksi ajaib: Lempar user kembali ke halaman Edit utama!
               Action::make('lanjutkan')
                    ->label('Lanjutkan Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (LaporanUtama $record): string => LaporanUtamaResource::getUrl('edit', ['record' => $record->id])),
                
                // Fitur hapus jika draft dirasa sudah tidak penting
               DeleteAction::make()
                    ->label('Hapus Draft'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
            
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDraftLaporans::route('/'),
        ];
    }

    // Matikan tombol "Create" di halaman ini, karena membuat laporan HANYA dari menu utama
    public static function canCreate(): bool
    {
        return false;
    }
}