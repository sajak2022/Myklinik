<x-filament-panels::page>
    <style>
        /* Khusus Layar Desktop (>= 1024px): Header & Menu Samping Tetap Menempel di Kiri */
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

        /* Pada Layar Mobile (< 1024px): Layout Natural Responsif Tanpa Menimpa Sidebar */
        @media (max-width: 1023.98px) {
            .fi-page-has-sub-navigation .fi-header {
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                z-index: auto !important;
            }

            .fi-page-sub-navigation-sidebar-ctn {
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                z-index: auto !important;
                max-height: none !important;
                overflow-y: visible !important;
            }
        }

        /* Reset margin for rich editor inside cards */
        .cppt-card-content p {
            margin-bottom: 0.25rem;
        }
    </style>

    @if ($pendaftaran)
        {{-- Unified Patient Header Component --}}
        @include('filament.clusters.detail-kunjungan.components.patient-header', ['pendaftaran' => $pendaftaran])

        {{-- ========================================================================= --}}
        {{-- LAYOUT SPLIT 2 KOLOM: FORMULIR CPPT (KIRI) vs KARTU RIWAYAT CPPT (KANAN)  --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- KOLOM KIRI: FORMULIR INPUT CPPT (7 Kolom) --}}
            <div class="lg:col-span-7 space-y-6">
                <form wire:submit="simpan" class="space-y-4">
                    {{ $this->form }}

                    {{-- Bar Aksi Simpan CPPT --}}
                    <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-wrap items-center justify-between gap-4">
                        <span class="text-xs text-red-600 dark:text-red-400 font-semibold italic">
                            Tekan tombol Simpan untuk menyimpan CPPT
                        </span>

                        <x-filament::button type="submit" color="primary" size="md" icon="heroicon-m-document-check" class="shadow font-bold">
                            Simpan
                        </x-filament::button>
                    </div>
                </form>
            </div>

            {{-- KOLOM KANAN: DAFTAR KARTU RIWAYAT CPPT (5 Kolom) --}}
            <div class="lg:col-span-5 space-y-4">
                @php
                    $riwayat = $this->riwayatCppt;
                @endphp

                @if ($riwayat && $riwayat->count() > 0)
                    <div class="space-y-4 overflow-y-auto pr-1" style="max-height: 850px;">
                        @foreach ($riwayat as $cppt)
                            @php
                                $bgGradient = 'bg-primary-600 dark:bg-primary-800';
                            @endphp

                            <div class="rounded-xl {{ $bgGradient }} text-white p-5 shadow-md relative transition duration-200">
                                {{-- Header Kartu --}}
                                <div class="flex items-start justify-between border-b border-white/20 pb-3 mb-3">
                                    {{-- Sisi Kiri: Poli --}}
                                    <div>
                                        <h4 class="text-base font-extrabold tracking-wide">
                                            {{ $cppt->pendaftaran?->poli?->nama ?? 'Poli Umum' }}
                                        </h4>
                                        <div class="text-[11px] opacity-90 mt-0.5">
                                            Metode: <strong>{{ ($cppt->is_sbar ? 'SBAR ' : '') . ($cppt->is_tbak ? 'TBAK ' : '') . ((!$cppt->is_sbar && !$cppt->is_tbak) ? 'SOAP' : '') }}</strong>
                                        </div>
                                    </div>

                                    {{-- Sisi Kanan: PPA Info & Tombol Aksi --}}
                                    <div class="text-right text-[11px] space-y-0.5">
                                        <div class="flex items-center justify-end gap-1.5 mb-1">
                                            {{-- Tombol Verifikasi DPJP --}}
                                            @if(!$cppt->is_verified && (auth()->user()?->hasRole('Dokter') || auth()->user()?->hasRole('super_admin')))
                                                <button wire:click="verifikasiCppt({{ $cppt->id }})" title="Verifikasi Catatan CPPT" class="p-1 rounded bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                                                    <x-filament::icon icon="heroicon-m-check-badge" class="h-3.5 w-3.5" />
                                                </button>
                                            @endif

                                            {{-- Tombol Hapus --}}
                                            <button wire:click="hapusCppt({{ $cppt->id }})" wire:confirm="Hapus catatan CPPT ini?" title="Hapus CPPT" class="p-1 rounded bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5" />
                                            </button>
                                        </div>

                                        <div class="font-bold text-xs">{{ $cppt->nama_ppa }}</div>
                                        <div class="opacity-90">Sp/Sub: {{ $cppt->profesi }}</div>
                                        <div class="opacity-90">Tgl: {{ $cppt->tanggal_waktu ? $cppt->tanggal_waktu->format('d-m-Y H:i:s') : '-' }}</div>
                                        <div class="opacity-80">ID: {{ $cppt->id }}</div>
                                        <div class="font-semibold {{ $cppt->is_verified ? 'text-amber-200' : 'text-white' }}">
                                            Verifikasi: {{ $cppt->is_verified ? 'Sudah Diverifikasi (' . ($cppt->verifiedBy?->nama_lengkap ?? 'Dokter DPJP') . ')' : 'Belum diverifikasi' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isi SOAP Catatan Perkembangan --}}
                                <div class="text-xs space-y-2.5 leading-relaxed cppt-card-content">
                                    @if ($cppt->subjektif)
                                        <div>
                                            <span class="font-black underline block text-[11px] opacity-95">Subyektif (S) & Obyektif (O)</span>
                                            <div class="mt-0.5 opacity-95 prose-sm prose-invert">
                                                {!! $cppt->subjektif !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($cppt->assessment)
                                        <div>
                                            <span class="font-black underline block text-[11px] opacity-95">Assesment (A)</span>
                                            <div class="mt-0.5 opacity-95 prose-sm prose-invert">
                                                {!! $cppt->assessment !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($cppt->planning)
                                        <div>
                                            <span class="font-black underline block text-[11px] opacity-95">Planning (P) & Instruksi</span>
                                            <div class="mt-0.5 opacity-95 prose-sm prose-invert">
                                                {!! $cppt->planning !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigasi Paginasi Ringkas --}}
                    <div class="pt-2">
                        {{ $riwayat->links() }}
                    </div>
                @else
                    <div class="fi-section rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <x-filament::icon icon="heroicon-o-document-text" class="mx-auto h-10 w-10 text-gray-400 mb-2" />
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat CPPT</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gunakan formulir di sebelah kiri untuk menambahkan catatan perkembangan pasien terintegrasi.</p>
                    </div>
                @endif
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
</x-filament-panels::page>
