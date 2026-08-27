<?php

namespace App\Filament\Resources\Pendaftarans\Schemas;

use App\Models\Pasien;
use App\Models\Pegawai;
use App\Models\Pendaftaran;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PendaftaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Header Baris Utama: Pasien & Detail Pendaftaran
                Section::make('Pendaftaran Kunjungan Pasien')
                    ->icon('heroicon-o-user-plus')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Select::make('pasien_id')
                            ->label('Pilih Pasien')
                            ->relationship('pasien', 'nama')
                            ->getOptionLabelFromRecordUsing(fn (Pasien $record) => "{$record->no_rm} - {$record->nama}" . ($record->nama_panggilan ? " ({$record->nama_panggilan})" : ''))
                            ->searchable(['no_rm', 'nama', 'nomor_kartu'])
                            ->required()
                            ->live()
                            ->columnSpan(2)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $active = Pendaftaran::where('pasien_id', $state)
                                        ->whereIn('status_pelayanan', Pendaftaran::ACTIVE_STATUSES)
                                        ->first();

                                    if ($active) {
                                        Notification::make()
                                            ->warning()
                                            ->persistent()
                                            ->title('Pasien Masih Dalam Pelayanan')
                                            ->body("Pasien ini masih memiliki antrian/pelayanan aktif ({$active->no_pendaftaran} - {$active->poli?->nama} - Status: {$active->status_pelayanan}). Pelayanan sebelumnya harus diselesaikan terlebih dahulu.")
                                            ->send();
                                    }

                                    $pasien = Pasien::find($state);
                                    $hasHistory = Pendaftaran::where('pasien_id', $state)->exists();
                                    $set('jenis_kunjungan', $hasHistory ? 'Lama' : 'Baru');

                                    // Isi penanggung jawab jika pasien memiliki data keluarga
                                    if ($pasien && $pasien->keluargas()->exists()) {
                                        $kel = $pasien->keluargas()->first();
                                        $set('ada_pj', true);
                                        $set('pj_nama', $kel->nama);
                                        $set('pj_hubungan', $kel->statusKeluarga?->deskripsi ?? 'Keluarga');
                                        $set('pj_no_telepon', $kel->telepon);
                                        $set('pj_alamat', $kel->alamat);
                                    }
                                }
                            })
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    $active = Pendaftaran::where('pasien_id', $value)
                                        ->whereIn('status_pelayanan', Pendaftaran::ACTIVE_STATUSES)
                                        ->exists();

                                    if ($active) {
                                        $fail('Pasien ini masih memiliki pendaftaran aktif yang belum selesai. Selesaikan atau batalkan pelayanan sebelumnya terlebih dahulu.');
                                    }
                                },
                            ]),

                        DateTimePicker::make('tanggal_pendaftaran')
                            ->label('Waktu Pendaftaran')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        TextInput::make('no_pendaftaran')
                            ->label('No. Pendaftaran')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Otomatis di-generate')
                            ->helperText('Contoh: REG-YYYYMMDD-0001'),
                    ]),

                // Grid 2 Kolom: KIRI (Tujuan Pelayanan), KANAN (Penanggung Jawab & Pengantar)
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        // ==========================================
                        // KOLOM KIRI: Tujuan Layanan & Catatan
                        // ==========================================
                        Grid::make(1)
                            ->schema([
                                Section::make('Tujuan ke:')
                                    ->icon('heroicon-o-home')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('jenis_pelayanan')
                                            ->label('Jenis Pelayanan')
                                            ->options([
                                                'Pelayanan Rawat Jalan'            => 'Pelayanan Rawat Jalan',
                                                'Pelayanan Gawat Darurat (IGD)'   => 'Pelayanan Gawat Darurat (IGD)',
                                                'Pelayanan Rawat Inap'             => 'Pelayanan Rawat Inap',
                                            ])
                                            ->default('Pelayanan Rawat Jalan')
                                            ->required()
                                            ->native(false)
                                            ->columnSpanFull(),

                                        Select::make('kategori_pelayanan')
                                            ->label('Kategori Pelayanan')
                                            ->options([
                                                'Pelayanan Medik Dasar'        => 'Pelayanan Medik Dasar',
                                                'Pelayanan Medik Spesialistik' => 'Pelayanan Medik Spesialistik',
                                                'Pelayanan Penunjang Medis'    => 'Pelayanan Penunjang Medis',
                                            ])
                                            ->default('Pelayanan Medik Dasar')
                                            ->native(false)
                                            ->dehydrated(false),

                                        Select::make('poli_id')
                                            ->label('Poli Tujuan')
                                            ->relationship('poli', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->placeholder('Pilih Poli Tujuan'),

                                        Select::make('dokter_id')
                                            ->label('Dokter / DPJP')
                                            ->options(function (Get $get) {
                                                $query = Pegawai::query()
                                                    ->where(function ($q) {
                                                        $q->where('profesi', 'Dokter')
                                                          ->orWhere('profesi', 'dokter');
                                                    });

                                                if ($poliId = $get('poli_id')) {
                                                    $poliDokters = (clone $query)->where('poli_id', $poliId)->get();
                                                    if ($poliDokters->isNotEmpty()) {
                                                        return $poliDokters->mapWithKeys(function ($d) {
                                                            $gelar = trim("{$d->gelar_depan} {$d->nama_lengkap}" . ($d->gelar_belakang ? ", {$d->gelar_belakang}" : ''));
                                                            $nip = $d->nip ? "{$d->nip} - " : '';
                                                            return [$d->id => "{$nip}{$gelar}"];
                                                        });
                                                    }
                                                }

                                                return $query->get()->mapWithKeys(function ($d) {
                                                    $gelar = trim("{$d->gelar_depan} {$d->nama_lengkap}" . ($d->gelar_belakang ? ", {$d->gelar_belakang}" : ''));
                                                    $nip = $d->nip ? "{$d->nip} - " : '';
                                                    return [$d->id => "{$nip}{$gelar}"];
                                                });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih Dokter')
                                            ->columnSpanFull(),

                                        Textarea::make('catatan')
                                            ->label('Catatan Pendaftaran')
                                            ->rows(2)
                                            ->columnSpanFull()
                                            ->placeholder('Catatan khusus pendaftaran / keluhan singkat pasien'),
                                    ]),
                            ]),

                        // ==========================================
                        // KOLOM KANAN: Penanggung Jawab Pasien
                        // ==========================================
                        Grid::make(1)
                            ->schema([

                                // Section: Kasus Kecelakaan
                                Section::make('Kasus Kecelakaan')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->collapsible()
                                    ->columns(2)
                                    ->schema([
                                        Checkbox::make('is_kecelakaan')
                                            ->label('Centang jika merupakan Kasus Kecelakaan')
                                            ->live()
                                            ->columnSpanFull(),

                                        Select::make('jenis_kecelakaan')
                                            ->label('Jenis Kecelakaan')
                                            ->options([
                                                'KLL dan bukan kecelakaan kerja' => 'KLL dan bukan kecelakaan kerja',
                                                'KLL dan kecelakaan kerja'      => 'KLL dan kecelakaan kerja',
                                                'Kecelakaan Kerja'              => 'Kecelakaan Kerja',
                                                'Bukan KLL dan Kecelakaan Kerja' => 'Bukan KLL dan Kecelakaan Kerja',
                                            ])
                                            ->placeholder('Pilih Jenis')
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('is_kecelakaan')),

                                        TextInput::make('no_laporan_polisi')
                                            ->label('No. Laporan Polisi (LP)')
                                            ->placeholder('No. LP Kepolisian')
                                            ->visible(fn (Get $get): bool => (bool) $get('is_kecelakaan')),

                                        DatePicker::make('tgl_kejadian_kecelakaan')
                                            ->label('Tanggal Kejadian')
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('is_kecelakaan')),

                                        Select::make('penjamin_kecelakaan')
                                            ->label('Penjamin Kecelakaan')
                                            ->options([
                                                'Jasa Raharja'          => 'Jasa Raharja',
                                                'BPJS Ketenagakerjaan'  => 'BPJS Ketenagakerjaan',
                                                'TASPEN'                => 'TASPEN',
                                                'ASABRI'                => 'ASABRI',
                                                'Pribadi / Lainnya'     => 'Pribadi / Lainnya',
                                            ])
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('is_kecelakaan')),

                                        TextInput::make('lokasi_kecelakaan')
                                            ->label('Lokasi Kejadian')
                                            ->columnSpanFull()
                                            ->placeholder('Nama jalan, kota/kabupaten kejadian')
                                            ->visible(fn (Get $get): bool => (bool) $get('is_kecelakaan')),
                                    ]),

                                // Section: Penanggung Jawab
                                Section::make('Penanggung Jawab Pasien')
                                    ->icon('heroicon-o-user')
                                    ->collapsible()
                                    ->columns(2)
                                    ->schema([
                                        Checkbox::make('ada_pj')
                                            ->label('Centang jika ada Penanggung Jawab / Wali')
                                            ->live()
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function ($component, $state, $record) {
                                                if ($record && ($record->pj_nama || $record->pj_no_telepon)) {
                                                    $component->state(true);
                                                }
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('pj_nama')
                                            ->label('Nama Penanggung Jawab')
                                            ->placeholder('Nama keluarga/wali')
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        Select::make('pj_hubungan')
                                            ->label('Hubungan Dgn Pasien')
                                            ->options([
                                                'Orang Tua'       => 'Orang Tua (Ayah / Ibu)',
                                                'Suami / Istri'   => 'Suami / Istri',
                                                'Anak'            => 'Anak Kandung',
                                                'Saudara Kandung' => 'Saudara Kandung',
                                                'Wali / Kerabat'  => 'Wali / Kerabat',
                                                'Diri Sendiri'    => 'Diri Sendiri',
                                            ])
                                            ->placeholder('Pilih Hubungan')
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        TextInput::make('pj_no_telepon')
                                            ->label('Telepon Seluler / HP')
                                            ->tel()
                                            ->placeholder('08xxxxxxxxxx')
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        Select::make('pj_jenis_kartu')
                                            ->label('Jenis Kartu Identitas')
                                            ->options([
                                                'KTP'     => 'Kartu Tanda Penduduk (KTP)',
                                                'SIM'     => 'Surat Izin Mengemudi (SIM)',
                                                'Paspor'  => 'Paspor',
                                                'Lainnya' => 'Lainnya',
                                            ])
                                            ->default('KTP')
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        TextInput::make('pj_nomor_kartu')
                                            ->label('Nomor Kartu Identitas')
                                            ->placeholder('NIK / No. Identitas')
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        TextInput::make('pj_pekerjaan')
                                            ->label('Pekerjaan')
                                            ->placeholder('Pekerjaan penanggung jawab')
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),

                                        Textarea::make('pj_alamat')
                                            ->label('Alamat Penanggung Jawab')
                                            ->rows(2)
                                            ->columnSpanFull()
                                            ->placeholder('Alamat lengkap penanggung jawab')
                                            ->visible(fn (Get $get): bool => (bool) $get('ada_pj')),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
