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

        /* Reset margin for rich editor inside cards */
        .cppt-card-content p {
            margin-bottom: 0.25rem;
        }

        /* Explicit CPPT Card Backgrounds (Guaranteed to work in Dark & Light Modes) */
        .cppt-card-dokter {
            background-color: #1d4ed8 !important;
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
            border: 1px solid #60a5fa !important;
            color: #ffffff !important;
        }

        .dark .cppt-card-dokter {
            background-color: #1e3a8a !important;
            background: linear-gradient(135deg, #1e3a8a 0%, #172554 100%) !important;
            border: 1px solid #3b82f6 !important;
            color: #ffffff !important;
        }

        .cppt-card-perawat {
            background-color: #059669 !important;
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            border: 1px solid #34d399 !important;
            color: #ffffff !important;
        }

        .dark .cppt-card-perawat {
            background-color: #064e3b !important;
            background: linear-gradient(135deg, #064e3b 0%, #022c22 100%) !important;
            border: 1px solid #10b981 !important;
            color: #ffffff !important;
        }

        .cppt-card-bidan {
            background-color: #7c3aed !important;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
            border: 1px solid #a78bfa !important;
            color: #ffffff !important;
        }

        .dark .cppt-card-bidan {
            background-color: #4c1d95 !important;
            background: linear-gradient(135deg, #4c1d95 0%, #2e1065 100%) !important;
            border: 1px solid #8b5cf6 !important;
            color: #ffffff !important;
        }

        .cppt-card-default {
            background-color: #475569 !important;
            background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;
            border: 1px solid #94a3b8 !important;
            color: #ffffff !important;
        }

        .dark .cppt-card-default {
            background-color: #1e293b !important;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            border: 1px solid #475569 !important;
            color: #ffffff !important;
        }

        .cppt-badge-dokter {
            background-color: rgba(15, 23, 42, 0.65) !important;
            color: #dbeafe !important;
            border: 1px solid rgba(147, 197, 253, 0.6) !important;
        }

        .cppt-badge-perawat {
            background-color: rgba(6, 78, 59, 0.75) !important;
            color: #d1fae5 !important;
            border: 1px solid rgba(110, 231, 183, 0.6) !important;
        }

        .cppt-badge-bidan {
            background-color: rgba(76, 29, 149, 0.75) !important;
            color: #f3e8ff !important;
            border: 1px solid rgba(196, 181, 253, 0.6) !important;
        }

        .cppt-badge-default {
            background-color: rgba(30, 41, 59, 0.75) !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(148, 163, 184, 0.6) !important;
        }
    </style>

    <div
        x-on:trigger-selesaikan-pelayanan.window="$wire.triggerSelesaikanPelayanan()"
        x-on:trigger-batalkan-final.window="$wire.triggerBatalkanFinal()"
        x-on:trigger-batalkan-pendaftaran.window="$wire.triggerBatalkanPendaftaran()"
    >
    @if ($pendaftaran)
        {{-- Unified Patient Header Component --}}
        @include('filament.clusters.detail-kunjungan.components.patient-header', [
            'pendaftaran' => $pendaftaran,
        ])

        {{-- ========================================================================= --}}
        {{-- LAYOUT SPLIT 2 KOLOM: FORMULIR CPPT (KIRI) vs KARTU RIWAYAT CPPT (KANAN)  --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- KOLOM KIRI: FORMULIR INPUT CPPT (7 Kolom) --}}
            <div class="lg:col-span-7 space-y-6">
                <form wire:submit="simpan" class="space-y-4">
                    {{ $this->form }}

                    {{-- Bar Aksi Simpan CPPT --}}
                    <div
                        class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-wrap items-center justify-between gap-4">
                        <span class="text-xs text-red-600 dark:text-red-400 font-semibold italic">
                            Tekan tombol Simpan untuk menyimpan CPPT
                        </span>

                        <x-filament::button type="submit" color="primary" size="md" icon="heroicon-m-document-check"
                            class="shadow font-bold">
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
                                $profesiLower = strtolower($cppt->profesi ?? '');
                                $isDokter = str_contains($profesiLower, 'dokter');
                                $isPerawat = str_contains($profesiLower, 'perawat');
                                $isBidan = str_contains($profesiLower, 'bidan');

                                if ($isDokter) {
                                    $cardClass = 'cppt-card-dokter';
                                    $profesiBadge = 'cppt-badge-dokter';
                                    $profesiLabel = 'Dokter';
                                } elseif ($isPerawat) {
                                    $cardClass = 'cppt-card-perawat';
                                    $profesiBadge = 'cppt-badge-perawat';
                                    $profesiLabel = 'Perawat';
                                } elseif ($isBidan) {
                                    $cardClass = 'cppt-card-bidan';
                                    $profesiBadge = 'cppt-badge-bidan';
                                    $profesiLabel = 'Bidan';
                                } else {
                                    $cardClass = 'cppt-card-default';
                                    $profesiBadge = 'cppt-badge-default';
                                    $profesiLabel = $cppt->profesi ?? 'PPA';
                                }
                            @endphp

                            <div class="rounded-xl {{ $cardClass }} p-5 shadow-md relative transition duration-200">
                                {{-- Header Kartu --}}
                                <div class="flex items-start justify-between border-b border-white/20 pb-3 mb-3">
                                    {{-- Sisi Kiri: Poli & Badge Profesi --}}
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-extrabold tracking-wide text-white">
                                                {{ $cppt->pendaftaran?->poli?->nama ?? 'Poli Umum' }}
                                            </h4>
                                            <span
                                                class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded {{ $profesiBadge }}">
                                                {{ $profesiLabel }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-white/90 mt-0.5">
                                            Metode:
                                            <strong>{{ ($cppt->is_sbar ? 'SBAR ' : '') . ($cppt->is_tbak ? 'TBAK ' : '') . (!$cppt->is_sbar && !$cppt->is_tbak ? 'SOAP' : '') }}</strong>
                                        </div>
                                    </div>

                                    {{-- Sisi Kanan: PPA Info & Tombol Aksi --}}
                                    <div class="text-right text-[11px] text-white space-y-0.5">
                                        <div class="flex items-center justify-end gap-1.5 mb-1">
                                            {{-- Tombol Verifikasi DPJP --}}
                                            @if (!$cppt->is_verified && (auth()->user()?->hasRole('Dokter') || auth()->user()?->hasRole('super_admin')))
                                                <button wire:click="verifikasiCppt({{ $cppt->id }})"
                                                    title="Verifikasi Catatan CPPT"
                                                    class="p-1 rounded bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                                                    <x-filament::icon icon="heroicon-m-check-badge"
                                                        class="h-3.5 w-3.5" />
                                                </button>
                                            @endif

                                            {{-- Tombol Hapus --}}
                                            <button wire:click="hapusCppt({{ $cppt->id }})"
                                                wire:confirm="Hapus catatan CPPT ini?" title="Hapus CPPT"
                                                class="p-1 rounded bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5" />
                                            </button>
                                        </div>

                                        <div class="font-bold text-xs">{{ $cppt->nama_ppa }}</div>
                                        <div class="text-white/90">Sp/Sub: {{ $cppt->profesi }}</div>
                                        <div class="text-white/90">Tgl:
                                            {{ $cppt->tanggal_waktu ? $cppt->tanggal_waktu->format('d-m-Y H:i:s') : '-' }}
                                        </div>
                                        <div class="text-white/80">ID: {{ $cppt->id }}</div>
                                        @php
                                            $isVerified = $cppt->is_verified || in_array($pendaftaran->status_pelayanan, [\App\Models\Pendaftaran::STATUS_FINAL, 'Selesai']);
                                            $verifierName = $cppt->verifiedBy?->nama_lengkap ?? $pendaftaran->dokter?->nama_lengkap ?? 'Dokter DPJP';
                                        @endphp
                                        <div
                                            class="font-semibold {{ $isVerified ? 'text-amber-200' : 'text-white' }}">
                                            Verifikasi:
                                            {{ $isVerified ? 'Sudah Diverifikasi (' . $verifierName . ')' : 'Belum diverifikasi' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isi SOAP Catatan Perkembangan --}}
                                <div class="text-xs text-white space-y-2.5 leading-relaxed cppt-card-content">
                                    @if ($cppt->subjektif)
                                        <div>
                                            <span class="font-black underline block text-[11px] text-white/95">Subyektif
                                                (S)
                                                & Obyektif (O)</span>
                                            <div class="mt-0.5 text-white/95 prose-sm prose-invert">
                                                {!! $cppt->subjektif !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($cppt->assessment)
                                        <div>
                                            <span class="font-black underline block text-[11px] text-white/95">Assesment
                                                (A)</span>
                                            <div class="mt-0.5 text-white/95 prose-sm prose-invert">
                                                {!! $cppt->assessment !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($cppt->planning)
                                        <div>
                                            <span class="font-black underline block text-[11px] text-white/95">Planning
                                                (P) & Instruksi</span>
                                            <div class="mt-0.5 text-white/95 prose-sm prose-invert">
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
                    <div
                        class="fi-section rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <x-filament::icon icon="heroicon-o-document-text"
                            class="mx-auto h-10 w-10 text-gray-400 mb-2" />
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat CPPT</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gunakan formulir di sebelah kiri untuk
                            menambahkan catatan perkembangan pasien terintegrasi.</p>
                    </div>
                @endif
            </div>

        </div>
    @else
        <div
            class="fi-section rounded-xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-user-group" class="mx-auto h-12 w-12 text-gray-400 mb-3" />
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tidak Ada Kunjungan Pasien Yang Dipilih</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Silakan terima pasien terlebih dahulu melalui menu
                Kunjungan Pasien.</p>
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
