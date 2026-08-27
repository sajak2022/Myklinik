@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $isDokterOrPerawat = $user && (
        $user->hasRole(['Dokter', 'Perawat']) ||
        ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat']))
    );
@endphp

@if (! $isDokterOrPerawat)
    @php
        $createPasienUrl = \App\Filament\Resources\Pasiens\PasienResource::getUrl('create');
    @endphp

    <div class="flex items-center me-0">
        <div class="p-1 bg-gray-100/90 dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/70 rounded-xl shadow-xs backdrop-blur-xs flex items-center">
            <a href="{{ $createPasienUrl }}"
               x-data="{}"
               x-tooltip="{
                   content: 'Pendaftaran Pasien Baru',
                   theme: $store.theme,
               }"
               title="Pasien Baru"
               aria-label="Pasien Baru"
               class="flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-100/70 dark:hover:bg-emerald-950/60 active:scale-95 transition-all duration-150 focus:outline-none cursor-pointer">
                <x-heroicon-m-user-plus class="w-4 h-4 shrink-0" />
            </a>
        </div>
    </div>
@endif

