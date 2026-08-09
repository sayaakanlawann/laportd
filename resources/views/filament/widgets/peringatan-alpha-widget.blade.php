<x-filament-widgets::widget>
    <!-- x-filament::section akan membuat card dengan proporsi sempurna bawaan Filament -->
    <x-filament::section
        icon="heroicon-o-exclamation-triangle"
        icon-color="danger"
        class="ring-2 ring-danger-500/50 dark:ring-danger-500/30"
    >
        <!-- Judul Peringatan (Otomatis Merah) -->
        <x-slot name="heading">
            <span class="text-danger-600 dark:text-danger-400 font-extrabold tracking-tight">
                PERINGATAN! ADA LAPORAN YANG BELUM ANDA ISI
            </span>
        </x-slot>

        <!-- Sub-judul -->
        <x-slot name="description">
            <span class="text-danger-600/80 dark:text-danger-400/80 font-medium text-sm">
                Admin mencatat Anda belum mengisi laporan operasional untuk jadwal berikut. Mohon segera diselesaikan:
            </span>
        </x-slot>

        <!-- Daftar Laporan yang Belum Diisi -->
        <div class="mt-2 flex flex-col gap-3">
            @foreach($alphaLaporans as $laporan)
                <div class="flex items-center justify-between p-4 rounded-xl bg-danger-50 dark:bg-danger-500/10 border border-danger-200 dark:border-danger-500/20 shadow-sm">
                    
                    <!-- Info Tanggal dan Shift -->
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-danger-100 dark:border-danger-900">
                            <x-filament::icon
                                icon="heroicon-m-calendar-days"
                                class="h-6 w-6 text-danger-500 dark:text-danger-400"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($laporan->tanggal_tugas)->translatedFormat('d F Y') }}
                            </p>
                            <p class="text-sm font-bold text-danger-600 dark:text-danger-400 uppercase tracking-wider">
                                Shift {{ $laporan->shift }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Tombol Native Filament (Proporsional dan Interaktif) -->
                    <x-filament::button
                        href="{{ \App\Filament\Resources\LaporanUtamas\LaporanUtamaResource::getUrl('edit', ['record' => $laporan->id]) }}"
                        tag="a"
                        color="danger"
                        size="sm"
                        icon="heroicon-m-pencil-square"
                    >
                        Isi Laporan
                    </x-filament::button>

                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>