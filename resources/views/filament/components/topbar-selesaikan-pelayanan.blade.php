@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $isAuthorized = $user && (
        $user->hasRole(['super_admin', 'Admin', 'Dokter']) ||
        ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter']))
    );
@endphp

@if ($isAuthorized)
    <div
        x-data="{
            isDetail: false,
            checkPath() {
                this.isDetail = window.location.pathname.includes('/detail-kunjungan/') || window.location.pathname.endsWith('/detail-kunjungan');
            },
            bukaModalFinal() {
                if (window.Livewire) {
                    window.Livewire.dispatch('trigger-selesaikan-pelayanan');
                    const comp = window.Livewire.all().find(c => c.name && (c.name.includes('cppt-pasien') || c.name.includes('pemeriksaan-pasien')));
                    if (comp && typeof comp.call === 'function') {
                        comp.call('triggerSelesaikanPelayanan');
                    }
                }
            },
            bukaModalBatalFinal() {
                if (window.Livewire) {
                    window.Livewire.dispatch('trigger-batalkan-final');
                    const comp = window.Livewire.all().find(c => c.name && (c.name.includes('cppt-pasien') || c.name.includes('pemeriksaan-pasien')));
                    if (comp && typeof comp.call === 'function') {
                        comp.call('triggerBatalkanFinal');
                    }
                }
            }
        }"
        x-init="
            checkPath();
            document.addEventListener('livewire:navigated', () => checkPath());
            window.addEventListener('popstate', () => checkPath());
        "
        x-show="isDetail"
        x-cloak
        style="display: none;"
        class="flex items-center me-0"
    >
        <div class="flex items-center gap-1 p-1 bg-gray-100/90 dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/70 rounded-xl shadow-xs backdrop-blur-xs">
            {{-- Tombol Batalkan Final --}}
            <button
                type="button"
                @click="bukaModalBatalFinal()"
                x-data="{}"
                x-tooltip="{
                    content: 'Batalkan Final',
                    theme: $store.theme,
                }"
                title="Batalkan Final"
                aria-label="Batalkan Final"
                class="flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 hover:bg-amber-100/70 dark:hover:bg-amber-950/60 active:scale-95 transition-all duration-150 focus:outline-none cursor-pointer"
            >
                <x-heroicon-m-arrow-uturn-left class="w-4 h-4 shrink-0" />
            </button>

            {{-- Divider --}}
            <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>

            {{-- Tombol Final --}}
            <button
                type="button"
                @click="bukaModalFinal()"
                x-data="{}"
                x-tooltip="{
                    content: 'Final',
                    theme: $store.theme,
                }"
                title="Final"
                aria-label="Final"
                class="flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-100/70 dark:hover:bg-emerald-950/60 active:scale-95 transition-all duration-150 focus:outline-none cursor-pointer"
            >
                <x-heroicon-m-check-badge class="w-4 h-4 shrink-0" />
            </button>
        </div>
    </div>
@endif
