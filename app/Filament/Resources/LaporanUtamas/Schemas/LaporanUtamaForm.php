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
use Illuminate\Support\Arr;

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
->rules([
        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
            // Karena multiple(), value bisa berupa array, jadi kita loop
            $files = Arr::wrap($value);
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $hash = md5_file($file->getRealPath());

                    // Cek apakah hash ini ada di laporan FINAL lain (bukan laporan ini sendiri)
                    $isDuplicate = \App\Models\LaporanUtama::where('status', 'final')
                        ->where('id', '!=', $record?->id ?? 0) // Abaikan id laporan yang sedang diedit
                        ->where('semua_hash_foto', 'like', '%' . $hash . '%') // Cek brankas hash
                        ->exists();

                    if ($isDuplicate) {
                        $fail('KECURANGAN TERDETEKSI: Foto ini (atau foto yang mirip) sudah pernah digunakan di laporan lain!');
                    }
                }
            }
        },
    ])

    // 🔥 MODIFIKASI AFTER STATE UPDATED ABANG 🔥
    ->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        $newHashes = []; // Array untuk menampung sidik jari dari foto baru

        foreach (Arr::wrap($state) as $file) {
            if (is_string($file)) {
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Ambil sidik jari DULU, baru simpan filenya
                $newHashes[] = md5_file($file->getRealPath());
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // Ambil isi brankas hash lama, lalu gabungkan dengan hash yang baru
        $existingHashes = $record->semua_hash_foto ? explode(',', $record->semua_hash_foto) : [];
        $mergedHashes = array_unique(array_filter(array_merge($existingHashes, $newHashes)));

        // 1. Simpan path gambar DAN isi brankas hash terbaru ke database
        $record->update([
            $component->getName() => $paths,
            'semua_hash_foto' => implode(',', $mergedHashes) // Simpan dalam bentuk teks pisah koma
        ]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                
    ->disk('public')
    ->directory('evidence')
    ->image()
    ->multiple()
    ->maxFiles(2)
    ->maxSize(10240)
    ->imageResizeMode('contain') 
    ->imageResizeTargetWidth('1080') 
    ->imageResizeTargetHeight('1080') 
    ->required(),
                                
                            FileUpload::make('ev_alat_studio')
                                ->label('Alat & Master')
                                ->live()
->rules([
        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
            // Karena multiple(), value bisa berupa array, jadi kita loop
            $files = Arr::wrap($value);
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $hash = md5_file($file->getRealPath());

                    // Cek apakah hash ini ada di laporan FINAL lain (bukan laporan ini sendiri)
                    $isDuplicate = \App\Models\LaporanUtama::where('status', 'final')
                        ->where('id', '!=', $record?->id ?? 0) // Abaikan id laporan yang sedang diedit
                        ->where('semua_hash_foto', 'like', '%' . $hash . '%') // Cek brankas hash
                        ->exists();

                    if ($isDuplicate) {
                        $fail('KECURANGAN TERDETEKSI: Foto ini (atau foto yang mirip) sudah pernah digunakan di laporan lain!');
                    }
                }
            }
        },
    ])

    // 🔥 MODIFIKASI AFTER STATE UPDATED ABANG 🔥
    ->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        $newHashes = []; // Array untuk menampung sidik jari dari foto baru

        foreach (Arr::wrap($state) as $file) {
            if (is_string($file)) {
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Ambil sidik jari DULU, baru simpan filenya
                $newHashes[] = md5_file($file->getRealPath());
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // Ambil isi brankas hash lama, lalu gabungkan dengan hash yang baru
        $existingHashes = $record->semua_hash_foto ? explode(',', $record->semua_hash_foto) : [];
        $mergedHashes = array_unique(array_filter(array_merge($existingHashes, $newHashes)));

        // 1. Simpan path gambar DAN isi brankas hash terbaru ke database
        $record->update([
            $component->getName() => $paths,
            'semua_hash_foto' => implode(',', $mergedHashes) // Simpan dalam bentuk teks pisah koma
        ]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                
    ->disk('public')
    ->directory('evidence')
    ->image()
    ->multiple()
    ->maxFiles(2)
    ->maxSize(10240)
    ->imageResizeMode('contain') 
    ->imageResizeTargetWidth('1080') 
    ->imageResizeTargetHeight('1080') 
    ->required(),

                            FileUpload::make('ev_jaringan')
                                ->label('Jaringan')
                                ->live()
->rules([
        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
            // Karena multiple(), value bisa berupa array, jadi kita loop
            $files = Arr::wrap($value);
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $hash = md5_file($file->getRealPath());

                    // Cek apakah hash ini ada di laporan FINAL lain (bukan laporan ini sendiri)
                    $isDuplicate = \App\Models\LaporanUtama::where('status', 'final')
                        ->where('id', '!=', $record?->id ?? 0) // Abaikan id laporan yang sedang diedit
                        ->where('semua_hash_foto', 'like', '%' . $hash . '%') // Cek brankas hash
                        ->exists();

                    if ($isDuplicate) {
                        $fail('KECURANGAN TERDETEKSI: Foto ini (atau foto yang mirip) sudah pernah digunakan di laporan lain!');
                    }
                }
            }
        },
    ])

    // 🔥 MODIFIKASI AFTER STATE UPDATED ABANG 🔥
    ->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        $newHashes = []; // Array untuk menampung sidik jari dari foto baru

        foreach (Arr::wrap($state) as $file) {
            if (is_string($file)) {
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Ambil sidik jari DULU, baru simpan filenya
                $newHashes[] = md5_file($file->getRealPath());
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // Ambil isi brankas hash lama, lalu gabungkan dengan hash yang baru
        $existingHashes = $record->semua_hash_foto ? explode(',', $record->semua_hash_foto) : [];
        $mergedHashes = array_unique(array_filter(array_merge($existingHashes, $newHashes)));

        // 1. Simpan path gambar DAN isi brankas hash terbaru ke database
        $record->update([
            $component->getName() => $paths,
            'semua_hash_foto' => implode(',', $mergedHashes) // Simpan dalam bentuk teks pisah koma
        ]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                
    ->disk('public')
    ->directory('evidence')
    ->image()
    ->multiple()
    ->maxFiles(2)
    ->maxSize(10240)
    ->imageResizeMode('contain') 
    ->imageResizeTargetWidth('1080') 
    ->imageResizeTargetHeight('1080') 
    ->required(),

                            FileUpload::make('ev_jalur_av')
                                ->label('Jalur AV')
                                ->live()
->rules([
        fn ($record) => function (string $attribute, $value, Closure $fail) use ($record) {
            // Karena multiple(), value bisa berupa array, jadi kita loop
            $files = Arr::wrap($value);
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $hash = md5_file($file->getRealPath());

                    // Cek apakah hash ini ada di laporan FINAL lain (bukan laporan ini sendiri)
                    $isDuplicate = \App\Models\LaporanUtama::where('status', 'final')
                        ->where('id', '!=', $record?->id ?? 0) // Abaikan id laporan yang sedang diedit
                        ->where('semua_hash_foto', 'like', '%' . $hash . '%') // Cek brankas hash
                        ->exists();

                    if ($isDuplicate) {
                        $fail('KECURANGAN TERDETEKSI: Foto ini (atau foto yang mirip) sudah pernah digunakan di laporan lain!');
                    }
                }
            }
        },
    ])

    // 🔥 MODIFIKASI AFTER STATE UPDATED ABANG 🔥
    ->afterStateUpdated(function ($state, $record, $component) {
        if (!$record) return;

        $paths = [];
        $newHashes = []; // Array untuk menampung sidik jari dari foto baru

        foreach (Arr::wrap($state) as $file) {
            if (is_string($file)) {
                $paths[] = $file;
            } elseif ($file instanceof TemporaryUploadedFile) {
                // Ambil sidik jari DULU, baru simpan filenya
                $newHashes[] = md5_file($file->getRealPath());
                $paths[] = $file->store('evidence', 'public');
            }
        }

        // Ambil isi brankas hash lama, lalu gabungkan dengan hash yang baru
        $existingHashes = $record->semua_hash_foto ? explode(',', $record->semua_hash_foto) : [];
        $mergedHashes = array_unique(array_filter(array_merge($existingHashes, $newHashes)));

        // 1. Simpan path gambar DAN isi brankas hash terbaru ke database
        $record->update([
            $component->getName() => $paths,
            'semua_hash_foto' => implode(',', $mergedHashes) // Simpan dalam bentuk teks pisah koma
        ]);

        // 2. Beritahu Filament agar sinkron dengan file yang baru dipindah
        $component->state($paths);
    })                                
    ->disk('public')
    ->directory('evidence')
    ->image()
    ->multiple()
    ->maxFiles(2)
    ->maxSize(10240)
    ->imageResizeMode('contain') 
    ->imageResizeTargetWidth('1080') 
    ->imageResizeTargetHeight('1080') 
    ->required(),
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
    ->label('Siaran')
    ->required()
    ->addActionLabel('+ Tambah Program')
    ->minItems(fn (Get $get): int => $get('shift') === 'pagi' ? 3 : 4)
    ->defaultItems(fn (Get $get): int => $get('shift') === 'pagi' ? 3 : 4)
    
    // ========================================================
    // 1. KETIKA MEMUAT DATA DARI DATABASE (EDIT/REFRESH)
    // ========================================================
    ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
        // A. Kembalikan format jam tayang agar cocok dengan dropdown
        if (!empty($data['jam_tayang']) && !empty($data['jam_selesai'])) {
            $jamMulai = \Carbon\Carbon::parse($data['jam_tayang'])->format('H:i');
            $jamSelesai = \Carbon\Carbon::parse($data['jam_selesai'])->format('H:i');
            $data['jam_tayang'] = "{$jamMulai}|{$jamSelesai}";
        }

        // B. Cegah Dropdown Reset: Jika nama program tidak ada di master data, paksa pilih "Other"
        if (!empty($data['nama_program'])) {
            $isKnown = \App\Models\ProgramSiaran::where('nama_program', $data['nama_program'])->exists();
            
            if (!$isKnown) {
                // Pindahkan nama dari DB ke inputan teks 'Ketik Baru'
                $data['nama_program_custom'] = $data['nama_program']; 
                // Setel dropdown kembali ke 'Other'
                $data['nama_program'] = 'Other'; 
            }
        }
        return $data;
    })

    // ========================================================
    // 2. KETIKA MENYIMPAN KE DATABASE (SUBMIT FINAL)
    // ========================================================
    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
        if (!empty($data['jam_tayang']) && str_contains($data['jam_tayang'], '|')) {
            $pecah = explode('|', $data['jam_tayang']);
            $data['jam_tayang'] = trim($pecah[0]);
            $data['jam_selesai'] = trim($pecah[1]);
        }
        if (isset($data['nama_program']) && $data['nama_program'] === 'Other') {
            $data['nama_program'] = $data['nama_program_custom'] ?? 'Program Tidak Diketahui';
        }
        unset($data['nama_program_custom']); // Hapus sebelum ditolak MySQL
        return $data;
    })
    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
        if (!empty($data['jam_tayang']) && str_contains($data['jam_tayang'], '|')) {
            $pecah = explode('|', $data['jam_tayang']);
            $data['jam_tayang'] = trim($pecah[0]);
            $data['jam_selesai'] = trim($pecah[1]);
        }
        if (isset($data['nama_program']) && $data['nama_program'] === 'Other') {
            $data['nama_program'] = $data['nama_program_custom'] ?? 'Program Tidak Diketahui';
        }
        unset($data['nama_program_custom']); // Hapus sebelum ditolak MySQL
        return $data;
    })
    
    // ... Pengaturan schema kolom di bawahnya ...
    ->columnSpanFull()
    ->columns(5)
    ->schema([
        
        Select::make('jam_tayang')
    ->label('Waktu Siaran')
    ->options(function ($livewire) {
        $shiftAktif = data_get($livewire->data, 'shift') ?? request()->query('shift');
        $query = ProgramSiaran::where('is_aktif', true);

        if ($shiftAktif === 'pagi') {
            $query->whereTime('jam_tayang_default', '>=', '09:00:00')
                  ->whereTime('jam_tayang_default', '<=', '12:00:00');
        } elseif ($shiftAktif === 'sore') {
            $query->where(function($q) {
                $q->whereTime('jam_tayang_default', '<', '09:00:00')
                  ->orWhereTime('jam_tayang_default', '>', '12:00:00');
            });
        }
        
        // --- MODIFIKASI DIMULAI DI SINI ---
        // 1. Tarik data asli ke dalam bentuk Array
        $jadwalAsli = $query->pluck('jam_tayang_default', 'jam_tayang_default')->toArray();
        
        $opsiRapi = [];
        
        // 2. Ubah tampilannya
        foreach ($jadwalAsli as $key => $value) {
            $opsiRapi[$key] = str_replace('|', ' - ', $value);
        }
        
        return $opsiRapi;
        // --- MODIFIKASI SELESAI ---
    })
    ->live()
    ->required(),
            
            // ❌ HAPUS ->dehydrateStateUsing DI SINI KARENA MERUSAK FORM SAAT ONBLUR

        Hidden::make('jam_selesai'),

        Group::make()->schema([
            Select::make('nama_program')
                ->label('Program')
                ->options(function ($get) {
                    $waktu = $get('jam_tayang'); 
                    if (! $waktu) return [];
                    
                    $jamMulai = str_contains($waktu, '|') ? explode('|', $waktu)[0] : $waktu;
                    $opsi = ProgramSiaran::where('jam_tayang_default', 'like', "%{$jamMulai}%")->pluck('nama_program', 'nama_program')->toArray();
                    $opsi['Other'] = 'Lainnya (Ketik Manual)...';
                    return $opsi;
                })
                ->live() // Wajib agar Ketik Baru muncul
                ->required(),

            TextInput::make('nama_program_custom')
                ->label('Ketik Baru')
                ->visible(fn ($get): bool => $get('nama_program') === 'Other')
                ->required(fn ($get): bool => $get('nama_program') === 'Other')
                ->live(onBlur: true), // ✅ SEKARANG INI AMAN DIGUNAKAN
        ]),

        // ... Lanjutkan kolom jenis acara & kendala seperti sebelumnya ...

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
    // ->live() <--- DIHAPUS SAJA: Karena ini bukan trigger yang memunculkan inputan lain
    ->required(),

Select::make('status_siaran')
    ->label('Kendala Siaran')
    ->live() // <--- INI SUDAH BENAR: Sebagai trigger untuk detil kendala
    ->options([
        'Aman' => 'Aman',
        'Audio' => 'Audio',
        'Video' => 'Video',
        'Perangkat Lainnya' => 'Perangkat Lainnya',
    ])
    ->required(),

TextInput::make('catatan_kendala')
    ->label('Detil Kendala')
    // ->live(onBlur: true) <--- DIHAPUS SAJA: Sepertinya tadi tersisa di sini
    ->visible(fn ($get): bool => $get('status_siaran') !== 'Aman') // <--- KEMBALIKAN LOGIKA INI (Sepertinya terpotong)
    ->required(fn ($get): bool => $get('status_siaran') !== 'Aman')
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
    ->afterStateUpdated(fn ($state, $record, $component) => $record?->update([$component->getName() => $state]))
                        ->required()
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }
}