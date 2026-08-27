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

        $hasTtvPerawat = $pendaftaran->hasPemeriksaanFisikByProfesi(['Perawat', 'Bidan']);
        $hasTtvDokter = $pendaftaran->hasPemeriksaanFisikByProfesi('Dokter');
        $hasCpptPerawat = $pendaftaran->hasCpptByProfesi(['Perawat', 'Bidan']);
        $hasCpptDokter = $pendaftaran->hasCpptByProfesi('Dokter');

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
            \App\Models\Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'Siap Diperiksa Dokter (Asesmen Selesai)',
            \App\Models\Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
            \App\Models\Pendaftaran::STATUS_SELESAI             => 'Pelayanan Selesai',
            \App\Models\Pendaftaran::STATUS_BATAL               => 'Pendaftaran Dibatalkan',
            default                                             => $pendaftaran->status_pelayanan,
        };
    @endphp

    {{-- ========================================================================= --}}
    {{-- HEADER NATIVE FILAMENT: DATA PASIEN (KIRI) & DATA DOKTER/LAYANAN (KANAN)  --}}
    {{-- ========================================================================= --}}
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6 space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- SISI KIRI: DATA PASIEN & DETAIL ESELON --}}
            <div class="lg:col-span-7 space-y-1.5 border-b lg:border-b-0 border-gray-100 dark:border-gray-800 pb-4 lg:pb-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    @if ($detailPasienUrl)
                        <a href="{{ $detailPasienUrl }}" class="text-2xl font-black text-gray-950 dark:text-white tracking-tight hover:text-primary-600 dark:hover:text-primary-400 hover:underline inline-flex items-center gap-1.5" title="Klik untuk melihat Detail Lengkap Rekam Medis Pasien">
                            <span>{{ $pendaftaran->pasien?->no_rm ?? '-' }}</span>
                        </a>
                    @else
                        <span class="text-2xl font-black text-gray-950 dark:text-white tracking-tight">
                            {{ $pendaftaran->pasien?->no_rm ?? '-' }}
                        </span>
                    @endif

                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400">
                        KTP
                    </span>
                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-emerald-900/30 dark:text-emerald-400">
                        {{ $pendaftaran->pasien?->status_pasien ?? 'Hidup' }} / Aktif
                    </span>
                    <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-bold text-purple-700 ring-1 ring-inset ring-purple-700/10 dark:bg-purple-900/30 dark:text-purple-400">
                        Antrian: {{ $pendaftaran->no_antrian ?? '-' }}
                    </span>
                </div>

                <div class="text-base font-bold text-gray-900 dark:text-white">
                    @if ($detailPasienUrl)
                        <a href="{{ $detailPasienUrl }}" class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline inline-flex items-center gap-1.5" title="Klik untuk melihat Detail Lengkap Rekam Medis Pasien">
                            <span>{{ $pendaftaran->pasien?->nama ?? '-' }}</span>
                        </a>
                    @else
                        {{ $pendaftaran->pasien?->nama ?? '-' }}
                    @endif
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
                    {{ $pendaftaran->pasien?->pekerjaan?->deskripsi ?? 'Pegawai Negeri Sipil / ASN (KEMENKES)' }}
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
                    {{ $pendaftaran->no_asuransi ? $pendaftaran->no_asuransi . ' - ' : ($pendaftaran->pasien?->nomor_kartu ? $pendaftaran->pasien->nomor_kartu . ' - ' : '') }}{{ $pendaftaran->penjamin ?? '0 Rupiah' }}
                </div>

                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $pendaftaran->kelas_rawat ?? 'Non Kelas' }}
                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- BAR STATUS ASESMEN PERAWAT & AKSI TERUSKAN KE DOKTER                     --}}
        {{-- ========================================================================= --}}
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3 bg-gray-50/70 dark:bg-gray-800/40 -mx-6 -mb-6 p-4 rounded-b-xl">
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-4 w-4 text-primary-500" />
                    Kelengkapan Asesmen Awal:
                </span>

                {{-- Status TTV --}}
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold {{ $hasTtvPerawat ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    <x-filament::icon :icon="$hasTtvPerawat ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'" class="h-3.5 w-3.5" />
                    TTV Perawat: {{ $hasTtvPerawat ? 'Sudah Diisi' : 'Belum Diisi' }}
                </span>

                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold {{ $hasCpptPerawat ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    <x-filament::icon :icon="$hasCpptPerawat ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'" class="h-3.5 w-3.5" />
                    CPPT Perawat: {{ $hasCpptPerawat ? 'Sudah Diisi' : 'Belum Diisi' }}
                </span>

                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold {{ $hasTtvDokter ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    <x-filament::icon :icon="$hasTtvDokter ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'" class="h-3.5 w-3.5" />
                    TTV Dokter: {{ $hasTtvDokter ? 'Sudah Diisi' : 'Belum Diisi' }}
                </span>

                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold {{ $hasCpptDokter ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    <x-filament::icon :icon="$hasCpptDokter ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'" class="h-3.5 w-3.5" />
                    CPPT Dokter: {{ $hasCpptDokter ? 'Sudah Diisi' : 'Belum Diisi' }}
                </span>
            </div>

        </div>
    </div>
@endif

