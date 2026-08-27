<?php

namespace App\Filament\Resources\Pendaftarans\Tables;

use App\Models\Pendaftaran;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PendaftaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                /** @var User|null $user */
                $user = Auth::user();
                $pegawai = $user?->pegawai;
                $poliId = $pegawai?->poli_id;

                $query->with(['pasien.tempatLahir', 'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'])
                    ->latest('tanggal_pendaftaran')
                    ->latest('id');

                if ($poliId && ! $user?->hasRole('super_admin')) {
                    $query->where('poli_id', $poliId);
                }

                return $query;
            })
            ->searchPlaceholder('Cari No. RM / Nama Pasien / No. Registrasi...')
            ->searchDebounce('400ms')
            ->poll('5s')
            ->recordUrl(null)
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
            ->filters([
                // Filter Periode Tanggal (Default: Hari Ini)
                \Filament\Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode Waktu')
                    ->placeholder('Hari Ini')
                    ->options([
                        'semua'      => 'Semua Periode',
                        'minggu_ini' => 'Minggu Ini',
                        'bulan_ini'  => 'Bulan Ini',
                        'tahun_ini'  => 'Tahun Ini',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'semua'      => $query,
                            'minggu_ini' => $query->whereBetween('tanggal_pendaftaran', [now()->startOfWeek(), now()->endOfWeek()]),
                            'bulan_ini'  => $query->whereMonth('tanggal_pendaftaran', now()->month)->whereYear('tanggal_pendaftaran', now()->year),
                            'tahun_ini'  => $query->whereYear('tanggal_pendaftaran', now()->year),
                            default      => $query->whereDate('tanggal_pendaftaran', today()),
                        };
                    }),

                \Filament\Tables\Filters\SelectFilter::make('status_pelayanan')
                    ->label('Status Pelayanan')
                    ->placeholder('Antrian Aktif / Sedang Dilayani')
                    ->options([
                        'semua'           => 'Semua Status',
                        'sedang_dilayani' => 'Antrian Aktif / Sedang Dilayani',
                        'selesai'         => 'Selesai',
                        'batal'           => 'Batal Kunjungan',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === 'semua') {
                            return $query;
                        }

                        if ($value === 'batal') {
                            return $query->whereIn('status_pelayanan', [Pendaftaran::STATUS_BATAL, 'Batal']);
                        }

                        if ($value === 'selesai') {
                            return $query->whereIn('status_pelayanan', [Pendaftaran::STATUS_FINAL, 'Selesai']);
                        }

                        return $query->whereIn('status_pelayanan', [
                            Pendaftaran::STATUS_MENUNGGU,
                            Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT,
                            Pendaftaran::STATUS_MENUNGGU_DOKTER,
                            Pendaftaran::STATUS_SEDANG_DIPERIKSA,
                        ]);
                    }),
            ])
            ->actions([
                // 1. Tombol Terima (Perawat & Dokter) - Saat status Menunggu atau Menunggu Dokter
                Action::make('terima')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-check-circle')
                    ->visible(function (Pendaftaran $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();
                        $isAuthorized = $user && (
                            $user->hasRole(['super_admin', 'Admin', 'Perawat', 'Bidan', 'Dokter']) ||
                            ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan', 'Dokter']))
                        );
                        return $isAuthorized && in_array($record->status_pelayanan, [
                            Pendaftaran::STATUS_MENUNGGU,
                            Pendaftaran::STATUS_MENUNGGU_DOKTER,
                        ]);
                    })
                    ->action(function (Pendaftaran $record, \Livewire\Component $livewire) {
                        /** @var User|null $user */
                        $user = Auth::user();
                        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
                        $nextStatus = ($isDokter || $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU_DOKTER)
                            ? Pendaftaran::STATUS_SEDANG_DIPERIKSA
                            : Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT;

                        $record->update(['status_pelayanan' => $nextStatus]);
                        session(['active_pendaftaran_id' => $record->id]);

                        \Filament\Notifications\Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah diterima untuk pemeriksaan.")
                            ->info()
                            ->send();

                        $livewire->redirect(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 2. Tombol Batal - Saat status Menunggu, Pemeriksaan Perawat, atau Menunggu Dokter
                Action::make('batal')
                    ->label('Batal')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kunjungan Pengunjung?')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pendaftaran pengunjung ini?')
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, [
                        Pendaftaran::STATUS_MENUNGGU,
                        Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT,
                        Pendaftaran::STATUS_MENUNGGU_DOKTER,
                    ]))
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_BATAL]);
                        session()->forget('active_pendaftaran_id');

                        \Filament\Notifications\Notification::make()
                            ->title('Pendaftaran Dibatalkan')
                            ->warning()
                            ->send();
                    }),

                // 3. Tombol Lihat - Saat sudah diterima (Pemeriksaan Perawat, Sedang Diperiksa, Final/Selesai, atau Batal)
                Action::make('lihat')
                    ->label('Lihat')
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-eye')
                    ->visible(fn (Pendaftaran $record): bool => ! in_array($record->status_pelayanan, [
                        Pendaftaran::STATUS_MENUNGGU,
                        Pendaftaran::STATUS_MENUNGGU_DOKTER,
                    ]))
                    ->action(function (Pendaftaran $record, \Livewire\Component $livewire) {
                        session(['active_pendaftaran_id' => $record->id]);
                        $livewire->redirect(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),
            ]);
    }
}
