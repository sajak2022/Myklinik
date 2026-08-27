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
            ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('tanggal_pendaftaran', today())
                ->where('status_pelayanan', '!=', Pendaftaran::STATUS_SELESAI)
                ->with(['pasien', 'poli', 'dokter']))
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
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->actions([
                // 1. Tombol Terima (Perawat) - Saat status Menunggu
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
                            $user->hasRole(['super_admin', 'Admin', 'Perawat', 'Bidan']) ||
                            ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan']))
                        );
                        return $isAuthorized && $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU;
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT]);
                        session(['active_pendaftaran_id' => $record->id]);

                        \Filament\Notifications\Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah diterima untuk pemeriksaan awal oleh perawat.")
                            ->info()
                            ->send();

                        return redirect()->to(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 2. Tombol Periksa Dokter - Saat status Sedang Diperiksa
                Action::make('periksa')
                    ->label('Periksa')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, [Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT, Pendaftaran::STATUS_SEDANG_DIPERIKSA]))
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),

                // 3. Tombol Selesaikan Pelayanan - Khusus Admin & Dokter
                Action::make('selesaikan')
                    ->label('Selesaikan')
                    ->button()
                    ->color('success')
                    ->size('sm')
                    ->icon('heroicon-m-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Pelayanan Pasien?')
                    ->modalDescription('Apakah Anda yakin ingin menyelesaikan proses pelayanan/pemeriksaan untuk pasien ini?')
                    ->modalSubmitActionLabel('Ya, Selesaikan')
                    ->visible(function (Pendaftaran $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();
                        $isAuthorized = $user && (
                            $user->hasRole(['super_admin', 'Admin', 'Dokter']) ||
                            ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter']))
                        );

                        return $isAuthorized && $record->status_pelayanan === Pendaftaran::STATUS_SEDANG_DIPERIKSA;
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_SELESAI]);
                        session()->forget('active_pendaftaran_id');

                        \Filament\Notifications\Notification::make()
                            ->title('Pelayanan Selesai')
                            ->body("Pelayanan untuk pasien {$record->pasien?->nama} telah berhasil diselesaikan.")
                            ->success()
                            ->send();
                    }),

                // 4. Tombol Batal - Saat status Menunggu atau Pemeriksaan Perawat
                Action::make('batal')
                    ->label('Batal')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kunjungan Pengunjung?')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pendaftaran pengunjung ini?')
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, [Pendaftaran::STATUS_MENUNGGU, Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT]))
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_BATAL]);
                        session()->forget('active_pendaftaran_id');

                        \Filament\Notifications\Notification::make()
                            ->title('Pendaftaran Dibatalkan')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
