<?php

namespace App\Filament\Resources\Pendaftarans\Tables;

use App\Models\Pendaftaran;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendaftaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status_pelayanan', 'Menunggu')->with(['pasien', 'poli', 'dokter']))
            ->searchPlaceholder('Cari No. RM / Nama Pasien / No. Registrasi...')
            ->searchDebounce('400ms')
            ->columns([
                ViewColumn::make('kunjungan')
                    ->label('Pengunjung')
                    ->view('filament.tables.columns.kunjungan-card')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('no_pendaftaran', 'like', "%{$search}%")
                                ->orWhere('no_antrian', 'like', "%{$search}%")
                                ->orWhere('no_asuransi', 'like', "%{$search}%")
                                ->orWhereHas('pasien', function ($sq) use ($search) {
                                    $sq->where('nama', 'like', "%{$search}%")
                                        ->orWhere('no_rm', 'like', "%{$search}%")
                                        ->orWhere('nama_panggilan', 'like', "%{$search}%");
                                })
                                ->orWhereHas('poli', function ($sq) use ($search) {
                                    $sq->where('nama', 'like', "%{$search}%");
                                })
                                ->orWhereHas('dokter', function ($sq) use ($search) {
                                    $sq->where('nama_lengkap', 'like', "%{$search}%");
                                });
                        });
                    }),

                ViewColumn::make('no_antrian')
                    ->label('Antrian')
                    ->view('filament.tables.columns.antrian-box')
                    ->alignment(Alignment::Center),
            ])
            ->defaultSort('tanggal_pendaftaran', 'desc')
            ->filtersLayout(FiltersLayout::Dropdown)
            ->recordUrl(null)
            ->filters([
                // Filter Periode Tanggal
                SelectFilter::make('periode')
                    ->label('Periode Waktu')
                    ->placeholder('Hari Ini')
                    ->options([
                        'minggu_ini' => 'Minggu Ini',
                        'bulan_ini'  => 'Bulan Ini',
                        'tahun_ini'  => 'Tahun Ini',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'minggu_ini' => $query->whereBetween('tanggal_pendaftaran', [now()->startOfWeek(), now()->endOfWeek()]),
                            'bulan_ini'  => $query->whereMonth('tanggal_pendaftaran', now()->month)->whereYear('tanggal_pendaftaran', now()->year),
                            'tahun_ini'  => $query->whereYear('tanggal_pendaftaran', now()->year),
                            default      => $query->whereDate('tanggal_pendaftaran', today()),
                        };
                    }),
            ])
            ->actions([
                // 1. Tombol Terima (Atas) - Saat status Menunggu
                Action::make('terima')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-check-circle')
                    ->visible(fn (Pendaftaran $record): bool => $record->status_pelayanan === 'Menunggu')
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => 'Sedang Diperiksa']);

                        \Filament\Notifications\Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah diterima dan masuk ke ruang pemeriksaan.")
                            ->success()
                            ->send();

                        return redirect()->to(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 2. Tombol Batal (Bawah) - Saat status Menunggu
                Action::make('batal')
                    ->label('Batal')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kunjungan Pengunjung?')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pendaftaran pengunjung ini?')
                    ->visible(fn (Pendaftaran $record): bool => $record->status_pelayanan === 'Menunggu')
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => 'Batal']);

                        \Filament\Notifications\Notification::make()
                            ->title('Pendaftaran Dibatalkan')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
