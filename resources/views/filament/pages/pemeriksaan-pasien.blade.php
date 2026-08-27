<x-filament-panels::page>
    <style>
        @media (min-width: 1024px) {
            .fi-page-has-sub-navigation .fi-header {
                position: sticky !important;
                top: 4rem !important;
                z-index: 20 !important;
                width: max-content !important;
                max-width: 16rem !important;
                background: transparent !important;
                margin-bottom: 0 !important;
                padding-top: 0.25rem !important;
                padding-bottom: 0.25rem !important;
            }

            .fi-page-sub-navigation-sidebar-ctn {
                position: sticky !important;
                top: 9.5rem !important;
                align-self: flex-start !important;
                max-height: calc(100vh - 10.5rem) !important;
                overflow-y: auto !important;
                z-index: 15 !important;
                width: 16rem !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
        }
    </style>

    <div
        x-on:trigger-selesaikan-pelayanan.window="$wire.triggerSelesaikanPelayanan()"
        x-on:trigger-batalkan-final.window="$wire.triggerBatalkanFinal()"
        x-on:trigger-batalkan-pendaftaran.window="$wire.triggerBatalkanPendaftaran()"
    >
    @if ($pendaftaran)
        {{-- Unified Patient Header Component --}}
        @include('filament.clusters.detail-kunjungan.components.patient-header', ['pendaftaran' => $pendaftaran])

        {{-- ========================================================================= --}}
        {{-- LAYOUT SPLIT 2 KOLOM: FORMULIR & TABEL (KIRI) vs CATATAN MEDIS (KANAN)    --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- KOLOM KIRI (8 Kolom): FORM NATIVE FILAMENT & TABEL RIWAYAT KUNJUNGAN --}}
            <div class="lg:col-span-8 space-y-8">

                {{-- Form Native Filament --}}
                <form wire:submit="simpan" class="space-y-8">
                    {{ $this->form }}

                    {{-- Bar Aksi Tombol Simpan dengan Spacing Proporsional --}}
                    <div class="fi-section rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-information-circle" class="h-4 w-4 text-primary-500" />
                            <span>Pastikan data tanda vital dan pemeriksaan fisik pasien sudah lengkap dan valid.</span>
                        </div>

                        <x-filament::button type="submit" color="success" size="lg" icon="heroicon-m-check-circle" class="shadow font-bold">
                            Simpan Pemeriksaan Fisik
                        </x-filament::button>
                    </div>
                </form>

                {{-- Tabel Native Filament Hasil Input Kunjungan Ini --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $this->table }}
                </div>
            </div>

            {{-- KOLOM KANAN (4 Kolom): PANEL CATATAN MEDIS SEBELUMNYA --}}
            <div class="lg:col-span-4">
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden sticky top-6">
                    {{-- Header Panel --}}
                    <div class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 px-4 py-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Catatan Medis</h3>
                        </div>
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-emerald-900/30 dark:text-emerald-400">
                            Sebelumnya
                        </span>
                    </div>

                    {{-- Konten List Histori --}}
                    <div class="p-4 overflow-y-auto space-y-3.5" style="max-height: 640px;">
                        @forelse (($this->catatanMedisSebelumnya ?? collect()) as $history)
                            <div class="rounded-xl border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50/70 dark:bg-emerald-950/30 p-4 text-xs leading-relaxed text-gray-800 dark:text-gray-200 shadow-sm space-y-1.5">
                                <div class="flex items-center justify-between border-b border-emerald-200 dark:border-emerald-800/80 pb-2 font-bold text-emerald-900 dark:text-emerald-300">
                                    <span>* Kunjungan - {{ $history->pendaftaran?->no_pendaftaran ?? '-' }}</span>
                                    <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">
                                        {{ $history->waktu_pemeriksaan ? $history->waktu_pemeriksaan->format('d-m-Y H:i') : '' }}
                                    </span>
                                </div>
                                <p>* Keadaan Umum - {{ $history->keadaan_umum ?? 'baik' }}</p>
                                <p>* Tingkat Kesadaran - {{ $history->tingkat_kesadaran ?? 'Sadar Baik / Alert' }}</p>
                                <p>* GCS - {{ $history->gcs_total ?? '15' }}</p>
                                <p>* Sistolik - {{ $history->sistolik }} mmHg</p>
                                <p>* Diastolik - {{ $history->diastolik }} mmHg</p>
                                <p>* Suhu - {{ $history->suhu }} °C</p>
                                <p>* Saturasi O2 - {{ $history->saturasi_o2 }}%</p>
                                <p>* Frekuensi Nadi - {{ $history->frekuensi_nadi }} X/Menit</p>
                                <p>* Frekuensi Nafas - {{ $history->frekuensi_nafas }} X/Menit</p>
                                <p class="pt-1.5 text-[11px] text-gray-500 dark:text-gray-400 border-t border-emerald-200/60 dark:border-emerald-800/60">
                                    * Umur Saat Kunjungan: {{ $pendaftaran->pasien?->tanggal_lahir ? \Carbon\Carbon::parse($pendaftaran->pasien->tanggal_lahir)->diff($history->waktu_pemeriksaan)->format('%y Thn %m Bln') : '-' }}
                                </p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="mx-auto h-8 w-8 text-gray-400 mb-2 opacity-60" />
                                Belum ada riwayat catatan medis dari kunjungan terdahulu untuk pasien ini.
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination Panel Catatan Medis --}}
                    @if ($this->catatanMedisSebelumnya && $this->catatanMedisSebelumnya->hasPages())
                        <div class="border-t border-gray-200 dark:border-gray-800 px-4 py-3">
                            {{ $this->catatanMedisSebelumnya->links('filament::components.pagination.index') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="fi-section rounded-xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-user-group" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tidak Ada Kunjungan Pasien Yang Dipilih</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Silakan terima pasien terlebih dahulu melalui menu Kunjungan Pasien.</p>
            <div class="mt-4">
                <x-filament::button :href="\App\Filament\Pages\KunjunganPasien::getUrl()" tag="a">
                    Buka Kunjungan Pasien
                </x-filament::button>
            </div>
        </div>
    @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
