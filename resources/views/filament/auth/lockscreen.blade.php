<x-filament-panels::page.simple>
    <style>
        .lockscreen-logo-light {
            display: block;
        }
        .lockscreen-logo-dark {
            display: none;
        }
        html.dark .lockscreen-logo-light {
            display: none !important;
        }
        html.dark .lockscreen-logo-dark {
            display: block !important;
        }
        html:not(.dark) .lockscreen-logo-light {
            display: block !important;
        }
        html:not(.dark) .lockscreen-logo-dark {
            display: none !important;
        }
    </style>

    <div>
        {{-- Header: Logo & Title --}}
        <div style="text-align: center; margin-bottom: 1.25rem; display: flex; flex-direction: column; align-items: center;">
            {{-- Logo Light Mode --}}
            <img
                src="{{ \App\Models\Pengaturan::getLogoUrl() }}"
                alt="{{ \App\Models\Pengaturan::getPengaturan()->nama_klinik ?? 'Myklinik' }}"
                class="lockscreen-logo-light"
                style="height: 2.75rem; max-width: 100%; object-fit: contain; margin: 0 auto 0.75rem auto; transform: translateX(26px);"
            />

            {{-- Logo Dark Mode --}}
            <img
                src="{{ \App\Models\Pengaturan::getDarkModeLogoUrl() }}"
                alt="{{ \App\Models\Pengaturan::getPengaturan()->nama_klinik ?? 'Myklinik' }}"
                class="lockscreen-logo-dark"
                style="height: 2.75rem; max-width: 100%; object-fit: contain; margin: 0 auto 0.75rem auto; transform: translateX(26px);"
            />

            <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white" style="margin: 0; line-height: 1.25;">
                Sesi Terkunci
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem; margin-bottom: 0;">
                Masukkan password akun Anda untuk membuka sesi.
            </p>
        </div>

        {{-- Avatar & User Info --}}
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin-bottom: 0.75rem;">
            <div style="position: relative; display: inline-block; margin-bottom: 0.5rem;">
                <img
                    src="{{ filament()->getUserAvatarUrl(auth()->user()) }}"
                    alt="{{ auth()->user()->name ?? 'User' }}"
                    style="width: 4.75rem; height: 4.75rem; border-radius: 9999px; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid rgba(59, 130, 246, 0.5); display: block;"
                />
                <div style="position: absolute; bottom: -0.25rem; right: -0.25rem; background-color: #f59e0b; color: #ffffff; border-radius: 9999px; padding: 0.25rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff;" class="dark:border-gray-900">
                    <x-filament::icon icon="heroicon-m-lock-closed" style="width: 14px; height: 14px;" />
                </div>
            </div>

            <h3 class="text-base font-semibold text-gray-950 dark:text-white" style="margin: 0; line-height: 1.3;">
                {{ auth()->user()->name ?? 'Pengguna' }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.15rem; margin-bottom: 0;">
                {{ auth()->user()->email ?? '' }}
            </p>
        </div>

        {{-- Form Password --}}
        <form wire:submit="authenticate" class="space-y-6 mt-4">
            {{ $this->form }}

            <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <x-filament::button
                    type="submit"
                    size="lg"
                    class="w-full"
                    style="width: 100%; display: flex; justify-content: center; align-items: center;"
                    icon="heroicon-m-lock-open"
                    wire:target="authenticate"
                    wire:loading.attr="disabled"
                >
                    <span wire:target="authenticate" wire:loading.remove>Buka Kunci</span>
                    <span wire:target="authenticate" wire:loading>Memverifikasi...</span>
                </x-filament::button>

                <div style="text-align: center; padding-top: 0.25rem;">
                    <button
                        type="button"
                        wire:click="logout"
                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline focus:outline-none"
                        style="font-size: 0.8125rem; color: #6b7280; text-decoration: underline; background: none; border: none; cursor: pointer;"
                    >
                        Bukan Anda? Masuk dengan akun lain
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page.simple>
