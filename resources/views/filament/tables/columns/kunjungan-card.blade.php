@php
    $record = $getRecord();
    $pasien = $record->pasien;
    $dokter = $record->dokter;
    $poli = $record->poli;

    $namaDokter = $dokter ? trim("{$dokter->gelar_depan} {$dokter->nama_lengkap}" . ($dokter->gelar_belakang ? ", {$dokter->gelar_belakang}" : '')) : '-';

    $pasienUrl = $pasien ? \App\Filament\Resources\Pasiens\PasienResource::getUrl('view', ['record' => $pasien->id]) : null;

    $statusText = match ($record->status_pelayanan) {
        'Menunggu' => 'Menunggu Antrian',
        'Sedang Diperiksa' => 'Sedang Dilayani',
        'Selesai' => 'Pelayanan Selesai',
        'Batal' => 'Pendaftaran Dibatalkan',
        default => $record->status_pelayanan,
    };

    $statusColor = match ($record->status_pelayanan) {
        'Menunggu' => 'color: #f59e0b; font-weight: 600;',
        'Sedang Diperiksa' => 'color: var(--primary-500, #3b82f6); font-weight: 700;',
        'Selesai' => 'color: #10b981; font-weight: 700;',
        'Batal' => 'color: #ef4444; font-weight: 600;',
        default => 'color: #94a3b8;',
    };
@endphp

<div class="py-2 text-left space-y-1.5 leading-relaxed">
    {{-- Baris 1: No Registrasi - Waktu --}}
    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
        {{ $record->no_pendaftaran }} &bull; {{ $record->tanggal_pendaftaran?->format('d-m-Y H:i:s') ?? '-' }}
    </div>

    {{-- Baris 2: NAMA PASIEN (Ukuran & Ketebalan Font Seragam) --}}
    <div class="text-base font-bold tracking-tight leading-tight flex items-center gap-1.5">
        <span class="text-primary-600 dark:text-primary-400 font-bold">[{{ $pasien->no_rm ?? '00001' }}]</span>
        <span class="text-gray-900 dark:text-white font-bold">{{ $pasien->nama ?? 'Nama Pasien' }}</span>
    </div>

    {{-- Baris 3, 4, 5: Informasi Pelayanan (Tujuan, DPJP, Status) --}}
    <div style="display: grid; grid-template-columns: 48px 10px 1fr; row-gap: 3px; font-size: 12px; line-height: 1.45; align-items: baseline;">
        <span class="text-gray-500 dark:text-gray-400 font-medium">Tujuan</span>
        <span class="text-gray-400 dark:text-gray-500" style="text-align: center;">:</span>
        <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $poli->nama ?? 'Poli Umum' }}</span>

        <span class="text-gray-500 dark:text-gray-400 font-medium">DPJP</span>
        <span class="text-gray-400 dark:text-gray-500" style="text-align: center;">:</span>
        <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ $namaDokter }}</span>

        <span class="text-gray-500 dark:text-gray-400 font-medium">Status</span>
        <span class="text-gray-400 dark:text-gray-500" style="text-align: center;">:</span>
        <span style="{{ $statusColor }}">{{ $statusText }}</span>
    </div>
</div>
