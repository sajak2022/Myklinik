<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('nip')
                            ->label('NIP')
                            ->placeholder('-'),

                        TextEntry::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->placeholder('-'),

                        TextEntry::make('gelar_depan')
                            ->label('Gelar Depan')
                            ->placeholder('-'),

                        TextEntry::make('gelar_belakang')
                            ->label('Gelar Belakang')
                            ->placeholder('-'),

                        TextEntry::make('tempatLahir.name')
                            ->label('Tempat Lahir')
                            ->placeholder('-'),

                        TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d/m/Y')
                            ->placeholder('-'),

                        TextEntry::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'Laki-laki' => 'info',
                                'Perempuan' => 'success',
                                default     => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('agama.deskripsi')
                            ->label('Agama')
                            ->placeholder('-'),

                        TextEntry::make('profesi')
                            ->label('Profesi')
                            ->badge()
                            ->color('primary')
                            ->placeholder('-'),

                        TextEntry::make('jenisSpesialis.deskripsi')
                            ->label('Jenis Spesialis')
                            ->placeholder('-'),

                        TextEntry::make('poli.nama')
                            ->label('Poli Penugasan / Unit Layanan')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),

                        TextEntry::make('no_str')
                            ->label('No. STR')
                            ->placeholder('-'),

                        TextEntry::make('str_berlaku_sampai')
                            ->label('STR Berlaku s/d')
                            ->date('d/m/Y')
                            ->placeholder('-'),

                        TextEntry::make('no_sip')
                            ->label('No. SIP')
                            ->placeholder('-'),

                        TextEntry::make('sip_berlaku_sampai')
                            ->label('SIP Berlaku s/d')
                            ->date('d/m/Y')
                            ->placeholder('-'),

                        TextEntry::make('jenisKartu.deskripsi')
                            ->label('Jenis Kartu Identitas')
                            ->placeholder('-'),

                        TextEntry::make('nomor_kartu')
                            ->label('Nomor Kartu')
                            ->placeholder('-'),

                        TextEntry::make('alamat_kartu')
                            ->label('Alamat KTP')
                            ->placeholder('-'),

                        TextEntry::make('rt_kartu')
                            ->label('RT (KTP)')
                            ->placeholder('-'),

                        TextEntry::make('rw_kartu')
                            ->label('RW (KTP)')
                            ->placeholder('-'),

                        TextEntry::make('kode_pos_kartu')
                            ->label('Kode Pos')
                            ->placeholder('-'),

                        TextEntry::make('alamat')
                            ->label('Alamat Sekarang')
                            ->columnSpanFull()
                            ->placeholder('-'),

                        TextEntry::make('rt')
                            ->label('RT')
                            ->placeholder('-'),

                        TextEntry::make('rw')
                            ->label('RW')
                            ->placeholder('-'),

                        TextEntry::make('kode_pos')
                            ->label('Kode Pos')
                            ->placeholder('-'),

                        TextEntry::make('province.name')
                            ->label('Provinsi')
                            ->placeholder('-'),

                        TextEntry::make('regency.name')
                            ->label('Kabupaten / Kota')
                            ->placeholder('-'),

                        TextEntry::make('district.name')
                            ->label('Kecamatan')
                            ->placeholder('-'),

                        TextEntry::make('village.name')
                            ->label('Desa / Kelurahan')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'Aktif'    => 'success',
                                'Cuti'     => 'warning',
                                'Nonaktif' => 'danger',
                                default    => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('user.name')
                            ->label('Akun User')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diubah')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
