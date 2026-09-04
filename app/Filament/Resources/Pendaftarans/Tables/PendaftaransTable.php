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
                $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
                $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
                $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

                $pegawai = $user?->pegawai;
                $poliId = $pegawai?->poli_id;

                $query->with(['pasien.tempatLahir', 'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'])
                    ->latest('tanggal_pendaftaran')
                    ->latest('id');

                if ($poliId && ! $isAdmin) {
                    $query->where('poli_id', $poliId);
                }

                // MENU PENGUNJUNG: Hanya menampilkan antrian pengunjung yang BELUM DITERIMA (Menunggu)
                if ($isDokter && ! $isAdmin) {
                    $query->where('status_pelayanan', Pendaftaran::STATUS_MENUNGGU_DOKTER);

                    if ($pegawai?->id) {
                        $query->where(function ($q) use ($pegawai) {
                            $q->where('dokter_id', $pegawai->id)
                              ->orWhereNull('dokter_id');
                        });
                    }
                } elseif ($isPerawat && ! $isAdmin) {
                    $query->where('status_pelayanan', Pendaftaran::STATUS_MENUNGGU);
                } else {
                    $query->whereIn('status_pelayanan', [
                        Pendaftaran::STATUS_MENUNGGU,
                        Pendaftaran::STATUS_MENUNGGU_DOKTER,
                    ]);
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
            ->filters([])
            ->actions([
                // 1. Tombol Terima (Perawat & Dokter)
                Action::make('terima')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-check-circle')
                    ->visible(function (Pendaftaran $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();
                        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
                        $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
                        $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

                        if ($isDokter && ! $isAdmin) {
                            return $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU_DOKTER;
                        }

                        if ($isPerawat && ! $isAdmin) {
                            return $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU;
                        }

                        return in_array($record->status_pelayanan, [
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

                // 2. Tombol Batal
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
            ]);
    }
}
