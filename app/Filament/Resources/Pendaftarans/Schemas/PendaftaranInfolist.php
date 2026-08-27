<?php

namespace App\Filament\Resources\Pendaftarans\Schemas;

use App\Models\Pendaftaran;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PendaftaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. CARD PROFIL PASIEN (PERSIS DENGAN INFOLIST PASIEN)
                ViewEntry::make('profile_card')
                    ->view('filament.infolists.components.patient-profile-card')
                    ->columnSpanFull(),

                // TABS DETAIL IDENTITAS, KELUARGA, KONTAK (PERSIS DENGAN INFOLIST PASIEN)
                Tabs::make('Detail Pasien')
                    ->tabs([
                        Tab::make('Kartu Identitas')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                RepeatableEntry::make('kartu_identitas')
                                    ->label('')
                                    ->state(fn (Pendaftaran $record) => $record->pasien?->nomor_kartu || $record->pasien?->jenis_kartu_detail_id
                                        ? [[
                                            'no_kartu' => $record->pasien->nomor_kartu,
                                            'jenis' => $record->pasien->jenisKartu?->deskripsi,
                                            'alamat' => $record->pasien->sama_dengan_alamat_sekarang ? $record->pasien->alamat : $record->pasien->alamat_kartu,
                                            'kelurahan' => $record->pasien->village?->name,
                                            'kecamatan' => $record->pasien->district?->name,
                                            'kabupaten' => $record->pasien->regency?->name,
                                            'provinsi' => $record->pasien->province?->name,
                                        ]]
                                        : [])
                                    ->schema([
                                        Grid::make(7)
                                            ->schema([
                                                TextEntry::make('no_kartu')->label('No. Kartu')->placeholder('-'),
                                                TextEntry::make('jenis')->label('Jenis')->placeholder('-'),
                                                TextEntry::make('alamat')->label('Alamat')->placeholder('-'),
                                                TextEntry::make('kelurahan')->label('Kelurahan')->placeholder('-'),
                                                TextEntry::make('kecamatan')->label('Kecamatan')->placeholder('-'),
                                                TextEntry::make('kabupaten')->label('Kabupaten / Kota')->placeholder('-'),
                                                TextEntry::make('provinsi')->label('Provinsi')->placeholder('-'),
                                            ]),
                                    ])
                                    ->contained(false),
                            ]),

                        Tab::make('Kartu Jaminan / Asuransi')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                // Tab asuransi / jaminan
                            ]),

                        Tab::make('Keluarga Pasien')
                            ->icon('heroicon-o-users')
                            ->schema([
                                RepeatableEntry::make('pasien.keluargas')
                                    ->label('')
                                    ->schema([
                                        Grid::make(7)
                                            ->schema([
                                                TextEntry::make('statusKeluarga.deskripsi')->label('SHDK')->placeholder('-'),
                                                TextEntry::make('nama')->label('Nama')->placeholder('-'),
                                                TextEntry::make('jenis_kelamin')->label('Jenis Kelamin')->placeholder('-'),
                                                TextEntry::make('tanggal_lahir')->label('Tgl. Lahir')->date('d/m/Y')->placeholder('-'),
                                                TextEntry::make('pendidikan.deskripsi')->label('Pendidikan')->placeholder('-'),
                                                TextEntry::make('pekerjaan.deskripsi')->label('Pekerjaan')->placeholder('-'),
                                                TextEntry::make('telepon_seluler')->label('Telepon')->placeholder('-'),
                                            ]),
                                    ])
                                    ->contained(false),
                            ]),

                        Tab::make('Kontak')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                RepeatableEntry::make('pasien.kontaks')
                                    ->label('')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('jenisKontak.deskripsi')->label('Jenis Kontak')->placeholder('-'),
                                                TextEntry::make('nomor_kontak')->label('Nomor Kontak')->placeholder('-'),
                                            ]),
                                    ])
                                    ->contained(false),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 2. DATA KUNJUNGAN SAAT INI
                Section::make('Data Kunjungan Saat Ini')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('no_pendaftaran')
                            ->label('No. Registrasi')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('no_antrian')
                            ->label('No. Antrian')
                            ->badge()
                            ->color(fn (Pendaftaran $record): string => match ($record->status_pelayanan) {
                                'Menunggu' => 'primary',
                                'Sedang Diperiksa' => 'info',
                                default => 'gray',
                            })
                            ->weight('bold')
                            ->formatStateUsing(function (Pendaftaran $record, $state): ?string {
                                if (in_array($record->status_pelayanan, ['Selesai', 'Batal'])) {
                                    return '-';
                                }
                                return $state ? "Antrian #{$state}" : '-';
                            }),

                        TextEntry::make('tanggal_pendaftaran')
                            ->label('Waktu Pendaftaran')
                            ->dateTime('d/m/Y H:i:s'),

                        TextEntry::make('status_pelayanan')
                            ->label('Status Pelayanan')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Pendaftaran::STATUS_MENUNGGU            => 'Menunggu Antrian Perawat',
                                Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT => 'Sedang Dilayani Perawat',
                                Pendaftaran::STATUS_MENUNGGU_DOKTER     => 'Siap Diperiksa Dokter',
                                Pendaftaran::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
                                Pendaftaran::STATUS_FINAL               => 'Final',
                                Pendaftaran::STATUS_BATAL               => 'Dibatalkan',
                                default                                 => $state,
                            })
                            ->colors([
                                'warning' => Pendaftaran::STATUS_MENUNGGU,
                                'info'    => Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT,
                                'primary' => Pendaftaran::STATUS_MENUNGGU_DOKTER,
                                'cyan'    => Pendaftaran::STATUS_SEDANG_DIPERIKSA,
                                'success' => Pendaftaran::STATUS_FINAL,
                                'danger'  => Pendaftaran::STATUS_BATAL,
                            ]),

                        TextEntry::make('jenis_pelayanan')
                            ->label('Jenis Pelayanan'),

                        TextEntry::make('poli.nama')
                            ->label('Poli Tujuan')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('dokter.nama_lengkap')
                            ->label('Dokter / DPJP')
                            ->placeholder('-'),

                        TextEntry::make('petugas.name')
                            ->label('Petugas Pendaftaran')
                            ->placeholder('-'),

                        TextEntry::make('pj_nama')
                            ->label('Penanggung Jawab')
                            ->state(fn (Pendaftaran $record): string => $record->pj_nama ? "{$record->pj_nama} ({$record->pj_hubungan})" : '-')
                            ->columnSpan(2),

                        TextEntry::make('catatan')
                            ->label('Catatan Pendaftaran')
                            ->placeholder('-')
                            ->columnSpan(2),
                    ]),

                // 3. RIWAYAT BEROBAT / HISTORY KUNJUNGAN PASIEN
                Section::make('Riwayat Berobat Pasien (History Kunjungan)')
                    ->icon('heroicon-o-clock')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('pasien.pendaftarans')
                            ->label('Daftar Riwayat Kunjungan & Pelayanan Pasien')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('no_pendaftaran')
                                    ->label('No. Registrasi')
                                    ->weight('bold')
                                    ->fontFamily('mono'),

                                TextEntry::make('tanggal_pendaftaran')
                                    ->label('Waktu Kunjungan')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('poli.nama')
                                    ->label('Poli Layanan')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('dokter.nama_lengkap')
                                    ->label('Dokter / DPJP')
                                    ->placeholder('-'),

                                TextEntry::make('status_pelayanan')
                                    ->label('Status')
                                    ->badge()
                                    ->colors([
                                        'warning' => 'Menunggu',
                                        'info'    => 'Sedang Diperiksa',
                                        'success' => 'Selesai',
                                        'danger'  => 'Batal',
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
