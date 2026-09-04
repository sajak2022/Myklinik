@props([
    'pendaftaran',
])

@if ($pendaftaran)
    @php
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
        $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
        $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

        $detailPasienUrl = $pendaftaran->pasien ? \App\Filament\Resources\Pasiens\PasienResource::getUrl('view', ['record' => $pendaftaran->pasien->id]) : null;
        $editPasienUrl = $pendaftaran->pasien ? \App\Filament\Resources\Pasiens\PasienResource::getUrl('edit', ['record' => $pendaftaran->pasien->id]) : null;

        $statusBadgeClass = match ($pendaftaran->status_pelayanan) {
            \App\Models\Pendaftaran::STATUS_MENUNGGU            => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-700/20 dark:bg-amber-900/30 dark:text-amber-400',
            \App\Models\Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20 dark:bg-blue-900/30 dark:text-blue-400',
            \App\Models\Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20 dark:bg-purple-900/30 dark:text-purple-400',
            \App\Models\Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-700/20 dark:bg-cyan-900/30 dark:text-cyan-400',
            \App\Models\Pendaftaran::STATUS_FINAL, 'Selesai'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-700/20 dark:bg-emerald-900/30 dark:text-emerald-400',
            \App\Models\Pendaftaran::STATUS_BATAL               => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-700/20 dark:bg-rose-900/30 dark:text-rose-400',
            default                                             => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-700/20 dark:bg-gray-800 dark:text-gray-300',
        };

        $statusLabelText = match ($pendaftaran->status_pelayanan) {
            \App\Models\Pendaftaran::STATUS_MENUNGGU            => 'Menunggu Antrian Perawat',
            \App\Models\Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'Sedang Dilayani Perawat',
            \App\Models\Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'Siap Diperiksa Dokter (Asesmen Selesai)',
            \App\Models\Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
            \App\Models\Pendaftaran::STATUS_FINAL, 'Selesai'    => 'Final',
            \App\Models\Pendaftaran::STATUS_BATAL               => 'Pendaftaran Dibatalkan',
            default                                             => $pendaftaran->status_pelayanan,
        };
    @endphp

    {{-- ========================================================================= --}}
    {{-- HEADER NATIVE FILAMENT: DATA PASIEN (KIRI) & DATA DOKTER/LAYANAN (KANAN)  --}}
    {{-- ========================================================================= --}}
    <div class="fi-section rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6 space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- SISI KIRI: DATA PASIEN & DETAIL ESELON --}}
            <div class="lg:col-span-7 space-y-1.5 border-b lg:border-b-0 border-gray-100 dark:border-gray-800 pb-4 lg:pb-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-2xl font-black text-gray-950 dark:text-white tracking-tight">
                        {{ $pendaftaran->pasien?->no_rm ?? '-' }}
                    </span>

                    {{-- Icon Aksi Pasien: Detail Pasien (Semua User) & Edit Pasien (Khusus Admin, Bukan Dokter/Perawat) --}}
                    <div class="inline-flex items-center gap-1.5">
                        @if ($detailPasienUrl)
                            <a
                                href="{{ $detailPasienUrl }}"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 shadow-sm transition-all"
                                title="Lihat Detail Rekam Medis Pasien"
                                aria-label="Lihat Detail Rekam Medis Pasien"
                            >
                                <x-heroicon-m-eye class="w-4 h-4" />
                            </a>
                        @endif

                        @if ($editPasienUrl && $isAdmin && !$isDokter && !$isPerawat)
                            <a
                                href="{{ $editPasienUrl }}"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 shadow-sm transition-all"
                                title="Edit Data Pasien"
                                aria-label="Edit Data Pasien"
                            >
                                <x-heroicon-m-pencil-square class="w-4 h-4" />
                            </a>
                        @endif
                    </div>
                </div>

                <div class="text-base font-bold text-gray-900 dark:text-white">
                    {{ $pendaftaran->pasien?->nama ?? '-' }}
                </div>

                <div class="text-xs text-gray-600 dark:text-gray-300">
                    <span class="font-medium">{{ strtoupper($pendaftaran->pasien?->tempatLahir?->name ?? 'BANDUNG') }}, </span>
                    <span>{{ $pendaftaran->pasien?->tanggal_lahir ? \Carbon\Carbon::parse($pendaftaran->pasien->tanggal_lahir)->translatedFormat('d F Y') : '-' }} / </span>
                    <strong class="text-gray-900 dark:text-white">{{ $pendaftaran->pasien?->tanggal_lahir ? \Carbon\Carbon::parse($pendaftaran->pasien->tanggal_lahir)->diff(now())->format('%y Thn %m Bln %d Hr') : '-' }}</strong>
                </div>

                <div class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2">
                    <span>
                        @if(strtolower($pendaftaran->pasien?->jenis_kelamin ?? '') === 'perempuan')
                            <strong class="text-rose-600 dark:text-rose-400">♀ Perempuan</strong>
                        @else
                            <strong class="text-blue-600 dark:text-blue-400">♂ Laki-Laki</strong>
                        @endif
                    </span>
                    <span>/</span>
                    <span>Status: <strong>{{ $pendaftaran->pasien?->status_pasien ?? 'Hidup' }} / Aktif</strong></span>
                </div>

                {{-- Status Kepegawaian & Kategori Pasien --}}
                <div class="text-xs font-semibold text-blue-700 dark:text-blue-400">
                    {{ $pendaftaran->pasien?->pekerjaan ?? 'Pegawai Negeri Sipil / ASN (KEMENKES)' }}
                </div>

                {{-- Eselon 1 & Eselon 2 --}}
                <div class="text-xs text-gray-700 dark:text-gray-300 space-y-0.5 pt-0.5">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Eselon 1 :</span>
                        <strong>{{ $pendaftaran->pasien?->unitEksternal?->nama ?? 'Direktorat Jenderal Farmasi dan Alat Kesehatan' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Eselon 2 :</span>
                        <strong>{{ $pendaftaran->pasien?->subUnitEksternal?->nama ?? 'Sekretariat Direktorat Jenderal Farmasi dan Alat Kesehatan' }}</strong>
                    </div>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 pt-0.5">
                    <span class="text-gray-400 dark:text-gray-500">Alamat:</span> {{ $pendaftaran->pasien?->alamat ?? 'Alamat belum dilengkapi' }}
                </div>
            </div>

            {{-- SISI KANAN: DATA DOKTER PEMERIKSA & PELAYANAN (RATA KANAN) --}}
            <div class="lg:col-span-5 space-y-1 text-xs lg:text-right">
                <div class="text-base font-black text-emerald-600 dark:text-emerald-400">
                    DPJP: {{ $pendaftaran->dokter?->nama_lengkap ?? 'dr. Khairunnisa' }}
                </div>

                <div class="text-sm font-black text-blue-600 dark:text-blue-400">
                    {{ $pendaftaran->poli?->nama ?? 'Poli Umum' }}
                </div>

                <div class="text-gray-500 dark:text-gray-400 mt-1">
                    Masuk: <strong>{{ $pendaftaran->created_at ? $pendaftaran->created_at->translatedFormat('l, d F Y \P\u\k\u\l H:i:s') : '-' }}</strong>
                </div>

                <div class="text-gray-500 dark:text-gray-400">
                    Keluar: <strong>{{ $pendaftaran->status_pelayanan === \App\Models\Pendaftaran::STATUS_SELESAI ? ($pendaftaran->updated_at ? $pendaftaran->updated_at->translatedFormat('l, d F Y \P\u\k\u\l H:i:s') : '-') : '-' }}</strong>
                </div>

                <div class="mt-1">
                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                        Status: {{ $statusLabelText }}
                    </span>
                </div>

                <div class="text-xs font-black text-gray-900 dark:text-white mt-1.5">
                    {{ $pendaftaran->pasien?->nomor_kartu ? $pendaftaran->pasien->nomor_kartu . ' - ' : '' }}{{ $pendaftaran->pasien?->jenis_kartu ?? 'KTP' }}
                </div>
            </div>

        </div>
    </div>
@endif

