@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $isAuthorized = $user && (
        $user->hasRole(['super_admin', 'Dokter']) ||
        ($user->pegawai && $user->pegawai->profesi === 'Dokter')
    );
@endphp

@if ($isAuthorized)
    <div
        x-data="{
            isDetail: window.location.pathname.includes('detail-kunjungan')
        }"
        x-init="
            const checkPath = () => {
                isDetail = window.location.pathname.includes('detail-kunjungan');
            };
            window.addEventListener('livewire:navigated', checkPath);
            window.addEventListener('popstate', checkPath);
            checkPath();
        "
        x-show="isDetail"
        x-cloak
        class="flex items-center me-1"
    >
        <button
            type="button"
            @click="Livewire.dispatch('trigger-selesaikan-pelayanan')"
            x-data="{}"
            x-tooltip="{
                content: 'Selesaikan Pelayanan',
                theme: $store.theme,
            }"
            title="Selesaikan Pelayanan"
            aria-label="Selesaikan Pelayanan"
            class="fi-icon-btn relative flex items-center justify-center w-9 h-9 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors focus:outline-none cursor-pointer"
        >
            <x-heroicon-m-check-badge class="w-5 h-5 shrink-0" />
        </button>
    </div>
@endif
