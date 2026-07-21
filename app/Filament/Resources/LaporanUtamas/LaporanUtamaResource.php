<?php

namespace App\Filament\Resources\LaporanUtamas;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use App\Filament\Resources\LaporanUtamas\Pages\CreateLaporanUtama;
use App\Filament\Resources\LaporanUtamas\Pages\EditLaporanUtama;
use App\Filament\Resources\LaporanUtamas\Pages\ListLaporanUtamas;
use App\Filament\Resources\LaporanUtamas\Schemas\LaporanUtamaForm;
use App\Filament\Resources\LaporanUtamas\Tables\LaporanUtamasTable;
use App\Models\LaporanUtama;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class LaporanUtamaResource extends Resource
{
    protected static ?string $model = LaporanUtama::class;

    // --- TAMBAHAN BEDAH MIKRO: UI SIDEBAR ---
    protected static ?string $modelLabel = 'Riwayat Evidence';
    protected static ?string $pluralModelLabel = 'Riwayat Laporan TD';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Harian';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate'; // Ikon Dokumen
    protected static ?int $navigationSort = 1; // Posisi paling atas


    protected static ?string $recordTitleAttribute = 'sidebar';

    public static function form(Schema $schema): Schema
    {
        return LaporanUtamaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn ($record): string => "/laporan/{$record->id}/edit")
            ->columns([
                Tables\Columns\TextColumn::make('shift')
                    ->label('Shift')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pagi' => 'warning',
                        'sore' => 'indigo',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tanggal_tugas')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_petugas')
                    ->label('TD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pdu_nama')
                    ->label('PDU')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tx_petugas_nama')
                    ->label('TX')
                    ->toggleable(isToggledHiddenByDefault: true),
                // --- PERBAIKAN LOG SIARAN (MENAMPILKAN JAM, PROGRAM, STATUS & CATATAN) ---
                Tables\Columns\TextColumn::make('log_siaran_lengkap')
                    ->label('Log Siaran')
                    ->html() // Mengizinkan render HTML
                    ->getStateUsing(function ($record) {
                        if ($record->siarans->isEmpty()) return '-';
                        
                        $html = '<ul style="margin: 0; padding: 0; list-style-type: none; font-size: 0.85rem;">';
                        
                        foreach ($record->siarans as $siaran) {
                            $jamMulai = \Carbon\Carbon::parse($siaran->jam_tayang)->format('H:i');
                            $jamSelesai = \Carbon\Carbon::parse($siaran->jam_selesai)->format('H:i');
                            
                            // Warna teks status (Hijau jika Aman, Merah jika Kendala)
                            $colorStatus = $siaran->status_siaran == 'Aman' ? 'color: #10b981;' : 'color: #ef4444;';
                            
                            $html .= "<li style='margin-bottom: 8px; border-bottom: 1px solid #334155; padding-bottom: 4px;'>";
                            $html .= "<strong style='color: #38bdf8;'>{$jamMulai} - {$jamSelesai}</strong> | {$siaran->nama_program} ";
                            $html .= "<span style='{$colorStatus} font-weight: bold; font-size: 0.75rem;'>[{$siaran->status_siaran}]</span>";
                            
                            // Jika ada catatan kendala, tampilkan di bawahnya
                            if (!empty($siaran->catatan_kendala)) {
                                $html .= "<br><span style='color: #fbbf24; font-size: 0.75rem;'>⚠️ Catatan: {$siaran->catatan_kendala}</span>";
                            }
                            
                            $html .= "</li>";
                        }
                        
                        $html .= '</ul>';
                        return $html;
                    }),
                Tables\Columns\IconColumn::make('kru_lengkap')
                    ->label('Kru')
                    ->boolean(),
                Tables\Columns\IconColumn::make('pra_kendala')
                    ->label('Kendala')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('gray'),
            ])
            ->filters([
                //
            ])
            // --- HEADER ACTIONS: TOMBOL EXPORT EXCEL DI ATAS TABEL ---
            // --- HEADER ACTIONS: TOMBOL EXPORT EXCEL DI ATAS TABEL ---
            ->headerActions([
                Action::make('export_excel') // <-- BERSIH! (Tanpa Tables\)
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->modalHeading('📥 Export Rekap Excel')
                    ->modalDescription('Pilih mode export data laporan TD.')
                    ->modalSubmitActionLabel('Download Excel')
                    ->form([
                        Forms\Components\Toggle::make('export_all')
                            ->label('Export Seluruh Data (Pisahkan ke banyak Sheet)')
                            ->live()
                            ->default(false),
                        Forms\Components\TextInput::make('bulan')
                            ->label('Pilih Bulan Tertentu (Opsi 1)')
                            ->type('month')
                            ->required(fn (Get $get) => ! $get('export_all'))
                            ->disabled(fn (Get $get) => $get('export_all')),
                    ])
                    ->action(function (array $data) {
                        $query = $data['export_all'] ? 'export_all=1' : 'bulan=' . $data['bulan'];
                        return redirect('/export-excel?' . $query);
                    }),
            ])
            ->actions([
                // --- 1. TOMBOL LIHAT EVIDENCE (POP-UP) ---
                Action::make('lihat_evidence') // <-- BERSIH!
                    ->label('Evidence')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->modalHeading(fn ($record) => '🖼️ Bukti Evidence - TD: ' . $record->nama_petugas)
                    ->modalSubmitAction(false) 
                    ->modalCancelActionLabel('Tutup')
                    ->infolist([
                        \Filament\Infolists\Components\RepeatableEntry::make('evidence')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->weight('bold')
                                    ->color('info'),
                                \Filament\Infolists\Components\ImageEntry::make('link_drive')
                                    ->hiddenLabel()
                                    ->height(200)
                                    ->extraImgAttributes(['class' => 'rounded-xl shadow-sm object-cover w-full'])
                                    ->url(fn ($state) => $state)
                                    ->openUrlInNewTab(),
                            ])
                            ->grid(2)
                            
                    ]),

                // --- 2. TOMBOL UNDUH PDF ---
                Action::make('download_pdf') // <-- BERSIH!
                    ->label('PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (LaporanUtama $record) => "/evidence/{$record->id}/download")
                    ->openUrlInNewTab(),

                // --- 3. TOMBOL EDIT ---
                // --- 3. TOMBOL EDIT (DIARAHKAN KE FRONT-END BUATAN ABANG) ---
                //

                // --- 4. TOMBOL HAPUS ---
                DeleteAction::make(), // <-- BERSIH!
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaporanUtamas::route('/'),
            // 'create' => CreateLaporanUtama::route('/create'),
            // 'edit' => EditLaporanUtama::route('/{record}/edit'),
        ];
    }
}
