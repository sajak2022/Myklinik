<?php

namespace App\Filament\Resources\Pasiens\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PasienInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(12)
                            ->schema([

                                Grid::make(1)
                                    ->columnSpan(2)
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                ImageEntry::make('profile_foto')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(function ($record) {
                                                        $jk = strtolower($record->jenis_kelamin ?? '');

                                                        if (str_contains($jk, 'l') && !str_contains($jk, 'p')) {
                                                            return asset('profile/men.png');
                                                        } elseif (str_contains($jk, 'p')) {
                                                            return asset('profile/women.png');
                                                        }

                                                        return asset('profile/men.png');
                                                    })
                                                    ->size(110)
                                                    ->extraImgAttributes([
                                                        'class' => 'dark:invert dark:brightness-200 opacity-90 transition-all duration-200',
                                                    ])
                                                    ->alignCenter(),
                                            ])
                                            ->compact()
                                            ->contained(true)
                                            ->extraAttributes([
                                                'class' => 'bg-gray-100 dark:bg-gray-800/80 rounded-xl',
                                            ]),
                                    ]),


                                Grid::make(1)
                                    ->columnSpan(4)
                                    ->schema([
                                        TextEntry::make('no_rm')
                                            ->label('No. Rekam Medis')
                                            ->size('lg')
                                            ->weight('bold')
                                            ->placeholder('-'),

                                        TextEntry::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->state(fn($record) => trim(
                                                collect([$record->gelar_depan, $record->nama, $record->gelar_belakang])
                                                    ->filter()
                                                    ->join(' ')
                                            ))
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('ttl')
                                            ->label('Tempat / Tanggal Lahir')
                                            ->state(fn($record) => collect([
                                                $record->tempatLahir?->name,
                                                optional($record->tanggal_lahir)->translatedFormat('d F Y'),
                                            ])->filter()->join(' / '))
                                            ->placeholder('-'),

                                        TextEntry::make('umur')
                                            ->label('Umur')
                                            ->state(function ($record) {
                                                if (blank($record->tanggal_lahir)) {
                                                    return null;
                                                }
                                                $diff = Carbon::parse($record->tanggal_lahir)->diff(now());
                                                return "{$diff->y}th {$diff->m}bln {$diff->d}hr";
                                            })
                                            ->placeholder('-'),

                                        TextEntry::make('agama.deskripsi')
                                            ->label('Agama')
                                            ->placeholder('-'),

                                        TextEntry::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->badge(),
                                    ]),


                                Grid::make(2)
                                    ->columnSpan(6)
                                    ->schema([
                                        TextEntry::make('pendidikan.deskripsi')
                                            ->label('Pendidikan')
                                            ->placeholder('-'),

                                        TextEntry::make('pekerjaan.deskripsi')
                                            ->label('Pekerjaan')
                                            ->placeholder('-'),

                                        TextEntry::make('statusPerkawinan.deskripsi')
                                            ->label('Status Perkawinan')
                                            ->placeholder('-'),

                                        TextEntry::make('golonganDarah.deskripsi')
                                            ->label('Gol. Darah')
                                            ->placeholder('-'),

                                        TextEntry::make('sukuBangsa.deskripsi')
                                            ->label('Suku Bangsa')
                                            ->placeholder('-'),

                                        TextEntry::make('country.name')
                                            ->label('Kewarganegaraan')
                                            ->placeholder('-'),

                                        TextEntry::make('alamat')
                                            ->label('Alamat')
                                            ->placeholder('-')
                                            ->columnSpan(2),

                                        TextEntry::make('rt_rw')
                                            ->label('RT / RW / Kode Pos')
                                            ->state(fn($record) => "RT " . ($record->rt ?? '-') . " / RW " . ($record->rw ?? '-') . " (Pos: " . ($record->kode_pos ?? '-') . ")")
                                            ->columnSpan(2),

                                        TextEntry::make('wilayah')
                                            ->label('Wilayah (Kel / Kec / Kab / Prov)')
                                            ->state(fn($record) => collect([
                                                $record->village?->name,
                                                $record->district?->name,
                                                $record->regency?->name,
                                                $record->province?->name,
                                            ])->filter()->join(', '))
                                            ->placeholder('-')
                                            ->columnSpan(2),
                                    ]),
                            ]),
                    ]),

                Tabs::make('Detail')
                    ->tabs([
                        Tab::make('Kartu Identitas')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                RepeatableEntry::make('kartu_identitas')
                                    ->label('')
                                    ->state(fn($record) => $record->nomor_kartu || $record->jenis_kartu_detail_id
                                        ? [[
                                            'no_kartu' => $record->nomor_kartu,
                                            'jenis' => $record->jenisKartu?->deskripsi,
                                            'alamat' => $record->sama_dengan_alamat_sekarang ? $record->alamat : $record->alamat_kartu,
                                            'kelurahan' => $record->village?->name,
                                            'kecamatan' => $record->district?->name,
                                            'kabupaten' => $record->regency?->name,
                                            'provinsi' => $record->province?->name,
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
                                // Tambahkan komponen jaminan/asuransi di sini
                            ]),

                        Tab::make('Keluarga Pasien')
                            ->icon('heroicon-o-users')
                            ->schema([
                                RepeatableEntry::make('keluargas')
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
                                RepeatableEntry::make('kontaks')
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
            ]);
    }
}
