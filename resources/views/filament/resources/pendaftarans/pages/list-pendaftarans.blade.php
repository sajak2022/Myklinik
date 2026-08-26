<x-filament-panels::page>
    <style>
        /* Khusus halaman Pengunjung: Posisikan tombol aksi Terima diatas & Batal dibawah */
        .fi-ta-actions-cell,
        .fi-ta-actions-cell > div,
        .fi-ta-actions {
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            justify-content: center !important;
            gap: 6px !important;
            min-width: 84px !important;
        }

        .fi-ta-actions-cell .fi-btn,
        .fi-ta-actions-cell a,
        .fi-ta-actions-cell button,
        .fi-ta-actions .fi-btn,
        .fi-ta-actions a,
        .fi-ta-actions button {
            width: 100% !important;
            min-width: 84px !important;
            text-align: center !important;
            justify-content: center !important;
        }
    </style>

    {{ $this->table }}
</x-filament-panels::page>

