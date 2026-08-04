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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


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
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                ->required()
                                ->columnSpanFull(),
                            
                            TextInput::make('nama_petugas')
    ->label('Nama Petugas (TD)')
    ->formatStateUsing(fn ($state) => $state ?? auth()->user()->name)
    ->disabled()
 
    ->dehydrated()
    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state])) // Wajib ada agar nilai tetap tersimpan ke database saat disubmit
    ->required()
    ->columnSpanFull(),

                            Select::make('pdu_nama')
                                ->label('Petugas PDU')
                                ->options(Petugas::where('is_aktif', true)->where('jabatan_utama', 'PDU')->pluck('nama', 'nama'))
                                ->searchable()
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                ->required()
                                ->columnSpanFull(),
                                Select::make('asisten_pdu')
    ->label('Asisten PDU')
    ->placeholder('Pilih asisten (Opsional)')
    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
                                ->live()
->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        // Cek satu-satu file yang diupload (karena multiple)
        foreach (\Illuminate\Support\Arr::wrap($state) as $file) {
            if (is_string($file)) {
                // Jika file sudah berupa teks path (file lama)
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Jika file baru, simpan permanen ke folder 'evidence' di disk 'public'
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // 1. Simpan path yang benar ke database
        $record->update([$component->getName() => $paths]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->imageResizeMode('contain') // Mempertahankan proporsi gambar
    ->imageResizeTargetWidth('1080') // Me-resize lebar maksimal jadi 1080px (Kualitas HD standar)
    ->imageResizeTargetHeight('1080') // Me-resize tinggi maksimal jadi 1080px
    // ------------------------------------------------------------
    
    // Opsional: Kompresi lanjutan di server (mengurangi ukuran file tanpa mengurangi dimensi)
    ->directory('evidence')->required(),
                                
                            FileUpload::make('ev_alat_studio')
                                ->label('Alat & Master')
                                ->live()
->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        // Cek satu-satu file yang diupload (karena multiple)
        foreach (\Illuminate\Support\Arr::wrap($state) as $file) {
            if (is_string($file)) {
                // Jika file sudah berupa teks path (file lama)
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Jika file baru, simpan permanen ke folder 'evidence' di disk 'public'
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // 1. Simpan path yang benar ke database
        $record->update([$component->getName() => $paths]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })
                                    ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->imageResizeMode('contain') // Mempertahankan proporsi gambar
    ->imageResizeTargetWidth('1080') // Me-resize lebar maksimal jadi 1080px (Kualitas HD standar)
    ->imageResizeTargetHeight('1080') // Me-resize tinggi maksimal jadi 1080px
    // ------------------------------------------------------------
    
    // Opsional: Kompresi lanjutan di server (mengurangi ukuran file tanpa mengurangi dimensi)
     ->directory('evidence')->required(),

                            FileUpload::make('ev_jaringan')
                                ->label('Jaringan')
                                ->live()
->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        // Cek satu-satu file yang diupload (karena multiple)
        foreach (\Illuminate\Support\Arr::wrap($state) as $file) {
            if (is_string($file)) {
                // Jika file sudah berupa teks path (file lama)
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Jika file baru, simpan permanen ke folder 'evidence' di disk 'public'
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // 1. Simpan path yang benar ke database
        $record->update([$component->getName() => $paths]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->imageResizeMode('contain') // Mempertahankan proporsi gambar
    ->imageResizeTargetWidth('1080') // Me-resize lebar maksimal jadi 1080px (Kualitas HD standar)
    ->imageResizeTargetHeight('1080') // Me-resize tinggi maksimal jadi 1080px
    // ------------------------------------------------------------
    
    // Opsional: Kompresi lanjutan di server (mengurangi ukuran file tanpa mengurangi dimensi)
     ->directory('evidence')->required(),

                            FileUpload::make('ev_jalur_av')
                                ->label('Jalur AV')
                                ->live()
->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        // Cek satu-satu file yang diupload (karena multiple)
        foreach (\Illuminate\Support\Arr::wrap($state) as $file) {
            if (is_string($file)) {
                // Jika file sudah berupa teks path (file lama)
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Jika file baru, simpan permanen ke folder 'evidence' di disk 'public'
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // 1. Simpan path yang benar ke database
        $record->update([$component->getName() => $paths]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->imageResizeMode('contain') // Mempertahankan proporsi gambar
    ->imageResizeTargetWidth('1080') // Me-resize lebar maksimal jadi 1080px (Kualitas HD standar)
    ->imageResizeTargetHeight('1080') // Me-resize tinggi maksimal jadi 1080px
    // ------------------------------------------------------------
    
    // Opsional: Kompresi lanjutan di server (mengurangi ukuran file tanpa mengurangi dimensi)
     ->directory('evidence')->required(),
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
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('pra_ket_kendala')
                                ->label('Keterangan Kendala')
                                ->rows(2)
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                ->visible(fn (Get $get): bool => $get('pra_kendala') == '1') // Ubah === menjadi ==
                                ->required(fn (Get $get): bool => $get('pra_kendala') == '1') // Ubah === menjadi ==
                                ->columnSpanFull(),
                                
                            FileUpload::make('pra_ev_kendala')
                                ->label('Evidence Kendala')
                                ->disk('public')
                                ->directory('evidence')
                                ->image()->multiple()->maxFiles(2)->maxSize(10240)->imageResizeMode('contain') // Mempertahankan proporsi gambar
    ->imageResizeTargetWidth('1080') // Me-resize lebar maksimal jadi 1080px (Kualitas HD standar)
    ->imageResizeTargetHeight('1080') // Me-resize tinggi maksimal jadi 1080px
   
     ->directory('evidence')
                                ->visible(fn (Get $get): bool => $get('pra_kendala') == '1')
                                ->live()
->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        // Cek satu-satu file yang diupload (karena multiple)
        foreach (\Illuminate\Support\Arr::wrap($state) as $file) {
            if (is_string($file)) {
                // Jika file sudah berupa teks path (file lama)
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Jika file baru, simpan permanen ke folder 'evidence' di disk 'public'
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // 1. Simpan path yang benar ke database
        $record->update([$component->getName() => $paths]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })
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
                            ->minItems(fn (Get $get): int => $get('shift') === 'pagi' ? 3 : 4)
    ->defaultItems(fn (Get $get): int => $get('shift') === 'pagi' ? 3 : 4)
                            ->validationMessages([
                                 'min' => 'Log jam tayang wajib diisi minimal 4 program siaran.',
                                ])
                                ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
                                        ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                        ->required(),

                                    TextInput::make('nama_program_custom')
                                        ->label('Ketik Baru')
                                        ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
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
                                    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                    ->required(),

                                Select::make('status_siaran')
                                    ->label('Kendala Siaran')
                                    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                                    ->options([
                                        'Aman' => 'Aman',
                                        'Audio' => 'Audio',
                                        'Video' => 'Video',
                                        'Perangkat Lainnya' => 'Perangkat Lainnya',
                                    ])
                                    ->required(),

                                TextInput::make('catatan_kendala')
                                    ->label('Detil Kendala')
                                    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state])),
                                    
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
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                        ->required()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }
}