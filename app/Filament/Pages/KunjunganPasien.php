<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Pendaftarans\PendaftaranResource;
use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class KunjunganPasien extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::UsersGroup;

    protected static ?string $navigationLabel = 'Kunjungan Pasien';

    protected static ?string $title = 'Kunjungan Pasien';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.kunjungan-pasien';

    public function table(Table $table): Table
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
        $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
        $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

        $pegawai = $user?->pegawai;
        $poliId = $pegawai?->poli_id;

        $query = Pendaftaran::query()
            ->with(['pasien.tempatLahir', 'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'])
            ->latest('tanggal_pendaftaran');

        if ($isDokter && ! $isAdmin) {
            // DOKTER: Hanya menerima pasien setelah perawat selesai mengisi pemeriksaan & CPPT
            $query->untukDokter($poliId, $pegawai?->id);
        } elseif ($isPerawat && ! $isAdmin) {
            // PERAWAT: Menerima pasien dari loket pendaftaran (Menunggu) hingga Selesai untuk poli terkait
            $query->untukPerawat($poliId);
        } else {
            // ADMIN / SUPER ADMIN: Melihat seluruh status kunjungan
            if ($poliId && ! $user->hasRole('super_admin')) {
                $query->where('poli_id', $poliId);
            }
        }

        $statusFilterOptions = $isDokter && ! $isAdmin
            ? [
                Pendaftaran::STATUS_MENUNGGU_DOKTER  => 'Siap Diperiksa Dokter (Pemeriksaan Perawat Selesai)',
                Pendaftaran::STATUS_SEDANG_DIPERIKSA => 'Sedang Diperiksa Dokter',
                Pendaftaran::STATUS_SELESAI          => 'Pelayanan Selesai',
            ]
            : [
                Pendaftaran::STATUS_MENUNGGU            => 'Menunggu Antrian Perawat',
                Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'Pemeriksaan Perawat (TTV & CPPT)',
                Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'Menunggu Pemeriksaan Dokter',
                Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
                Pendaftaran::STATUS_SELESAI             => 'Pelayanan Selesai',
            ];

        return $table
            ->query($query)
            ->searchPlaceholder('Cari No. RM / Nama Pasien...')
            ->searchDebounce('400ms')
            ->poll('10s')
            ->recordUrl(null)
            ->columns([
                ViewColumn::make('kunjungan')
                    ->label('Kunjungan Pasien')
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
            ->filtersFormColumns(2)
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

                // Filter Status Pelayanan
                SelectFilter::make('status_pelayanan')
                    ->label('Status Pelayanan')
                    ->placeholder('Semua Status')
                    ->options($statusFilterOptions),
            ])
            ->actions([
                // 1. Aksi untuk Perawat: Terima Pasien dari Antrian Pendaftaran
                Action::make('terima_perawat')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-check-circle')
                    ->visible(function (Pendaftaran $record) use ($isPerawat, $isAdmin): bool {
                        return ($isPerawat || $isAdmin) && $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU;
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT]);
                        session(['active_pendaftaran_id' => $record->id]);

                        Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah diterima untuk pemeriksaan awal oleh perawat.")
                            ->info()
                            ->send();

                        return redirect()->to(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 2. Aksi untuk Perawat: Lanjutkan Pemeriksaan Perawat
                Action::make('periksa_perawat')
                    ->label('Periksa')
                    ->button()
                    ->color('primary')
                    ->size('sm')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->visible(function (Pendaftaran $record) use ($isPerawat, $isAdmin): bool {
                        return ($isPerawat || $isAdmin) && $record->status_pelayanan === Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT;
                    })
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),

                // 3. Aksi untuk Dokter: Terima Pasien (Setelah Perawat Mengisi Pemeriksaan & CPPT)
                Action::make('terima_dokter')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-check-circle')
                    ->visible(function (Pendaftaran $record) use ($isDokter, $isAdmin): bool {
                        return ($isDokter || $isAdmin) && $record->status_pelayanan === Pendaftaran::STATUS_MENUNGGU_DOKTER;
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_SEDANG_DIPERIKSA]);
                        session(['active_pendaftaran_id' => $record->id]);

                        Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah masuk ke ruang pemeriksaan dokter.")
                            ->success()
                            ->send();

                        return redirect()->to(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 4. Aksi untuk Dokter: Periksa / Buka Layanan Pasien
                Action::make('periksa_dokter')
                    ->label('Periksa')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->visible(function (Pendaftaran $record) use ($isDokter, $isAdmin): bool {
                        return ($isDokter || $isAdmin) && $record->status_pelayanan === Pendaftaran::STATUS_SEDANG_DIPERIKSA;
                    })
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),

                // 5. Aksi untuk Dokter & Admin: Selesaikan Pelayanan
                Action::make('selesaikan')
                    ->label('Selesaikan')
                    ->button()
                    ->color('success')
                    ->size('sm')
                    ->icon('heroicon-m-check-badge')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalHeading('Selesaikan Pelayanan Pasien?')
                    ->modalDescription(fn (Pendaftaran $record) => "Dokter akan menyelesaikan pelayanan/pemeriksaan pasien {$record->pasien?->nama}. Status kunjungan akan berubah menjadi Selesai dan tidak lagi masuk antrean aktif.")
                    ->modalSubmitActionLabel('Ya, Selesaikan')
                    ->visible(function (Pendaftaran $record) use ($isDokter, $isAdmin): bool {
                        return ($isDokter || $isAdmin) && $record->status_pelayanan === Pendaftaran::STATUS_SEDANG_DIPERIKSA;
                    })
                    ->action(fn (Pendaftaran $record) => $this->selesaikanPelayanan($record)),

                // 6. Tombol Lihat Data Detail Kunjungan (Abu-abu)
                Action::make('lihat')
                    ->label('Lihat')
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-eye')
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, [Pendaftaran::STATUS_SELESAI, Pendaftaran::STATUS_MENUNGGU_DOKTER]))
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),

                // 7. Aksi Batal untuk Perawat & Admin
                Action::make('batal_perawat')
                    ->label('Batal')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kunjungan Pasien?')
                    ->modalDescription(fn (Pendaftaran $record) => "Apakah Anda yakin ingin membatalkan pendaftaran pelayanan pasien {$record->pasien?->nama}?")
                    ->modalSubmitActionLabel('Ya, Batalkan')
                    ->visible(function (Pendaftaran $record) use ($isPerawat, $isAdmin): bool {
                        return ($isPerawat || $isAdmin) && in_array($record->status_pelayanan, [Pendaftaran::STATUS_MENUNGGU, Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT]);
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => Pendaftaran::STATUS_BATAL]);
                        if (session('active_pendaftaran_id') == $record->id) {
                            session()->forget('active_pendaftaran_id');
                        }

                        Notification::make()
                            ->title('Pendaftaran Dibatalkan')
                            ->body("Kunjungan pasien {$record->pasien?->nama} telah dibatalkan.")
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    private function selesaikanPelayanan(Pendaftaran $record): void
    {
        $user = Auth::user();
        $isAuthorized = $user && (
            $user->hasRole(['super_admin', 'Admin', 'Dokter'])
            || $user->pegawai?->profesi === 'Dokter'
        );

        if (! $isAuthorized) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya dokter atau admin yang dapat menyelesaikan pelayanan pasien.')
                ->danger()
                ->send();
            return;
        }

        $record->refresh();

        if ($record->status_pelayanan !== Pendaftaran::STATUS_SEDANG_DIPERIKSA) {
            Notification::make()
                ->title('Status Pasien Sudah Berubah')
                ->body('Pasien ini sudah tidak berada dalam status pemeriksaan dokter.')
                ->warning()
                ->send();
            $this->resetTable();
            return;
        }

        $record->update(['status_pelayanan' => Pendaftaran::STATUS_SELESAI]);

        if (session('active_pendaftaran_id') === $record->id) {
            session()->forget('active_pendaftaran_id');
        }

        Notification::make()
            ->title('Pelayanan Selesai')
            ->body("Pelayanan untuk pasien {$record->pasien?->nama} telah berhasil diselesaikan.")
            ->success()
            ->send();

        $this->resetTable();
    }
}

