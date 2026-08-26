@php
    $patientRecord = null;
    if (isset($getRecord)) {
        $patientRecord = is_callable($getRecord) ? $getRecord() : $getRecord;
    } elseif (isset($record)) {
        $patientRecord = $record;
    } elseif (isset($this) && isset($this->record)) {
        $patientRecord = $this->record;
    }

    $rawPendaftarans = $patientRecord ? $patientRecord->pendaftarans()
        ->with(['poli', 'dokter'])
        ->latest('tanggal_pendaftaran')
        ->get() : collect();

    $itemsJson = $rawPendaftarans->map(function ($item, $index) {
        $badgeClass = match ($item->status_pelayanan) {
            'Menunggu' => 'badge-menunggu',
            'Sedang Diperiksa' => 'badge-diperiksa',
            'Selesai' => 'badge-selesai',
            'Batal' => 'badge-batal',
            default => 'badge-default',
        };

        $statusLabel = match ($item->status_pelayanan) {
            'Menunggu' => 'Menunggu Antrian',
            'Sedang Diperiksa' => 'Sedang Dilayani',
            'Selesai' => 'Selesai',
            'Batal' => 'Dibatalkan / Tidak Aktif',
            default => $item->status_pelayanan,
        };

        return [
            'index' => $index + 1,
            'id' => $item->id,
            'no_pendaftaran' => $item->no_pendaftaran,
            'tanggal' => $item->tanggal_pendaftaran?->format('d/m/Y H:i:s') ?? '-',
            'poli_nama' => $item->poli?->nama ?? 'Poli Umum',
            'dokter_nama' => $item->dokter?->nama_lengkap ?? '-',
            'penjamin' => $item->penjamin ?? 'Umum / Mandiri',
            'no_asuransi' => $item->no_asuransi ?? '',
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'view_url' => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $item->id]),
        ];
    })->values();
@endphp

<style>
    /* Force Filament Modal container, footer, & buttons to be sharp square */
    .fi-modal-window {
        border-radius: 0 !important;
    }
    .fi-modal-window * {
        border-radius: 0 !important;
    }

    .history-table-container {
        width: 100%;
        margin: 4px 0;
        border-radius: 0 !important;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        display: flex;
        flex-direction: column;
    }
    .dark .history-table-container {
        border-color: #27272a;
        background-color: #18181b;
    }

    .history-scroll-area {
        width: 100%;
        max-height: 420px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
        background-color: inherit;
    }

    .history-table th {
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f9fafb;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
    }
    .dark .history-table th {
        background-color: #18181b;
        color: #a1a1aa;
        border-bottom: 1px solid #27272a;
        border-right: 1px solid #27272a;
    }
    .history-table th:last-child {
        border-right: none;
    }

    .history-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        color: #1f2937;
        vertical-align: middle;
        background-color: inherit;
    }
    .dark .history-table td {
        border-bottom: 1px solid #27272a;
        border-right: 1px solid #27272a;
        color: #f4f4f5;
        background-color: #18181b;
    }
    .history-table td:last-child {
        border-right: none;
    }

    .history-table tbody tr {
        transition: background-color 0.1s ease;
    }
    .history-table tbody tr:hover td {
        background-color: #f9fafb;
    }
    .dark .history-table tbody tr:hover td {
        background-color: #27272a !important;
    }

    .history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .history-reg-no {
        font-weight: 400;
        color: #111827;
    }
    .dark .history-reg-no {
        color: #f4f4f5;
    }

    .history-secondary-text {
        font-size: 11px;
        color: #6b7280;
        margin-top: 1px;
    }
    .dark .history-secondary-text {
        color: #a1a1aa;
    }

    /* Badges (Square / Flat) */
    .history-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 8px;
        border-radius: 0 !important;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .badge-menunggu {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    .dark .badge-menunggu {
        background-color: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }
    .badge-diperiksa {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
    }
    .dark .badge-diperiksa {
        background-color: rgba(56, 189, 248, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.3);
    }
    .badge-selesai {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    .dark .badge-selesai {
        background-color: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(52, 211, 153, 0.3);
    }
    .badge-batal {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .dark .badge-batal {
        background-color: rgba(248, 113, 113, 0.15);
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.3);
    }
    .badge-default {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .dark .badge-default {
        background-color: #27272a;
        color: #d4d4d8;
        border: 1px solid #3f3f46;
    }

    /* Action button (Square / Flat) */
    .history-btn-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 4px 10px;
        background-color: #0284c7;
        color: #ffffff !important;
        border-radius: 0 !important;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #0369a1;
        transition: background-color 0.1s ease;
    }
    .history-btn-view:hover {
        background-color: #0369a1;
    }
    .dark .history-btn-view {
        background-color: #0284c7;
        border-color: #0284c7;
    }
    .dark .history-btn-view:hover {
        background-color: #38bdf8;
    }

    /* Toolbar & Pagination (Square / Flat) */
    .history-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-bottom: 1px solid #e5e7eb;
        background-color: #ffffff;
        gap: 12px;
    }
    .dark .history-toolbar {
        background-color: #18181b;
        border-bottom: 1px solid #27272a;
    }

    .history-search-input {
        width: 100%;
        max-width: 300px;
        padding: 5px 10px 5px 30px;
        font-size: 12px;
        border-radius: 0 !important;
        border: 1px solid #d1d5db;
        background-color: #ffffff;
        color: #1f2937;
        outline: none;
    }
    .dark .history-search-input {
        border-color: #3f3f46;
        background-color: #18181b;
        color: #f4f4f5;
    }
    .history-search-input:focus {
        border-color: #2563eb;
    }

    .history-pagination-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-top: 1px solid #e5e7eb;
        background-color: #ffffff;
        font-size: 12px;
        color: #4b5563;
        flex-wrap: wrap;
        gap: 8px;
    }
    .dark .history-pagination-footer {
        background-color: #18181b;
        border-top: 1px solid #27272a;
        color: #a1a1aa;
    }

    .history-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 26px;
        padding: 0 6px;
        border-radius: 0 !important;
        border: 1px solid #d1d5db;
        background-color: #ffffff;
        color: #374151;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
    }
    .dark .history-page-btn {
        border-color: #3f3f46;
        background-color: #18181b;
        color: #f4f4f5;
    }
    .history-page-btn:hover:not(:disabled) {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }
    .dark .history-page-btn:hover:not(:disabled) {
        background-color: #27272a;
    }
    .history-page-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .history-page-btn.active {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        font-weight: 700;
    }
    .dark .history-page-btn.active {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
    }
</style>

<div
    x-data="{
        allItems: {{ json_encode($itemsJson) }},
        search: '',
        currentPage: 1,
        perPage: 10,

        get filteredItems() {
            if (!this.search.trim()) {
                return this.allItems;
            }
            const q = this.search.toLowerCase();
            return this.allItems.filter(item =>
                (item.no_pendaftaran && item.no_pendaftaran.toLowerCase().includes(q)) ||
                (item.poli_nama && item.poli_nama.toLowerCase().includes(q)) ||
                (item.dokter_nama && item.dokter_nama.toLowerCase().includes(q)) ||
                (item.penjamin && item.penjamin.toLowerCase().includes(q)) ||
                (item.status_label && item.status_label.toLowerCase().includes(q)) ||
                (item.tanggal && item.tanggal.toLowerCase().includes(q))
            );
        },

        get totalPages() {
            if (this.perPage === 'all') return 1;
            return Math.max(1, Math.ceil(this.filteredItems.length / parseInt(this.perPage)));
        },

        get paginatedItems() {
            if (this.perPage === 'all') return this.filteredItems;
            const start = (this.currentPage - 1) * parseInt(this.perPage);
            return this.filteredItems.slice(start, start + parseInt(this.perPage));
        },

        get startIndex() {
            if (this.filteredItems.length === 0) return 0;
            if (this.perPage === 'all') return 1;
            return (this.currentPage - 1) * parseInt(this.perPage) + 1;
        },

        get endIndex() {
            if (this.perPage === 'all') return this.filteredItems.length;
            return Math.min(this.currentPage * parseInt(this.perPage), this.filteredItems.length);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        setPage(page) {
            this.currentPage = page;
        }
    }"
    x-init="$watch('search', () => { currentPage = 1; }); $watch('perPage', () => { currentPage = 1; });"
    class="history-table-container"
>
    <!-- Top Toolbar with Search & PerPage -->
    <div class="history-toolbar">
        <div style="position: relative; display: flex; align-items: center; width: 100%; max-width: 300px;">
            <svg style="position: absolute; left: 8px; width: 14px; height: 14px; color: #9ca3af; pointer-events: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                type="text"
                x-model="search"
                placeholder="Cari No. Pendaftaran / Poli..."
                class="history-search-input"
            />
        </div>

        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px;">
            <span style="opacity: 0.8;">Tampilkan:</span>
            <select
                x-model="perPage"
                class="history-page-btn"
                style="padding: 2px 6px; height: 26px;"
            >
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    <!-- Scrollable Table Body with Sticky Header -->
    <div class="history-scroll-area">
        <table class="history-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 45px;">No</th>
                    <th style="width: 170px;">No. Pendaftaran</th>
                    <th style="width: 165px;">Tanggal</th>
                    <th>Tujuan</th>
                    <th style="width: 210px;">Penjamin</th>
                    <th style="text-align: center; width: 155px;">Status</th>
                    <th style="text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in paginatedItems" :key="item.id">
                    <tr>
                        <td style="text-align: center; font-weight: 500; opacity: 0.7;" x-text="item.index"></td>
                        <td>
                            <span class="history-reg-no" x-text="item.no_pendaftaran"></span>
                        </td>
                        <td style="white-space: nowrap;" x-text="item.tanggal"></td>
                        <td>
                            <div style="font-weight: 600;" x-text="item.poli_nama"></div>
                            <div class="history-secondary-text" x-text="'DPJP: ' + item.dokter_nama"></div>
                        </td>
                        <td>
                            <div style="font-weight: 500;" x-text="item.penjamin"></div>
                            <template x-if="item.no_asuransi">
                                <div class="history-secondary-text" x-text="'No: ' + item.no_asuransi"></div>
                            </template>
                        </td>
                        <td style="text-align: center;">
                            <span
                                class="history-badge"
                                :class="item.badge_class"
                                x-text="item.status_label"
                            ></span>
                        </td>
                        <td style="text-align: center;">
                            <a
                                :href="item.view_url"
                                class="history-btn-view"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 13px; height: 13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                </svg>
                                <span>Lihat</span>
                            </a>
                        </td>
                    </tr>
                </template>

                <template x-if="paginatedItems.length === 0">
                    <tr>
                        <td colspan="7" style="padding: 40px 16px; text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 32px; height: 32px; opacity: 0.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span style="font-weight: 500; font-size: 13px; opacity: 0.8;">Tidak ada data riwayat pendaftaran yang cocok.</span>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="history-pagination-footer">
        <div>
            Menampilkan
            <span style="font-weight: 600;" x-text="startIndex"></span>
            -
            <span style="font-weight: 600;" x-text="endIndex"></span>
            dari
            <span style="font-weight: 600;" x-text="filteredItems.length"></span>
            data
        </div>

        <div style="display: flex; align-items: center; gap: 4px;" x-show="totalPages > 1">
            <button
                type="button"
                @click="prevPage()"
                :disabled="currentPage === 1"
                class="history-page-btn"
                title="Halaman Sebelumnya"
            >
                &lsaquo;
            </button>

            <template x-for="p in totalPages" :key="p">
                <button
                    type="button"
                    @click="setPage(p)"
                    class="history-page-btn"
                    :class="{ 'active': currentPage === p }"
                    x-text="p"
                ></button>
            </template>

            <button
                type="button"
                @click="nextPage()"
                :disabled="currentPage === totalPages"
                class="history-page-btn"
                title="Halaman Berikutnya"
            >
                &rsaquo;
            </button>
        </div>
    </div>
</div>
