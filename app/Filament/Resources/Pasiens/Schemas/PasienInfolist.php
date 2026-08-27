<?php

namespace App\Filament\Resources\Pasiens\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PasienInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewEntry::make('profile_card')
                    ->view('filament.infolists.components.patient-profile-card')
                    ->columnSpanFull(),

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
