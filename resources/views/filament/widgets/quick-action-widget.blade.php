<x-filament-widgets::widget>
    <x-filament::section class="relative overflow-hidden bg-white/5 backdrop-blur-md border border-white/10 shadow-sm h-full flex items-center">
        <div class="flex items-center justify-between w-full">
            
           

            <!-- Area Tombol Kanan -->
            <div>
                {{ $this->createLaporanAction }}
            </div>

        </div>
    </x-filament::section>

    <!-- KOMPONEN INI WAJIB ADA AGAR POP-UP MODAL BISA MUNCUL -->
    <x-filament-actions::modals />
</x-filament-widgets::widget>