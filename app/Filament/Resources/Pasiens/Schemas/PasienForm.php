<?php

namespace App\Filament\Resources\Pasiens\Schemas;

use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\UnitEksternal;
use App\Models\Village;
use App\Support\ReferensiOpsi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PasienForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. DATA IDENTITAS (4 COLUMNS DENGAN TOGGLE STATUS DI HEADER SEPERTI PEGAWAI)
                Section::make('Data Identitas')
                    ->icon('heroicon-o-identification')
                    ->columnSpanFull()
                    ->columns(4)
                    ->afterHeader([
                        Toggle::make('status_pasien')
                            ->label('Status Pasien')
                            ->inline(false)
                            ->formatStateUsing(fn($state) => $state === 'Hidup' || $state === true || $state === 1 || empty($state))
                            ->dehydrateStateUsing(fn($state) => $state ? 'Hidup' : 'Meninggal'),
                    ])
                    ->schema([
                        TextInput::make('no_rm')
                            ->label('No. RM (Rekam Medis)')
                            ->placeholder('Otomatis oleh sistem')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('No RM terisi otomatis'),

                        TextInput::make('norm_manual')
                            ->label('No. RM Manual / Lama')
                            ->placeholder('Opsional jika ada'),

                        TextInput::make('gelar_depan')
                            ->label('Gelar Depan')
                            ->placeholder('Contoh: dr., H., Prof.'),

                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required(),

                        TextInput::make('gelar_belakang')
                            ->label('Gelar Belakang')
                            ->placeholder('Contoh: S.Kom, M.Kes'),

                        TextInput::make('nama_panggilan')
                            ->label('Nama Panggilan'),

                        Select::make('tempat_lahir_regency_id')
                            ->label('Tempat Lahir')
                            ->relationship('tempatLahir', 'name')
                            ->searchable()
                            ->preload(),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->displayFormat('d/m/Y'),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-Laki' => 'Laki-Laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required(),

                        Select::make('agama')
                            ->label('Agama')
                            ->placeholder('[ Pilih Agama ]')
                            ->options(ReferensiOpsi::agama())
                            ->searchable()
                            ->preload(),

                        Select::make('status_perkawinan')
                            ->label('Status Perkawinan')
                            ->placeholder('[ Pilih Status Perkawinan ]')
                            ->options(ReferensiOpsi::statusPerkawinan())
                            ->searchable()
                            ->preload(),

                        Select::make('pendidikan')
                            ->label('Pendidikan')
                            ->placeholder('[ Pilih Pendidikan ]')
                            ->options(ReferensiOpsi::pendidikan())
                            ->searchable()
                            ->preload(),

                        Select::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->placeholder('[ Pilih Pekerjaan ]')
                            ->options(ReferensiOpsi::pekerjaan())
                            ->searchable()
                            ->preload(),

                        Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->placeholder('[ Pilih Golongan Darah ]')
                            ->options(ReferensiOpsi::golonganDarah())
                            ->searchable()
                            ->preload(),

                        Select::make('suku_bangsa')
                            ->label('Suku Bangsa')
                            ->placeholder('[ Pilih Suku Bangsa ]')
                            ->options(ReferensiOpsi::sukuBangsa())
                            ->searchable()
                            ->preload(),

                        Select::make('country_id')
                            ->label('Kewarganegaraan')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn($record) => $record ? $record->country_id : Country::where('code', 'ID')->value('id')),

                        Toggle::make('pasien_tidak_dikenal')
                            ->label('Pasien Tidak Dikenal')
                            ->inline(false)
                            ->default(false),
                    ]),

                // 2. ASAL INSTANSI / UNIT EKSTERNAL
                Section::make('Asal Instansi / Unit Eksternal')
                    ->icon('heroicon-o-building-office')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('unit_eksternal_id')
                            ->label('Unit Eksternal')
                            ->placeholder('[ Pilih Unit Eksternal ]')
                            ->options(fn() => UnitEksternal::whereNull('parent_id')->pluck('nama', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn($set) => $set('sub_unit_eksternal_id', null)),

                        Select::make('sub_unit_eksternal_id')
                            ->label('Sub Unit Eksternal')
                            ->placeholder('[ Pilih Sub Unit Eksternal ]')
                            ->options(function ($get) {
                                $parentId = $get('unit_eksternal_id');

                                return $parentId
                                    ? UnitEksternal::where('parent_id', $parentId)->pluck('nama', 'id')
                                    : [];
                            })
                            ->searchable()
                            ->disabled(fn($get) => blank($get('unit_eksternal_id'))),
                    ]),

                // 3. GRID 2 KOLOM: KARTU IDENTITAS & ALAMAT SEKARANG (PERSIS SEPERTI PEGAWAI)
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Kartu Identitas')
                            ->icon('heroicon-o-identification')
                            ->columns(2)
                            ->schema([
                                Toggle::make('sama_dengan_alamat_sekarang')
                                    ->label('Sama Dengan Alamat Sekarang')
                                    ->live()
                                    ->columnSpanFull(),

                                Select::make('jenis_kartu')
                                    ->label('Jenis Kartu Identitas')
                                    ->placeholder('[ Pilih Jenis Kartu ]')
                                    ->options(ReferensiOpsi::jenisKartu())
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),

                                TextInput::make('nomor_kartu')
                                    ->label('Nomor Kartu')
                                    ->columnSpan(1),

                                Textarea::make('alamat_kartu')
                                    ->label('Alamat')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),

                                TextInput::make('rt_kartu')->label('RT')
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),
                                TextInput::make('rw_kartu')->label('RW')
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),
                                TextInput::make('kode_pos_kartu')->label('Kode Pos')->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),

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
                                    })
                                    ->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),

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
                                    })
                                    ->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),

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
                                    })
                                    ->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),

                                Select::make('village_id_kartu')
                                    ->label('Kelurahan / Desa')
                                    ->options(fn($get) => $get('district_id_kartu')
                                        ? Village::where('code', 'like', $get('district_id_kartu') . '.%')->pluck('name', 'code')
                                        : Village::pluck('name', 'code'))
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->hidden(fn($get) => $get('sama_dengan_alamat_sekarang')),
                            ]),

                        Section::make('Alamat Sekarang')
                            ->icon('heroicon-o-map-pin')
                            ->columns(2)
                            ->schema([
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('rt')->label('RT'),
                                TextInput::make('rw')->label('RW'),
                                TextInput::make('kode_pos')->label('Kode Pos')->columnSpanFull(),

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
                                    })
                                    ->columnSpanFull(),

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
                                    })
                                    ->columnSpanFull(),

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
                                    })
                                    ->columnSpanFull(),

                                Select::make('village_id')
                                    ->label('Kelurahan / Desa')
                                    ->options(fn($get) => $get('district_id')
                                        ? Village::where('code', 'like', $get('district_id') . '.%')->pluck('name', 'code')
                                        : Village::pluck('name', 'code'))
                                    ->searchable()
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // 4. KONTAK (REPEATER SEPERTI PEGAWAI DENGAN 2 KOLOM)
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

                                Select::make('jenis_kontak')
                                    ->label('Jenis Kontak')
                                    ->placeholder('[ Pilih Jenis Kontak ]')
                                    ->options(ReferensiOpsi::jenisKontak())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Kontak')
                            ->reorderable(false),
                    ]),
            ]);
    }
}
