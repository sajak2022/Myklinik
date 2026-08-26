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
        $query = Pendaftaran::query()
            ->whereIn('status_pelayanan', ['Sedang Diperiksa', 'Selesai'])
            ->with(['pasien.tempatLahir', 'poli', 'dokter'])
            ->latest('tanggal_pendaftaran');

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
                    ->placeholder('Pilih Status')
                    ->options([
                        'Sedang Diperiksa' => 'Pasien Berada di ruangan ini / Sedang dilayani',
                        'Selesai'          => 'Selesai',
                    ]),
            ])
            ->actions([
                // 1. Tombol Lihat Data Detail Kunjungan (Abu-abu)
                Action::make('lihat')
                    ->label('Lihat')
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Pendaftaran $record): string => \App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $record->id])),
            ]);
    }
}

