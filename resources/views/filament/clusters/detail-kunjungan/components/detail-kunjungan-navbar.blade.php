@props([
    'pendaftaran',
    'activeTab' => 'pemeriksaan', // 'pemeriksaan' atau 'cppt'
])

@if ($pendaftaran)
    @php
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
        $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
        $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

        $recordId = $pendaftaran->id;
        $urlPemeriksaan = \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $recordId]);
        $urlCppt = \App\Filament\Clusters\DetailKunjungan\Pages\CpptPasien::getUrl(['record' => $recordId]);
        $urlKunjungan = \App\Filament\Pages\KunjunganPasien::getUrl();

        $statusBadgeClass = match ($pendaftaran->status_pelayanan) {
            \App\Models\Pendaftaran::STATUS_MENUNGGU            => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-700/20 dark:bg-amber-900/30 dark:text-amber-400',
            \App\Models\Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20 dark:bg-blue-900/30 dark:text-blue-400',
            \App\Models\Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20 dark:bg-purple-900/30 dark:text-purple-400',
            \App\Models\Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-700/20 dark:bg-cyan-900/30 dark:text-cyan-400',
            \App\Models\Pendaftaran::STATUS_SELESAI             => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-700/20 dark:bg-emerald-900/30 dark:text-emerald-400',
            \App\Models\Pendaftaran::STATUS_BATAL               => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-700/20 dark:bg-rose-900/30 dark:text-rose-400',
            default                                             => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-700/20 dark:bg-gray-800 dark:text-gray-300',
        };

        $statusLabelText = match ($pendaftaran->status_pelayanan) {
            \App\Models\Pendaftaran::STATUS_MENUNGGU            => 'Menunggu Antrian Perawat',
            \App\Models\Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'Sedang Dilayani Perawat',
            \App\Models\Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'Siap Diperiksa Dokter',
            \App\Models\Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
            \App\Models\Pendaftaran::STATUS_SELESAI             => 'Pelayanan Selesai',
            \App\Models\Pendaftaran::STATUS_BATAL               => 'Pendaftaran Dibatalkan',
            default                                             => $pendaftaran->status_pelayanan,
        };
    @endphp

    {{-- NAVBAR KHUSUS DETAIL KUNJUNGAN (KOTAK SEMPURNA) --}}
    <div class="fi-section sticky top-[4rem] lg:top-[4.25rem] z-30 w-full rounded-2xl bg-white/95 dark:bg-gray-900/95 px-5 py-3 shadow-sm ring-1 ring-gray-950/10 dark:ring-white/10 backdrop-blur-md -mt-1 mb-6 transition-all">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            
            {{-- SISI KIRI: BREADCRUMB / JUDUL PELAYANAN --}}
            <div class="flex items-center gap-2">
            </div>

            {{-- SISI KANAN: RINGKASAN PASIEN & AKSI CEPAT --}}
            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 text-xs">
                {{-- Identitas Ringkas Pasien --}}
                <div class="flex items-center gap-2.5">
                    <span class="font-black text-gray-900 dark:text-white tracking-tight text-sm">
                        [{{ $pendaftaran->pasien?->no_rm ?? '-' }}] {{ $pendaftaran->pasien?->nama ?? '-' }}
                    </span>
                    <span class="inline-flex items-center rounded-xl px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                        {{ $statusLabelText }}
                    </span>
                </div>

            </div>

        </div>
    </div>
@endif
