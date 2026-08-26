<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use App\Models\District;
use App\Models\Province;
use App\Models\ReferensiDetail;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Identitas')
                    ->icon('heroicon-o-identification')
                    ->columnSpanFull()
                    ->columns(4)
                    ->afterHeader([
                        Toggle::make('status')
                            ->label('Status')
                            ->inline(false)
                            ->formatStateUsing(fn($state) => $state === 'Aktif' || $state === true || $state === 1)
                            ->dehydrateStateUsing(fn($state) => $state ? 'Aktif' : 'Nonaktif'),
                    ])
                    ->schema([
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('gelar_depan')
                            ->label('Gelar Depan'),

                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required(),

                        TextInput::make('gelar_belakang')
                            ->label('Gelar Belakang'),

                        Select::make('tempat_lahir_regency_id')
                            ->label('Tempat Lahir')
                            ->relationship('tempatLahir', 'name')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->displayFormat('d/m/Y'),

                        Select::make('user_id')
                            ->label('Akun Pengguna (Login)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Opsional: pilih akun login untuk pegawai ini'),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required(),

                        Select::make('agama_detail_id')
                            ->label('Agama')
                            ->options(fn() => ReferensiDetail::whereHas(
                                'referensi',
                                fn($q) => $q->where('nama', 'Agama')
                            )->pluck('deskripsi', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('profesi')
                            ->label('Profesi')
                            ->options(fn() => ReferensiDetail::whereHas(
                                'referensi',
                                fn($q) => $q->where('nama', 'Pegawai')
                            )->where('status', true)
                                ->orderBy('urutan')
                                ->pluck('deskripsi', 'deskripsi'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        Select::make('jenis_spesialis_detail_id')
                            ->label('Jenis Spesialis/Subspesialis')
                            ->options(fn() => ReferensiDetail::whereHas(
                                'referensi',
                                fn($q) => $q->where('nama', 'Jenis Spesialis/Subspesialis')
                            )->pluck('deskripsi', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('profesi') === 'Dokter'),

                        Select::make('poli_id')
                            ->label('Poli Penugasan / Unit Layanan')
                            ->relationship('poli', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(fn(Get $get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan']))
                            ->visible(fn(Get $get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan']))
                            ->helperText('Wajib: Tentukan penugasan Poli Umum atau Poli Gigi agar antrian dan riwayat tidak tertukar.')
                            ->placeholder('Pilih Poli Penugasan (misal: Poli Umum / Poli Gigi)'),

                        TextInput::make('no_str')
                            ->label('No. STR')
                            ->visible(fn($get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan'])),

                        DatePicker::make('str_berlaku_sampai')
                            ->label('STR Berlaku Sampai')
                            ->displayFormat('d/m/Y')
                            ->visible(fn($get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan'])),

                        TextInput::make('no_sip')
                            ->label('No. SIP')
                            ->visible(fn($get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan'])),

                        DatePicker::make('sip_berlaku_sampai')
                            ->label('SIP Berlaku Sampai')
                            ->displayFormat('d/m/Y')
                            ->visible(fn($get) => in_array($get('profesi'), ['Dokter', 'Perawat', 'Bidan'])),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Kartu Identitas')
                            ->icon('heroicon-o-identification')
                            ->columns(2)
                            ->schema([
                                Select::make('jenis_kartu_detail_id')
                                    ->label('Jenis Kartu Identitas')
                                    ->options(fn() => ReferensiDetail::whereHas(
                                        'referensi',
                                        fn($q) => $q->where('nama', 'Jenis Kartu Identitas')
                                    )->pluck('deskripsi', 'id'))
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('nomor_kartu')
                                    ->label('Nomor Kartu'),

                                Textarea::make('alamat_kartu')
                                    ->label('Alamat')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('rt_kartu')->label('RT'),
                                TextInput::make('rw_kartu')->label('RW'),
                                TextInput::make('kode_pos_kartu')->label('Kode Pos'),

                                Select::make('province_id_kartu')
                                    ->label('Propinsi')
                                    ->options(fn() => Province::pluck('name', 'code'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $regencyId = $get('regency_id_kartu');
                                        if ($regencyId && ! Regency::where('code', $regencyId)->where('code', 'like', $get('province_id_kartu') . '.%')->exists()) {
                                            $set('regency_id_kartu', null);
                                            $set('district_id_kartu', null);
                                            $set('village_id_kartu', null);
                                        }
                                    }),

                                Select::make('regency_id_kartu')
                                    ->label('Kabupaten / Kota')
                                    ->options(fn($get) => $get('province_id_kartu')
                                        ? Regency::where('code', 'like', $get('province_id_kartu') . '.%')->pluck('name', 'code')
                                        : Regency::pluck('name', 'code'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $districtId = $get('district_id_kartu');
                                        if ($districtId && ! District::where('code', $districtId)->where('code', 'like', $get('regency_id_kartu') . '.%')->exists()) {
                                            $set('district_id_kartu', null);
                                            $set('village_id_kartu', null);
                                        }
                                    }),

                                Select::make('district_id_kartu')
                                    ->label('Kecamatan')
                                    ->options(fn($get) => $get('regency_id_kartu')
                                        ? District::where('code', 'like', $get('regency_id_kartu') . '.%')->pluck('name', 'code')
                                        : District::pluck('name', 'code'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $villageId = $get('village_id_kartu');
                                        if ($villageId && ! Village::where('code', $villageId)->where('code', 'like', $get('district_id_kartu') . '.%')->exists()) {
                                            $set('village_id_kartu', null);
                                        }
                                    }),

                                Select::make('village_id_kartu')
                                    ->label('Kelurahan / Desa')
                                    ->options(fn($get) => $get('district_id_kartu')
                                        ? Village::where('code', 'like', $get('district_id_kartu') . '.%')->pluck('name', 'code')
                                        : Village::pluck('name', 'code'))
                                    ->searchable(),
                            ]),

                        Section::make('Alamat')
                            ->icon('heroicon-o-map-pin')
                            ->columns(2)
                            ->schema([
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('rt')->label('RT'),
                                TextInput::make('rw')->label('RW'),
                                TextInput::make('kode_pos')->label('Kode Pos'),

                                Select::make('province_id')
                                    ->label('Propinsi')
                                    ->relationship('province', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $regencyId = $get('regency_id');
                                        if ($regencyId && ! Regency::where('code', $regencyId)->where('code', 'like', $get('province_id') . '.%')->exists()) {
                                            $set('regency_id', null);
                                            $set('district_id', null);
                                            $set('village_id', null);
                                        }
                                    }),

                                Select::make('regency_id')
                                    ->label('Kabupaten / Kota')
                                    ->options(fn($get) => $get('province_id')
                                        ? Regency::where('code', 'like', $get('province_id') . '.%')->pluck('name', 'code')
                                        : Regency::pluck('name', 'code'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $districtId = $get('district_id');
                                        if ($districtId && ! District::where('code', $districtId)->where('code', 'like', $get('regency_id') . '.%')->exists()) {
                                            $set('district_id', null);
                                            $set('village_id', null);
                                        }
                                    }),

                                Select::make('district_id')
                                    ->label('Kecamatan')
                                    ->options(fn($get) => $get('regency_id')
                                        ? District::where('code', 'like', $get('regency_id') . '.%')->pluck('name', 'code')
                                        : District::pluck('name', 'code'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $villageId = $get('village_id');
                                        if ($villageId && ! Village::where('code', $villageId)->where('code', 'like', $get('district_id') . '.%')->exists()) {
                                            $set('village_id', null);
                                        }
                                    }),

                                Select::make('village_id')
                                    ->label('Kelurahan / Desa')
                                    ->options(fn($get) => $get('district_id')
                                        ? Village::where('code', 'like', $get('district_id') . '.%')->pluck('name', 'code')
                                        : Village::pluck('name', 'code'))
                                    ->searchable(),
                            ]),
                    ]),

                Section::make('Kontak')
                    ->icon('heroicon-o-phone')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('kontaks')
                            ->relationship()
                            ->label('')
                            ->columns(2)
                            ->schema([
                                TextInput::make('nomor_kontak')
                                    ->label('Nomor')
                                    ->placeholder('Contoh: 081234567890')
                                    ->required(),

                                Select::make('jenis_kontak_detail_id')
                                    ->label('Jenis')
                                    ->options(fn() => ReferensiDetail::whereHas(
                                        'referensi',
                                        fn($q) => $q->where('nama', 'Jenis Kontak')
                                    )->pluck('deskripsi', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                // field status tetap disimpan, tapi disembunyikan dari grid,
                                // dikendalikan lewat action toggle di header
                                Toggle::make('status')
                                    ->label('Status')
                                    ->default(true)
                                    ->hidden(),
                            ])
                            ->extraItemActions([
                                Action::make('toggleStatus')
                                    ->icon(fn(Get $get): string => $get('status') ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                    ->color(fn(Get $get): string => $get('status') ? 'success' : 'danger')
                                    ->tooltip('Klik untuk ubah status Aktif/Non Aktif')
                                    ->action(fn(Get $get, Set $set) => $set('status', ! $get('status'))),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Kontak')
                            ->reorderable(false),
                    ]),
            ]);
    }
}
