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

    <div class="flex items-center me-3">
        <a href="{{ $createPasienUrl }}"
           x-data="{}"
           x-tooltip="{
               content: 'Pasien Baru',
               theme: $store.theme,
           }"
           title="Pasien Baru"
           aria-label="Pasien Baru"
           class="fi-icon-btn relative flex items-center justify-center w-9 h-9 text-gray-500 hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors focus:outline-none">
            <x-heroicon-m-user-plus class="w-5 h-5 shrink-0" />
        </a>
    </div>
@endif

