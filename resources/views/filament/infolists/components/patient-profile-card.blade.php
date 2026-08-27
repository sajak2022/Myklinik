@php
    $patientRecord = null;
    if (isset($getRecord) && is_callable($getRecord)) {
        $patientRecord = $getRecord();
    } elseif (isset($record)) {
        $patientRecord = $record;
    } elseif (isset($this) && isset($this->record)) {
        $patientRecord = $this->record;
    }

    $pasien = $patientRecord instanceof \App\Models\Pendaftaran ? $patientRecord->pasien : $patientRecord;
@endphp

@if ($pasien)
    @php
        $noRm = $pasien->no_rm ?? '-';
        $nama = trim(collect([$pasien->gelar_depan, $pasien->nama, $pasien->gelar_belakang])->filter()->join(' '));
        
        $jk = strtolower($pasien->jenis_kelamin ?? '');
        $isFemale = str_contains($jk, 'p') || str_contains($jk, 'perempuan');
        $avatarSrc = $isFemale ? asset('profile/women.png') : asset('profile/men.png');

        $tempatLahir = $pasien->tempatLahir?->name ?? '-';
        $tglLahir = $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y') : '-';
        
        $umur = '-';
        if ($pasien->tanggal_lahir) {
            $diff = \Carbon\Carbon::parse($pasien->tanggal_lahir)->diff(now());
            $umur = "{$diff->y}th {$diff->m}bln {$diff->d}hr";
        }

        $agama = $pasien->agama?->deskripsi ?? '-';
        $golDarah = $pasien->golonganDarah?->deskripsi ?? '-';
        $statusKawin = $pasien->statusPerkawinan?->deskripsi ?? '-';
        $pendidikan = $pasien->pendidikan?->deskripsi ?? '-';
        $pekerjaan = $pasien->pekerjaan?->deskripsi ?? '-';
        $suku = $pasien->sukuBangsa?->deskripsi ?? '-';
        $kewarganegaraan = $pasien->country?->name ?? 'Indonesia';
        $alamat = $pasien->alamat ?? '-';
        $rtRw = 'RT ' . ($pasien->rt ?? '-') . ' / RW ' . ($pasien->rw ?? '-') . ' (Pos: ' . ($pasien->kode_pos ?? '-') . ')';
        
        $wilayah = collect([
            $pasien->village?->name,
            $pasien->district?->name,
            $pasien->regency?->name,
            $pasien->province?->name,
        ])->filter()->join(', ') ?: '-';
    @endphp

    <div class="fi-section rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6">
        <div class="flex flex-col md:flex-row items-start gap-6">
            
            {{-- SISI KIRI: KOTAK FOTO PROFIL PASIEN (SINGLE BOX) --}}
            <div class="flex items-center justify-center shrink-0 w-28 h-28 sm:w-32 sm:h-32 p-3 bg-gray-50 dark:bg-gray-800/80 rounded-2xl ring-1 ring-gray-950/10 dark:ring-white/10 shadow-sm self-start overflow-hidden">
                <img
                    src="{{ $avatarSrc }}"
                    alt="Foto Profil Pasien"
                    class="w-full h-full object-contain dark:invert dark:brightness-200 opacity-90 transition-transform hover:scale-105 duration-200"
                />
            </div>

            {{-- SISI KANAN: DATA LENGKAP PASIEN TERORGANISIR --}}
            <div class="flex-1 min-w-0 w-full space-y-4">
                
                {{-- HEADER: NO. RM, STATUS, NAMA LENGKAP & BADGE PENDUKUNG --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider px-2 py-0.5 bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-700/20 dark:bg-primary-950 dark:text-primary-400 rounded-md">
                                No. RM: {{ $noRm }}
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-700/20 dark:bg-emerald-950 dark:text-emerald-400 rounded-md">
                                {{ $pasien->status_pasien ?? 'Hidup' }} / Aktif
                            </span>
                        </div>
                        <h2 class="text-2xl font-black text-gray-950 dark:text-white tracking-tight mt-1">
                            {{ $nama ?: '-' }}
                        </h2>
                        {{-- Jenis Kelamin di Bawah Nama --}}
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold {{ $isFemale ? 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-700/20 dark:bg-rose-950/50 dark:text-rose-400' : 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20 dark:bg-blue-950/50 dark:text-blue-400' }}">
                                {{ $isFemale ? '♀ Perempuan' : '♂ Laki-Laki' }}
                            </span>
                        </div>
                    </div>

                    {{-- STAT BADGES DI KANAN --}}
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <span class="text-gray-400 dark:text-gray-500 font-normal">Gol. Darah:</span>
                            <strong class="text-gray-900 dark:text-white font-bold">{{ $golDarah }}</strong>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <span class="text-gray-400 dark:text-gray-500 font-normal">Status:</span>
                            <strong class="text-gray-900 dark:text-white font-bold">{{ $statusKawin }}</strong>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <span class="text-gray-400 dark:text-gray-500 font-normal">Warga:</span>
                            <strong class="text-gray-900 dark:text-white font-bold">{{ $kewarganegaraan }}</strong>
                        </span>
                    </div>
                </div>

                {{-- GRID 3 KOLOM SEIMBANG DENGAN LABEL DAN NILAI YANG RAPI --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                    
                    {{-- KOLOM 1: DATA KELAHIRAN & PERSONAL --}}
                    <div class="space-y-2.5">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Tempat / Tanggal Lahir
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $tempatLahir }} / {{ $tglLahir }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Umur
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $umur }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Agama
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $agama }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Suku Bangsa
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $suku }}
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM 2: PENDIDIKAN & PEKERJAAN --}}
                    <div class="space-y-2.5">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Pendidikan
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $pendidikan }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Pekerjaan
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $pekerjaan }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Status Perkawinan
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $statusKawin }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Kewarganegaraan
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $kewarganegaraan }}
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM 3: ALAMAT & DOMISILI --}}
                    <div class="space-y-2.5">
                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Alamat
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs leading-relaxed">
                                {{ $alamat }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                RT / RW / Kode Pos
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs">
                                {{ $rtRw }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                Wilayah (Kel / Kec / Kab / Prov)
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white mt-0.5 text-xs leading-relaxed">
                                {{ $wilayah }}
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
@endif
