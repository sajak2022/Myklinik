<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Pendaftarans\PendaftaranResource;
use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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
        $query = Pendaftaran::query()->with(['pasien.tempatLahir', 'poli', 'dokter'])->latest('tanggal_pendaftaran');

        // Otomatis filter antrian/kunjungan sesuai Poli penugasan staf yang login (Perawat Poli Umum vs Perawat Poli Gigi)
        if ($user && ! $user->hasRole('super_admin')) {
            $pegawai = $user->pegawai;
            if ($pegawai && $pegawai->poli_id) {
                $query->where('poli_id', $pegawai->poli_id);
            }
        }

        return $table
            ->query($query)
            ->searchPlaceholder('Cari No. RM / Nama Pasien...')
            ->searchDebounce('400ms')
            ->poll('10s')
            ->recordUrl(null)
            ->columns([
                ViewColumn::make('kunjungan')
                    ->label('Informasi Kunjungan Pasien')
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
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter Data')
                    ->icon('heroicon-m-funnel')
                    ->size('sm')
            )
            ->filtersFormColumns(2)
            ->filters([
                // Filter Rentang Tanggal
                Filter::make('periode')
                    ->label('Rentang Tanggal')
                    ->columns(2)
                    ->columnSpanFull()
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('dd/mm/yyyy'),

                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('dd/mm/yyyy'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('tanggal_pendaftaran', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('tanggal_pendaftaran', '<=', $date),
                            );
                    }),

                // Filter Poli / Ruangan
                SelectFilter::make('poli_id')
                    ->label('Ruangan / Poli')
                    ->relationship('poli', 'nama')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Poli'),

                // Filter Status Pelayanan
                SelectFilter::make('status_pelayanan')
                    ->label('Status Pelayanan')
                    ->options([
                        'Menunggu'         => 'Menunggu Antrian',
                        'Sedang Diperiksa' => 'Sedang Dilayani',
                        'Selesai'          => 'Selesai Pelayanan',
                        'Batal'            => 'Batal',
                    ])
                    ->placeholder('Semua Status'),
            ])
            ->actions([
                // 1. Tombol Terima (Biru) - Jika status Menunggu -> Otomatis Membuka Halaman Pemeriksaan
                Action::make('terima')
                    ->label('Terima')
                    ->button()
                    ->color('info')
                    ->size('sm')
                    ->icon(null)
                    ->visible(fn (Pendaftaran $record): bool => $record->status_pelayanan === 'Menunggu')
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => 'Sedang Diperiksa']);

                        Notification::make()
                            ->title('Pasien Diterima')
                            ->body("Pasien {$record->pasien?->nama} telah diterima dan masuk ke ruang pemeriksaan.")
                            ->success()
                            ->send();

                        return redirect()->to(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]));
                    }),

                // 2. Tombol Masuk Pemeriksaan (Primary) - Saat status Sedang Diperiksa
                Action::make('periksa')
                    ->label('Pemeriksaan')
                    ->button()
                    ->color('primary')
                    ->size('sm')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]))
                    ->visible(fn (Pendaftaran $record): bool => $record->status_pelayanan === 'Sedang Diperiksa'),

                // 2. Tombol Selesaikan Pelayanan (Hijau) - Khusus Dokter / Admin (Bukan Perawat)
                Action::make('selesai')
                    ->label('Selesai')
                    ->button()
                    ->color('success')
                    ->size('sm')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Pelayanan Pasien?')
                    ->modalDescription('Apakah pemeriksaan dan pelayanan pasien ini sudah selesai?')
                    ->visible(function (Pendaftaran $record): bool {
                        if ($record->status_pelayanan !== 'Sedang Diperiksa') {
                            return false;
                        }

                        /** @var User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return false;
                        }

                        // Super admin selalu diizinkan
                        if ($user->hasRole('super_admin')) {
                            return true;
                        }

                        // Perawat tidak memiliki akses menyelesaikan pelayanan (hanya Dokter & Admin)
                        if ($user->hasRole('Perawat') || ($user->pegawai && $user->pegawai->profesi === 'Perawat')) {
                            return false;
                        }

                        return true;
                    })
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => 'Selesai']);

                        Notification::make()
                            ->title('Pelayanan Selesai')
                            ->body("Pelayanan pasien {$record->pasien?->nama} telah selesai.")
                            ->success()
                            ->send();
                    }),

                // 3. Tombol Lihat Data Detail Kunjungan (Abu-abu) - Mengarah ke Detail Kunjungan untuk Perawat, Dokter, dan Pendaftaran
                Action::make('lihat')
                    ->label('Lihat')
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id]))
                    ->visible(fn (Pendaftaran $record): bool => in_array($record->status_pelayanan, ['Sedang Diperiksa', 'Selesai'])),

                // 4. Tombol Batal (Merah) - HANYA saat status masih Menunggu (Sebelum Diterima)
                Action::make('batal')
                    ->label('Batal')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->icon(null)
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Kunjungan Pasien?')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan antrian pendaftaran kunjungan pasien ini?')
                    ->visible(fn (Pendaftaran $record): bool => $record->status_pelayanan === 'Menunggu')
                    ->action(function (Pendaftaran $record) {
                        $record->update(['status_pelayanan' => 'Batal']);

                        Notification::make()
                            ->title('Pendaftaran Dibatalkan')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}

