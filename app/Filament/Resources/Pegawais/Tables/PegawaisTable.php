<?php

namespace App\Filament\Resources\Pegawais\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'poli', 'tempatLahir']))
            ->columns([
                // Kolom Nomor Urut di sebelah kiri
                TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),

                // Menampilkan nama user dari tabel relasi
                TextColumn::make('user.name')
                    ->label('Akun User')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),

                TextColumn::make('nama_lengkap')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => trim(collect([$record->gelar_depan, $record->nama_lengkap, $record->gelar_belakang])->filter()->join(' '))),

                TextColumn::make('profesi')
                    ->label('Profesi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dokter'       => 'primary',
                        'Perawat'      => 'info',
                        'Bidan'        => 'warning',
                        'Farmasi'      => 'success',
                        'Administrasi' => 'gray',
                        default        => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('poli.nama')
                    ->label('Poli Penugasan')
                    ->badge()
                    ->color(fn ($record) => $record->poli?->nama === 'Poli Gigi' ? 'warning' : ($record->poli?->nama === 'Poli Umum' ? 'info' : 'gray'))
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('jenis_kelamin')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Laki-laki' => 'info',
                        'Perempuan' => 'success',
                        default     => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tempat_tanggal_lahir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif'    => 'success',
                        'Cuti'     => 'warning',
                        'Nonaktif' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('profesi')
                    ->label('Filter Profesi')
                    ->options([
                        'Dokter'       => 'Dokter',
                        'Perawat'      => 'Perawat',
                        'Bidan'        => 'Bidan',
                        'Farmasi'      => 'Farmasi',
                        'Administrasi' => 'Administrasi',
                    ]),

                SelectFilter::make('poli_id')
                    ->label('Filter Poli Penugasan')
                    ->relationship('poli', 'nama')
                    ->placeholder('Semua Poli'),

                SelectFilter::make('status')
                    ->label('Status Pegawai')
                    ->options([
                        'Aktif'    => 'Aktif',
                        'Cuti'     => 'Cuti',
                        'Nonaktif' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
