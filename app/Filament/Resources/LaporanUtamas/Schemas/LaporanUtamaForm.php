<?php

namespace App\Filament\Resources\LaporanUtamas\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\Petugas;
use App\Models\ProgramSiaran;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;


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
                            

// Masukkan di dalam schema form utama (paling atas/posisi bebas):
Hidden::make('shift')
    ->required(),
                            DatePicker::make('tanggal_tugas')
                                ->label('Tanggal Tugas')
                                ->default(now())
                                ->required()
                                ->columnSpanFull(),
                            
                            TextInput::make('nama_petugas')
    ->label('Nama Petugas (TD)')
    ->formatStateUsing(fn ($state) => $state ?? auth()->user()->name)
    ->disabled()
 
    ->dehydrated() // Wajib ada agar nilai tetap tersimpan ke database saat disubmit
    ->required()
    ->columnSpanFull(),

                            Select::make('pdu_nama')
                                ->label('Petugas PDU')
                                ->options(Petugas::where('is_aktif', true)->where('jabatan_utama', 'PDU')->pluck('nama', 'nama'))
                                ->searchable()
                                ->live()
                                ->required()
                                ->columnSpanFull(),
                                Select::make('asisten_pdu')
    ->label('Asisten PDU')
    ->placeholder('Pilih asisten (Opsional)')
    ->live()
    ->options(
        // Query langsung ke tabel user berdasarkan jabatan. 
        // Silakan ganti 'asisten_pdu' dengan nama role yang sebenarnya ada di database Abang
        Petugas::where('jabatan_utama', 'asisten_pdu')->pluck('nama', 'nama')
    )
    ->searchable()
    ->preload()
    ->columnSpanFull(),

                            Select::make('kru_lengkap')
                                ->label('Kehadiran Kru')
                                ->live()
                                ->options([
                                    '1' => 'Lengkap',
                                    '0' => 'Tidak Lengkap',
                                ])
                                ->required()
                                ->columnSpanFull(),

                            Select::make('tx_petugas_nama')
    ->label('Petugas TX (Transmisi)')
    ->options(
        Petugas::where('is_aktif', true)
            ->where('jabatan_utama', 'Transmisi')
            ->pluck('nama', 'nama')
    )
    ->multiple()
    ->live()
    ->searchable()
    ->required()
    ->columnSpanFull(),
                        ]),

                    // ------------------------------------------
                    // KOLOM 2 (KANAN, ROW 1): EVIDENCE RUTIN
                    // ------------------------------------------
                    Fieldset::make('Evidence Rutin (Pra-Siaran)')
                        ->schema([
                            FileUpload::make('evidence_sebelum_siaran')
                                ->label('Sebelum Siaran')
                                
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),
                                
                            FileUpload::make('ev_alat_studio')
                                ->label('Alat & Master')
                                
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),

                            FileUpload::make('ev_jaringan')
                                ->label('Jaringan')
                                
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),

                            FileUpload::make('ev_jalur_av')
                                ->label('Jalur AV')
                                
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')->required(),
                        ])->columns(2), // Berjajar rapi 2x2 di sebelah kanan

                    // ------------------------------------------
                    // KOLOM 2 (KANAN, ROW 2): KENDALA PRA-SIARAN
                    // Otomatis mengisi ruang kosong di bawah Evidence
                    // ------------------------------------------
                   // ------------------------------------------
                    // KOLOM 2 (KANAN, ROW 2): KENDALA PRA-SIARAN
                    // ------------------------------------------
                    Fieldset::make('Status Kendala Pra-Siaran')
                        ->schema([
                            Select::make('pra_kendala')
                                ->label('Apakah ada kendala sebelum siaran?')
                                ->options([
                                    '0' => 'Tidak Ada Kendala',
                                    '1' => 'Ada Kendala',
                                ])
                                ->default('0') // Berikan nilai default agar state awal tidak null
                                ->live() 
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('pra_ket_kendala')
                                ->label('Keterangan Kendala')
                                ->rows(2)
                                ->live(onBlur: true)
                                ->visible(fn (Get $get): bool => $get('pra_kendala') == '1') // Ubah === menjadi ==
                                ->required(fn (Get $get): bool => $get('pra_kendala') == '1') // Ubah === menjadi ==
                                ->columnSpanFull(),
                                
                            FileUpload::make('pra_ev_kendala')
                                ->label('Evidence Kendala')
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->directory('evidence')
                                ->visible(fn (Get $get): bool => $get('pra_kendala') == '1') // Ubah === menjadi ==
                                ->columnSpanFull(),
                                
                        ]),

                // Akhir Grid Atas

            // ==========================================
                // BAGIAN BAWAH: FULL WIDTH (LOG JAM TAYANG)
                // ==========================================
                Fieldset::make('Log Jam Tayang Siaran')
                    ->schema([
                        Repeater::make('siarans')
                            ->relationship('siarans')
                            ->label('') 
                            ->addActionLabel('+ Tambah Program')
                            ->defaultItems(4) // Otomatis memunculkan 4 baris saat buat laporan baru
                            ->minItems(4)
                            ->validationMessages([
                                 'min' => 'Log jam tayang wajib diisi minimal 4 program siaran.',
                                ])
                            ->columnSpanFull() 
                            ->columns(5)
                            ->schema([
                                
                                Select::make('jam_tayang')
    ->label('Waktu Siaran')
    ->options(function ($livewire) {
        // Ambil nilai shift secara paksa langsung dari array state Livewire
        // Cara ini paling kebal terhadap refresh AJAX di dalam Repeater.
        $shiftAktif = data_get($livewire->data, 'shift');

        // Fallback tambahan (untuk jaga-jaga saat halaman baru pertama kali dimuat)
        if (!$shiftAktif) {
            $shiftAktif = request()->query('shift');
        }

        $query = ProgramSiaran::where('is_aktif', true);

        // Filter ketat berdasarkan shift
        if ($shiftAktif === 'pagi') {
            $query->whereTime('jam_tayang_default', '>=', '09:00:00')
                  ->whereTime('jam_tayang_default', '<=', '12:00:00');
        } elseif ($shiftAktif === 'sore') {
            $query->where(function($q) {
                $q->whereTime('jam_tayang_default', '<', '09:00:00')
                  ->orWhereTime('jam_tayang_default', '>', '12:00:00');
            });
        }

        return $query->pluck('jam_tayang_default', 'jam_tayang_default');
    })
    ->live() 
    ->required()
    ->afterStateHydrated(function (Select $component, $record) {
        if ($record && $record->jam_tayang && $record->jam_selesai) {
            $jamMulai = \Carbon\Carbon::parse($record->jam_tayang)->format('H:i');
            $jamSelesai = \Carbon\Carbon::parse($record->jam_selesai)->format('H:i');
            $component->state("{$jamMulai}|{$jamSelesai}");
        }
    })
    ->dehydrateStateUsing(function ($state, $set) {
        if ($state && str_contains($state, '|')) {
            $pecah = explode('|', $state);
            
            if (is_callable($set)) {
                $set('jam_selesai', trim($pecah[1]));
            }

            return trim($pecah[0]);
        }
        return $state;
    }),

    Hidden::make('jam_selesai'),

                                Group::make()->schema([
                                    Select::make('nama_program')
                                        ->label('Program')
                                        // Hapus type hinting (Get $get) menjadi ($get) saja agar aman dari konflik namespace
                                        ->options(function ($get) {
                                            $waktu = $get('jam_tayang'); 
                                            if (! $waktu) return [];
                                            
                                            $jamMulai = str_contains($waktu, '|') ? explode('|', $waktu)[0] : $waktu;
                                            
                                            $opsi = ProgramSiaran::where('jam_tayang_default', 'like', "%{$jamMulai}%")->pluck('nama_program', 'nama_program')->toArray();
                                            $opsi['Other'] = 'Lainnya (Ketik Manual)...';
                                            return $opsi;
                                        })
                                        ->live()
                                        ->required(),

                                    TextInput::make('nama_program_custom')
                                        ->label('Ketik Baru')
                                        ->live()
                                        // Ubah juga type hinting di sini untuk jaga-jaga
                                        ->visible(fn ($get): bool => $get('nama_program') === 'Other')
                                        ->required(fn ($get): bool => $get('nama_program') === 'Other'),
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
                                    ->live()
                                    ->required(),

                                Select::make('status_siaran')
                                    ->label('Kendala Siaran')
                                    ->live()
                                    ->options([
                                        'Aman' => 'Aman',
                                        'Audio' => 'Audio',
                                        'Video' => 'Video',
                                        'Perangkat Lainnya' => 'Perangkat Lainnya',
                                    ])
                                    ->required(),

                                TextInput::make('catatan_kendala')
                                    ->label('Detil Kendala')
                                    ->live(onBlur: true),
                                    
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
                        ->live(onBlur: true)
                        ->required()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }
}