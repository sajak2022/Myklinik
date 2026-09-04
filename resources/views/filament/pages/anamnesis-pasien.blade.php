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

        /* Styling tab navigasi form yang rapi */
        .fi-tabs-item {
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
        }
    </style>

    <div
        x-on:trigger-selesaikan-pelayanan.window="$wire.triggerSelesaikanPelayanan()"
        x-on:trigger-batalkan-final.window="$wire.triggerBatalkanFinal()"
        x-on:trigger-batalkan-pendaftaran.window="$wire.triggerBatalkanPendaftaran()"
        x-on:trigger-teruskan-ke-dokter.window="$wire.triggerTeruskanKeDokter()"
        x-data="{
            activeTab: 'Keluhan Utama',
            pageIndex: 0,
            totalPages: {{ $this->riwayatSebelumnya->count() }},
            init() {
                this.setupTabListeners();
                const observer = new MutationObserver(() => this.setupTabListeners());
                observer.observe(this.$el, { childList: true, subtree: true });
            },
            setupTabListeners() {
                const tabs = this.$el.querySelectorAll('[role=tab], .fi-tabs-item');
                tabs.forEach(tab => {
                    if (!tab.dataset.boundTab) {
                        tab.dataset.boundTab = 'true';
                        tab.addEventListener('click', () => {
                            this.activeTab = tab.textContent.trim();
                        });
                    }
                });
            }
        }"
    >
    @if ($pendaftaran)
        {{-- Unified Patient Header Component --}}
        @include('filament.clusters.detail-kunjungan.components.patient-header', ['pendaftaran' => $pendaftaran])

        {{-- ========================================================================= --}}
        {{-- LAYOUT SPLIT 2 KOLOM: FORM ANAMNESIS (KIRI) vs CATATAN MEDIS (KANAN)      --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- KOLOM KIRI (7 Kolom): FORM NATIVE FILAMENT --}}
            <div class="lg:col-span-7 space-y-6">

                <form wire:submit="simpan" class="space-y-6">
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        {{ $this->form }}
                    </div>

                    {{-- Bar Aksi Tombol Simpan --}}
                    <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-information-circle" class="h-4 w-4 text-primary-500" />
                            <span>Pastikan data anamnesis pasien sudah lengkap sebelum disimpan.</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-filament::button type="submit" color="success" size="md" icon="heroicon-m-check-circle" class="shadow font-bold">
                                {{ $editingAnamnesisId ? 'Perbarui Anamnesis' : 'Simpan Anamnesis' }}
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- KOLOM KANAN (5 Kolom): PANEL CATATAN MEDIS PERSIS SEPERTI GAMBAR REFERENSI --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="rounded-lg bg-white dark:bg-gray-900 shadow-sm overflow-hidden border border-gray-200 dark:border-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10">

                    {{-- Header Abu-Abu Gelap --}}
                    <div style="background-color: #374151; color: #ffffff;" class="px-3 py-2 flex items-center justify-between border-b border-gray-600 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-xs font-semibold tracking-wide" style="color: #ffffff;">
                            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4" style="color: #ffffff;" />
                            <span>Catatan Medis</span>
                        </div>
                    </div>

                    {{-- Konten Catatan Medis Sesuai Tab Aktif --}}
                    <div class="p-4 bg-white dark:bg-gray-900 min-h-[480px] flex flex-col justify-between">
                        @if ($this->riwayatSebelumnya->isEmpty())
                            <div></div>

                            {{-- Footer Saat Tidak Ada Data --}}
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 select-none">
                                <div class="flex items-center gap-1">
                                    <button type="button" disabled class="p-1 rounded-md opacity-30 cursor-not-allowed text-gray-400">
                                        <x-filament::icon icon="heroicon-m-chevron-double-left" class="h-4 w-4" />
                                    </button>
                                    <button type="button" disabled class="p-1 rounded-md opacity-30 cursor-not-allowed text-gray-400">
                                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4" />
                                    </button>
                                    <span class="px-1.5 py-0.5 text-xs flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-300">
                                        <span>Hal.</span>
                                        <input type="text" value="0" readonly class="w-8 text-center rounded-md border-0 py-0.5 px-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 dark:text-white" />
                                        <span>dari</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">0</span>
                                    </span>
                                    <button type="button" disabled class="p-1 rounded-md opacity-30 cursor-not-allowed text-gray-400">
                                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                                    </button>
                                    <button type="button" disabled class="p-1 rounded-md opacity-30 cursor-not-allowed text-gray-400">
                                        <x-filament::icon icon="heroicon-m-chevron-double-right" class="h-4 w-4" />
                                    </button>
                                    <button type="button" wire:click="$refresh" class="p-1 ml-1 rounded-md text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Refresh">
                                        <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Data tidak ditemukan
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($this->riwayatSebelumnya as $idx => $item)
                                    @php
                                        $anamLama = $item->anamnesisRecords->first();
                                        // Hanya tampilkan nama jika perawat/petugas yang mengisi anamnesis
                                        $petugasNama = $anamLama?->pegawai?->nama_lengkap ?? ($anamLama?->user?->pegawai?->nama_lengkap ?? ($anamLama?->user?->name ?? null));
                                    @endphp
                                    <div
                                        x-show="pageIndex === {{ $idx }}"
                                        class="space-y-3 text-xs text-gray-800 dark:text-gray-200"
                                    >
                                        {{-- Info Poli, Tanggal, dan Petugas Pengisi --}}
                                        <div class="space-y-0.5 pb-2 border-b border-gray-100 dark:border-gray-800">
                                            <div class="font-bold text-gray-900 dark:text-white text-xs">
                                                {{ $item->poli?->nama ?? 'Poli Umum' }}
                                            </div>
                                            <div class="text-[11px] text-gray-600 dark:text-gray-400">
                                                {{ $item->tanggal_pendaftaran?->format('d-m-Y H:i:s') ?? '-' }}@if ($petugasNama) | {{ $petugasNama }}@endif
                                            </div>
                                        </div>

                                        {{-- Tampilan Lengkap Catatan Medis & Anamnesis Kunjungan Ini --}}
                                        <div class="space-y-2.5 text-xs leading-relaxed">

                                            {{-- 1. Keluhan Utama --}}
                                            @if ($anamLama?->keluhan_utama || $item->catatan)
                                                <div>
                                                    <span class="font-bold text-gray-900 dark:text-white">Keluhan Utama:</span>
                                                    <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ $anamLama?->keluhan_utama ?: $item->catatan }}</p>
                                                </div>
                                            @endif

                                            {{-- 2. Anamnesis Diperoleh --}}
                                            @if ($anamLama && $anamLama->sumber_anamnesis)
                                                <div>
                                                    <span class="font-bold text-gray-900 dark:text-white">Anamnesis Diperoleh:</span>
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $anamLama->sumber_anamnesis }}</span>
                                                    @if ($anamLama->nama_sumber_informasi)
                                                        <span class="text-gray-500 dark:text-gray-400">({{ $anamLama->nama_sumber_informasi }})</span>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- 3. Riwayat Penyakit Sekarang --}}
                                            @if ($anamLama?->riwayat_penyakit_sekarang)
                                                <div>
                                                    <span class="font-bold text-gray-900 dark:text-white">Riwayat Penyakit Sekarang:</span>
                                                    <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ $anamLama->riwayat_penyakit_sekarang }}</p>
                                                </div>
                                            @endif

                                            {{-- 4. Riwayat Penyakit Dahulu / Alergi / Pengobatan / Keluarga --}}
                                            @if ($anamLama && ($anamLama->riwayat_penyakit_dahulu || $anamLama->riwayat_alergi || $anamLama->riwayat_pengobatan || $anamLama->riwayat_penyakit_keluarga))
                                                <div class="space-y-0.5">
                                                    <span class="font-bold text-gray-900 dark:text-white">Riwayat Penyakit & Pengobatan:</span>
                                                    @if ($anamLama->riwayat_penyakit_dahulu)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Riw. Dahulu: {{ $anamLama->riwayat_penyakit_dahulu }}</p>
                                                    @endif
                                                    @if ($anamLama->riwayat_alergi)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Alergi: {{ $anamLama->riwayat_alergi }}</p>
                                                    @endif
                                                    @if ($anamLama->riwayat_pengobatan)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Pengobatan: {{ $anamLama->riwayat_pengobatan }}</p>
                                                    @endif
                                                    @if ($anamLama->riwayat_penyakit_keluarga)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Keluarga: {{ $anamLama->riwayat_penyakit_keluarga }}</p>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- 5. Status Fungsional --}}
                                            @if ($anamLama && (!empty($anamLama->alat_bantu_array) || $anamLama->alat_bantu_lainnya || $anamLama->cacat_tubuh_pilihan))
                                                <div class="space-y-0.5">
                                                    <span class="font-bold text-gray-900 dark:text-white">Status Fungsional:</span>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Alat Bantu: {{ !empty($anamLama->alat_bantu_array) ? implode(', ', $anamLama->alat_bantu_array) : ($anamLama->alat_bantu_lainnya ?: 'Tidak') }}</p>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Cacat Tubuh: {{ $anamLama->cacat_tubuh_pilihan === 'Ada' ? ($anamLama->cacat_tubuh_keterangan ?: 'Ada') : 'Tidak' }}</p>
                                                </div>
                                            @endif

                                            {{-- 6. Hubungan Status Psikososial --}}
                                            @if ($anamLama?->nilai_kepercayaan_agama)
                                                <div>
                                                    <span class="font-bold text-gray-900 dark:text-white">Nilai Kepercayaan/Agama:</span>
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $anamLama->nilai_kepercayaan_agama }}</span>
                                                </div>
                                            @endif

                                            {{-- 7. Edukasi --}}
                                            @if ($anamLama && ($anamLama->kesediaan_menerima_edukasi || $anamLama->ada_hambatan_edukasi || $anamLama->butuh_penerjemah || !empty($anamLama->kebutuhan_edukasi)))
                                                <div class="space-y-0.5">
                                                    <span class="font-bold text-gray-900 dark:text-white">Edukasi:</span>
                                                    @if ($anamLama->kesediaan_menerima_edukasi)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Kesediaan Menerima Informasi: {{ $anamLama->kesediaan_menerima_edukasi }}</p>
                                                    @endif
                                                    @if ($anamLama->ada_hambatan_edukasi)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Hambatan: {{ $anamLama->ada_hambatan_edukasi }}</p>
                                                    @endif
                                                    @if ($anamLama->butuh_penerjemah)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Penerjemah: {{ $anamLama->butuh_penerjemah }}</p>
                                                    @endif
                                                    @if (!empty($anamLama->kebutuhan_edukasi))
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Kebutuhan: {{ implode(', ', $anamLama->kebutuhan_edukasi) }}</p>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- 8. Skrining Gizi Awal --}}
                                            @if ($anamLama && $anamLama->total_skor_gizi !== null)
                                                <div class="space-y-0.5">
                                                    <span class="font-bold text-gray-900 dark:text-white">Skrining Gizi:</span>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Penurunan BB: {{ $anamLama->penurunan_bb ?? '-' }}</p>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Asupan Makan: {{ $anamLama->asupan_makan_berkurang ?? '-' }}</p>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Total Skor: {{ $anamLama->total_skor_gizi }} ({{ $anamLama->kategori_gizi ?? 'Risiko Rendah' }})</p>
                                                </div>
                                            @endif

                                            {{-- 9. Skrining Batuk / TB --}}
                                            @if ($anamLama && !empty($anamLama->skrining_batuk))
                                                <div class="space-y-0.5">
                                                    <span class="font-bold text-gray-900 dark:text-white">Skrining TB / Batuk:</span>
                                                    <p class="text-gray-700 dark:text-gray-300">&bull; Gejala: {{ implode(', ', $anamLama->skrining_batuk) }}</p>
                                                    @if ($anamLama->skrining_batuk_keterangan)
                                                        <p class="text-gray-700 dark:text-gray-300">&bull; Catatan: {{ $anamLama->skrining_batuk_keterangan }}</p>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Catatan CPPT Terkait (Jika Ada) --}}
                                            @if ($item->cpptRecords && $item->cpptRecords->isNotEmpty())
                                                @php
                                                    $validCppt = $item->cpptRecords->filter(fn($c) => !empty(trim(strip_tags($c->subjektif ?? ''))) || !empty(trim(strip_tags($c->objektif ?? ''))));
                                                @endphp
                                                @if ($validCppt->isNotEmpty())
                                                    <div class="pt-2 border-t border-gray-100 dark:border-gray-800 space-y-1">
                                                        <span class="font-bold text-gray-900 dark:text-white">Catatan CPPT:</span>
                                                        @foreach ($validCppt as $cppt)
                                                            <div class="bg-gray-50 dark:bg-gray-800/60 p-2 rounded text-[11px] text-gray-700 dark:text-gray-300 space-y-0.5">
                                                                <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                                    {{ $cppt->pegawai?->nama_lengkap ?? $cppt->user?->name ?? 'Petugas' }} ({{ $cppt->waktu_catat?->format('d-m-Y H:i') ?? '' }}):
                                                                </div>
                                                                @if (!empty(trim(strip_tags($cppt->subjektif ?? ''))))
                                                                    <div><strong>S:</strong> {!! strip_tags($cppt->subjektif) !!}</div>
                                                                @endif
                                                                @if (!empty(trim(strip_tags($cppt->objektif ?? ''))))
                                                                    <div><strong>O:</strong> {!! strip_tags($cppt->objektif) !!}</div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endif

                                            {{-- Jika belum ada anamnesis maupun catatan di kunjungan ini --}}
                                            @if (!$anamLama && !$item->catatan && (!isset($validCppt) || $validCppt->isEmpty()))
                                                <div class="py-6 text-center text-xs text-gray-400 dark:text-gray-500 italic">
                                                    Tidak ada catatan medis atau anamnesis pada kunjungan ini.
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Footer Pagination Bawah: << < Hal 1 dari X > >> --}}
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 select-none">
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click="pageIndex = 0"
                                        :disabled="pageIndex === 0"
                                        class="p-1 rounded-md text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:pointer-events-none transition"
                                        title="Halaman Pertama"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-double-left" class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="pageIndex = Math.max(0, pageIndex - 1)"
                                        :disabled="pageIndex === 0"
                                        class="p-1 rounded-md text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:pointer-events-none transition"
                                        title="Halaman Sebelumnya"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4" />
                                    </button>
                                    <span class="px-1.5 py-0.5 text-xs flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-300">
                                        <span>Hal.</span>
                                        <input
                                            type="text"
                                            :value="pageIndex + 1"
                                            readonly
                                            class="w-8 text-center rounded-md border-0 py-0.5 px-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 dark:text-white"
                                        />
                                        <span>dari</span>
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="totalPages"></span>
                                    </span>
                                    <button
                                        type="button"
                                        @click="pageIndex = Math.min(totalPages - 1, pageIndex + 1)"
                                        :disabled="pageIndex === totalPages - 1"
                                        class="p-1 rounded-md text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:pointer-events-none transition"
                                        title="Halaman Berikutnya"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="pageIndex = totalPages - 1"
                                        :disabled="pageIndex === totalPages - 1"
                                        class="p-1 rounded-md text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-30 disabled:pointer-events-none transition"
                                        title="Halaman Terakhir"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-double-right" class="h-4 w-4" />
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="$refresh"
                                        class="p-1 ml-1 rounded-md text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                        title="Refresh"
                                    >
                                        <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Menampilkan <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="pageIndex + 1"></span> &ndash; <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="totalPages"></span> dari <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="totalPages"></span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 text-center bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="p-4 bg-primary-50 dark:bg-primary-950/50 rounded-full text-primary-500 mb-4">
                <x-filament::icon icon="heroicon-o-user-minus" class="h-10 w-10" />
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tidak Ada Kunjungan Pasien Yang Dipilih</h3>
            <p class="text-xs text-gray-500 max-w-sm mt-1">
                Silakan pilih antrian pasien dari daftar kunjungan terlebih dahulu untuk mengisi data anamnesis.
            </p>
            <div class="mt-6">
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Pages\KunjunganPasien::getUrl() }}"
                    color="primary"
                    icon="heroicon-m-arrow-left"
                >
                    Kembali ke Antrian Kunjungan
                </x-filament::button>
            </div>
        </div>
    @endif
    </div>
</x-filament-panels::page>
