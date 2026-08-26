@props([
    'pendaftaran',
])

@if ($pendaftaran)
    @php
        $detailPasienUrl = $pendaftaran->pasien ? \App\Filament\Resources\Pasiens\PasienResource::getUrl('view', ['record' => $pendaftaran->pasien->id]) : null;
    @endphp

    {{-- ========================================================================= --}}
    {{-- HEADER NATIVE FILAMENT: DATA PASIEN (KIRI) & DATA DOKTER/LAYANAN (KANAN)  --}}
    {{-- ========================================================================= --}}
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-8">
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
                    Keluar: <strong>{{ $pendaftaran->status_pelayanan === 'Selesai' ? ($pendaftaran->updated_at ? $pendaftaran->updated_at->translatedFormat('l, d F Y \P\u\k\u\l H:i:s') : '-') : '-' }}</strong>
                </div>

                <div class="mt-1">
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold {{ $pendaftaran->status_pelayanan === 'Sedang Diperiksa' ? 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-700/10 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                        Status: {{ $pendaftaran->status_pelayanan === 'Sedang Diperiksa' ? 'Pasien Berada di ruangan ini / Sedang Dilayani' : 'Pelayanan Selesai' }}
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
    </div>
@endif
