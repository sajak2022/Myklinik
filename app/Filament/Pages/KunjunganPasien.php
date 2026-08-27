<?php

namespace App\Filament\Pages;

use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
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
            ->whereIn('status_pelayanan', [
                Pendaftaran::STATUS_FINAL,
                'Selesai',
                'Final',
                Pendaftaran::STATUS_BATAL,
                'Batal',
            ])
            ->latest('tanggal_pendaftaran')
            ->latest('id');

        if ($poliId && ! $user?->hasRole('super_admin')) {
            $query->where('poli_id', $poliId);
        }

        return $table
            ->query($query)
            ->searchPlaceholder('Cari No. RM / Nama Pasien...')
            ->searchDebounce('400ms')
            ->poll('5s')
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

                // Filter Status Pelayanan
                SelectFilter::make('status_pelayanan')
                    ->label('Status Pelayanan')
                    ->placeholder('Semua Status')
                    ->options([
                        'selesai' => 'Pelayanan Selesai',
                        'batal'   => 'Batal Kunjungan',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'batal'   => $query->whereIn('status_pelayanan', [Pendaftaran::STATUS_BATAL, 'Batal']),
                            'selesai' => $query->whereIn('status_pelayanan', [Pendaftaran::STATUS_FINAL, 'Selesai', 'Final']),
                            default   => $query,
                        };
                    }),
            ])
            ->actions([
                // Tombol Lihat Data Detail Kunjungan (Abu-abu)
                Action::make('lihat')
                    ->label('Lihat')
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-eye')
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, [
                        Pendaftaran::STATUS_FINAL,
                        'Selesai',
                        'Final',
                        Pendaftaran::STATUS_BATAL,
                        'Batal',
                    ]))
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),
            ]);
    }

    private function selesaikanPelayanan(Pendaftaran $record): void
    {
        /** @var User|null $user */
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

        if (in_array($record->status_pelayanan, [Pendaftaran::STATUS_FINAL, 'Selesai'])) {
            Notification::make()
                ->title('Peringatan: Pelayanan Sudah Final')
                ->body("Pelayanan untuk pasien {$record->pasien?->nama} ({$record->no_pendaftaran}) sudah berstatus Final dan tidak dapat diselesaikan ulang.")
                ->warning()
                ->send();
            $this->resetTable();
            return;
        }

        if (! in_array($record->status_pelayanan, [Pendaftaran::STATUS_SEDANG_DIPERIKSA, Pendaftaran::STATUS_MENUNGGU_DOKTER])) {
            Notification::make()
                ->title('Status Pasien Sudah Berubah')
                ->body('Pasien ini sudah tidak berada dalam status pemeriksaan dokter.')
                ->warning()
                ->send();
            $this->resetTable();
            return;
        }

        $record->selesaikanPelayanan();

        if (session('active_pendaftaran_id') === $record->id) {
            session()->forget('active_pendaftaran_id');
        }

        Notification::make()
            ->title('Pelayanan Final')
            ->body("Pelayanan untuk pasien {$record->pasien?->nama} telah berhasil difinalkan.")
            ->success()
            ->send();

        $this->resetTable();
    }
}

