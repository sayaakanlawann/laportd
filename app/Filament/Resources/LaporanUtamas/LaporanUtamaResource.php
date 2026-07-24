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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
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
                // --- 1. TOMBOL LIHAT EVIDENCE (POP-UP) ---
                // --- 1. TOMBOL LIHAT EVIDENCE (POP-UP) ---
                Action::make('lihat_evidence') 
                    ->label('Evidence')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->modalHeading(fn ($record) => '🖼️ Bukti Evidence - TD: ' . $record->nama_petugas)
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false) 
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function ($record): HtmlString {
                        $html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; width: 100%;">';
                        
                        // Helper khusus Kolom Baru (Filament) dengan label deskriptif per gambar
                        $renderFilamentImages = function($labelSection, $images) use (&$html) {
                            if (empty($images)) return;
                            
                            if (is_string($images)) {
                                $decoded = json_decode($images, true);
                                $images = is_array($decoded) ? $decoded : [$images];
                            }
                            
                            if (is_array($images)) {
                                $validItems = [];
                                foreach ($images as $img) {
                                    $path = '';
                                    $customCaption = '';
                                    
                                    if (is_array($img)) {
                                        $path = $img['path'] ?? reset($img);
                                        $customCaption = $img['keterangan'] ?? $img['caption'] ?? '';
                                    } else {
                                        $path = $img;
                                    }
                                    
                                    if (!empty($path) && is_string($path)) {
                                        $validItems[] = ['path' => $path, 'caption' => $customCaption];
                                    }
                                }
                                
                                if (!empty($validItems)) {
                                    $html .= '<div style="background: #1e293b; padding: 16px; border-radius: 8px; width: 100%; box-sizing: border-box;">';
                                    $html .= '<strong style="color: #38bdf8; display: block; margin-bottom: 10px; font-size: 0.95rem;">' . $labelSection . '</strong>';
                                    $html .= '<div style="display: flex; gap: 12px; flex-wrap: wrap;">';
                                    
                                    foreach ($validItems as $index => $item) {
                                        $url = str_starts_with($item['path'], 'http') ? $item['path'] : asset('storage/' . $item['path']);
                                        
                                        // Format label per gambar: Gambar X : [Nama Kategori] atau custom caption jika ada
                                        $imgNum = $index + 1;
                                        $imgCaption = !empty($item['caption']) 
                                            ? $item['caption'] 
                                            : "Gambar {$imgNum} : {$labelSection}";
                                        
                                        $html .= '<div style="display: flex; flex-direction: column; align-items: center; max-width: 120px;">';
                                        $html .= '<a href="' . $url . '" target="_blank"><img src="' . $url . '" style="width: 110px; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #475569;" /></a>';
                                        $html .= '<span style="font-size: 0.7rem; color: #94a3b8; margin-top: 6px; text-align: center; word-break: break-word; line-height: 1.2;">' . htmlspecialchars($imgCaption) . '</span>';
                                        $html .= '</div>';
                                    }
                                    
                                    $html .= '</div></div>';
                                }
                            }
                        };

                        // 1. Render Kolom Baru Filament
                        $renderFilamentImages('Sebelum Siaran', $record->evidence_sebelum_siaran);
                        $renderFilamentImages('Alat & Master', $record->ev_alat_studio);
                        $renderFilamentImages('Jaringan', $record->ev_jaringan);
                        $renderFilamentImages('Jalur AV', $record->ev_jalur_av);
                        $renderFilamentImages('Evidence Kendala', $record->pra_ev_kendala);

                        // 2. Render Data Lama
                        if (!empty($record->evidence)) {
                            $oldImg = is_string($record->evidence) ? json_decode($record->evidence, true) ?? $record->evidence : $record->evidence;
                            $oldImgArray = is_array($oldImg) ? $oldImg : [$oldImg];

                            $html .= '<div style="background: #1e293b; padding: 16px; border-radius: 8px; width: 100%; box-sizing: border-box;">';
                            $html .= '<strong style="color: #38bdf8; display: block; margin-bottom: 10px; font-size: 0.95rem;">📁 Arsip Evidence (Data Lama)</strong>';
                            $html .= '<div style="display: flex; gap: 12px; flex-wrap: wrap;">';
                            
                            foreach ($oldImgArray as $index => $img) {
                                $caption = '';
                                if (is_array($img)) {
                                    $caption = $img['keterangan'] ?? $img['caption'] ?? '';
                                    $img = reset($img);
                                }
                                if (!empty($img) && is_string($img)) {
                                    $url = str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                                    $imgNum = $index + 1;
                                    $imgCaption = !empty($caption) ? $caption : "Gambar {$imgNum} : Arsip Evidence";

                                    $html .= '<div style="display: flex; flex-direction: column; align-items: center; max-width: 120px;">';
                                    $html .= '<a href="' . $url . '" target="_blank"><img src="' . $url . '" style="width: 110px; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #475569;" /></a>';
                                    $html .= '<span style="font-size: 0.7rem; color: #94a3b8; margin-top: 6px; text-align: center; word-break: break-word; line-height: 1.2;">' . htmlspecialchars($imgCaption) . '</span>';
                                    $html .= '</div>';
                                }
                            }
                            $html .= '</div></div>';
                        }

                        if (!empty($record->link_drive)) {
                            $oldLink = is_string($record->link_drive) ? json_decode($record->link_drive, true) ?? $record->link_drive : $record->link_drive;
                            $oldLinkArray = is_array($oldLink) ? $oldLink : [$oldLink];

                            $html .= '<div style="background: #1e293b; padding: 16px; border-radius: 8px; width: 100%; box-sizing: border-box;">';
                            $html .= '<strong style="color: #38bdf8; display: block; margin-bottom: 10px; font-size: 0.95rem;">🔗 Arsip Link / File (Data Lama)</strong>';
                            $html .= '<div style="display: flex; gap: 12px; flex-wrap: wrap;">';
                            
                            foreach ($oldLinkArray as $index => $img) {
                                $caption = '';
                                if (is_array($img)) {
                                    $caption = $img['keterangan'] ?? $img['caption'] ?? '';
                                    $img = reset($img);
                                }
                                if (!empty($img) && is_string($img)) {
                                    $url = str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                                    $imgNum = $index + 1;
                                    $imgCaption = !empty($caption) ? $caption : "Gambar {$imgNum} : Arsip Link";

                                    $html .= '<div style="display: flex; flex-direction: column; align-items: center; max-width: 120px;">';
                                    $html .= '<a href="' . $url . '" target="_blank"><img src="' . $url . '" style="width: 110px; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #475569;" /></a>';
                                    $html .= '<span style="font-size: 0.7rem; color: #94a3b8; margin-top: 6px; text-align: center; word-break: break-word; line-height: 1.2;">' . htmlspecialchars($imgCaption) . '</span>';
                                    $html .= '</div>';
                                }
                            }
                            $html .= '</div></div>';
                        }

                        $html .= '</div>';
                        return new HtmlString($html);
                    }),

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
    // --- TAMBAHAN BEDAH MIKRO: FILTER DATA BERDASARKAN USER ---
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // 1. Jika yang login adalah Admin atau Developer (Noa), tampilkan SEMUA data
        if ($user->role === 'admin' || $user->email === 'noa@dev.id') {
            return parent::getEloquentQuery();
        }

        // 2. Jika yang login adalah TD biasa, HANYA tampilkan laporan dengan namanya
        return parent::getEloquentQuery()->where('nama_petugas', $user->name);
    }
    // ---------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => ListLaporanUtamas::route('/'),
            'create' => CreateLaporanUtama::route('/create'),
            'edit' => EditLaporanUtama::route('/{record}/edit'),
        ];
    }
}
