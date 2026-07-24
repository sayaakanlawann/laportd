<?php

namespace App\Filament\Resources\LaporanUtamas\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\Petugas;
use App\Models\ProgramSiaran;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class LaporanUtamaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            
            // ==========================================
            // GRID UTAMA 2 KOLOM (MENGHINDARI TITIK (1,1) MENUMPUK)
            // ==========================================
            
    ->columns([
        'default' => 1,
        'lg' => 2,
    ])
                ->schema([

                    // ------------------------------------------
                    // KOLOM 1 (KIRI, ROW 1): DATA PERSONIL & WAKTU
                    // ------------------------------------------
                    Fieldset::make('Data Personil & Waktu')
                        ->schema([
                            DatePicker::make('tanggal_tugas')
                                ->label('Tanggal Tugas')
                                ->default(now())
                                ->required()
                                ->columnSpanFull(),
                            
                            Select::make('nama_petugas')
                                ->label('Nama Petugas (TD)')
                                ->options(Petugas::where('is_aktif', true)->where('jabatan_utama', 'Technical Director')->pluck('nama', 'nama'))
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),

                            Select::make('pdu_nama')
                                ->label('Petugas PDU')
                                ->options(Petugas::where('is_aktif', true)->where('jabatan_utama', 'PDU')->pluck('nama', 'nama'))
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),

                            Select::make('kru_lengkap')
                                ->label('Kehadiran Kru')
                                ->options([
                                    '1' => 'Lengkap',
                                    '0' => 'Tidak Lengkap',
                                ])
                                ->required()
                                ->columnSpanFull(),

                            Repeater::make('tx_petugas_nama')
                                ->label('Petugas TX (Transmisi)')
                                ->schema([
                                    Select::make('nama')
                                        ->options(
                                            Petugas::where('is_aktif', true)
                                                ->where('jabatan_utama', 'Transmisi')
                                                ->pluck('nama', 'nama')
                                        )
                                        ->required(),
                                ])
                                ->addActionLabel('+ Tambah TX')
                                ->columnSpanFull(),
                        ]),

                    // ------------------------------------------
                    // KOLOM 2 (KANAN, ROW 1): EVIDENCE RUTIN
                    // ------------------------------------------
                    Fieldset::make('Evidence Rutin (Pra-Siaran)')
                        ->schema([
                            FileUpload::make('evidence_sebelum_siaran')
                                ->label('Sebelum Siaran')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),
                                
                            FileUpload::make('ev_alat_studio')
                                ->label('Alat & Master')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),

                            FileUpload::make('ev_jaringan')
                                ->label('Jaringan')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),

                            FileUpload::make('ev_jalur_av')
                                ->label('Jalur AV')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),
                        ])->columns(2), // Berjajar rapi 2x2 di sebelah kanan

                    // ------------------------------------------
                    // KOLOM 2 (KANAN, ROW 2): KENDALA PRA-SIARAN
                    // Otomatis mengisi ruang kosong di bawah Evidence
                    // ------------------------------------------
                    Fieldset::make('Status Kendala Pra-Siaran')
                        ->schema([
                            Select::make('pra_kendala')
                                ->label('Apakah ada kendala sebelum siaran?')
                                ->options([
                                    '0' => 'Tidak Ada Kendala',
                                    '1' => 'Ada Kendala',
                                ])
                                ->live() 
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('pra_ket_kendala')
                                ->label('Keterangan Kendala')
                                ->rows(2)
                                ->visible(fn (Get $get): bool => $get('pra_kendala') === '1')
                                ->required(fn (Get $get): bool => $get('pra_kendala') === '1')
                                ->columnSpanFull(),
                                
                            FileUpload::make('pra_ev_kendala')
                                ->label('Evidence Kendala')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')
                                ->visible(fn (Get $get): bool => $get('pra_kendala') === '1')
                                ->columnSpanFull(),
                        ]),

                // Akhir Grid Atas

            // ==========================================
            // BAGIAN BAWAH: LOG JAM TAYANG (FULL WIDTH)
            // ==========================================
            Fieldset::make('Log Jam Tayang Siaran')
                ->schema([
                    Repeater::make('siarans')
                        ->relationship('siarans')
                        ->label('') 
                        ->addActionLabel('+ Tambah Program')
                        ->columnSpanFull() 
                        ->columns(5) 
                        ->schema([
                            Select::make('waktu_siaran')
                                ->label('Waktu')
                                ->options(ProgramSiaran::where('is_aktif', true)->pluck('jam_tayang_default', 'jam_tayang_default'))
                                ->live() 
                                ->required(),

                            \Filament\Schemas\Components\Group::make()->schema([
                                Select::make('nama_program')
                                    ->label('Program')
                                    ->options(function (Get $get) {
                                        $waktu = $get('waktu_siaran');
                                        if (! $waktu) return [];
                                        
                                        $opsi = ProgramSiaran::where('jam_tayang_default', $waktu)->pluck('nama_program', 'nama_program')->toArray();
                                        $opsi['Other'] = 'Lainnya (Ketik Manual)...';
                                        return $opsi;
                                    })
                                    ->live()
                                    ->required(),

                                TextInput::make('nama_program_custom')
                                    ->label('Ketik Baru')
                                    ->visible(fn (Get $get): bool => $get('nama_program') === 'Other')
                                    ->required(fn (Get $get): bool => $get('nama_program') === 'Other'),
                            ]),

                            Select::make('jenis_acara')
                                ->label('Jenis')
                                ->options([
                                    'Live Studio 1' => 'Live Studio 1',
                                    'Live Studio 2' => 'Live Studio 2',
                                    'Live Studio 3' => 'Live Studio 3',
                                    'Relay' => 'Relay',
                                    'Relay Jakarta' => 'Relay Jakarta',
                                    'Relay Kalbar' => 'Relay Kalbar',
                                    'Relay Kaltim' => 'Relay Kaltim',
                                    'Relay Kalteng' => 'Relay Kalteng',
                                    'Relay Kaltara' => 'Relay Kaltara',
                                    'Record' => 'Record',
                                    'Playback' => 'Playback',
                                ])
                                ->searchable()
                                ->required(),

                            Select::make('status_siaran')
                                ->label('Status')
                                ->options([
                                    'Aman' => 'Aman',
                                    'Audio' => 'Audio',
                                    'Video' => 'Video',
                                ])
                                ->required(),

                            TextInput::make('catatan_kendala')
                                ->label('Catatan'),
                        ])
                ])->columnSpanFull(),

            // ==========================================
            // BAGIAN BAWAH: FINALISASI (FULL WIDTH)
            // ==========================================
            Fieldset::make('Finalisasi')
                ->schema([
                    Textarea::make('kesimpulan')
                        ->label('Kesimpulan Akhir')
                        ->rows(3)
                        ->required()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }
}