@php
    $record = $getRecord();
    $status = $record->status_pelayanan;
    $isFinished = in_array($status, [
        \App\Models\Pendaftaran::STATUS_FINAL,
        'Final',
        'Selesai',
        'Pelayanan Selesai',
        \App\Models\Pendaftaran::STATUS_BATAL,
        'Batal',
        'Pendaftaran Dibatalkan',
    ]);
@endphp

<div style="display: flex; align-items: center; justify-content: center; width: 100%; text-align: center; padding: 4px 0;">
    @if (! $isFinished && ! empty($record->no_antrian))
        <div style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; background-color: var(--primary-600, #2563eb); color: #ffffff; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; line-height: 1; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35); text-align: center; margin: 0 auto; user-select: none;">
            {{ $record->no_antrian }}
        </div>
    @else
        <span class="text-gray-400 dark:text-gray-600 font-bold text-sm">-</span>
    @endif
</div>
