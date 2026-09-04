<?php

namespace App\Filament\Resources\Pasiens\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PasiensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                $livewire = $table->getLivewire();
                $pasienId = $livewire->pasien_id ?? request()->query('pasien_id');
                $search = $livewire->tableSearch ?? request()->query('tableSearch');

                $filters = $livewire->tableFilters ?? [];
                $semuaPasien = !empty($filters['semua_pasien']['isActive']);

                // 1. Jika filter "Tampilkan Semua Pasien" diaktifkan
                if ($semuaPasien) {
                    return $query->with(['tempatLahir', 'unitEksternal']);
                }

                // 2. Jika dipilih lewat Global Search -> Hanya tampilkan 1 pasien tersebut
                if ($pasienId) {
                    return $query->where('id', $pasienId)->with(['tempatLahir', 'unitEksternal']);
                }

                // 3. Jika dicari lewat search bar tabel -> Tampilkan hasil yang dicari
                if (filled($search)) {
                    return $query->with(['tempatLahir', 'unitEksternal']);
                }

                // 4. Default: Jangan tampilkan seluruh data pasien (tabel tetap kosong)
                return $query->whereRaw('1 = 0');
            })
            ->searchPlaceholder('Ketik No. RM / Nama Pasien / NIK / Alamat...')
            ->searchDebounce('400ms')
            ->emptyStateHeading('Cari Data Pasien')
            ->emptyStateDescription('Gunakan kolom Global Search di navbar atas (atau aktifkan filter Tampilkan Semua Pasien) untuk menampilkan data pasien.')
            ->emptyStateIcon('heroicon-o-magnifying-glass')
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('no_rm')
                    ->label('Rekam Medis')
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->formatStateUsing(fn($record) => collect([
                        $record->gelar_depan,
                        $record->nama,
                        $record->gelar_belakang,
                    ])->filter()->join(' ')),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d/m/Y'),

                TextColumn::make('tempatLahir.name')
                    ->label('Tempat Lahir')
                    ->searchable(),

                TextColumn::make('norm_manual')
                    ->label('Nomor Rekam Medis')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('agama')
                    ->label('Agama')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('unitEksternal.nama')
                    ->label('Unit Eksternal')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_pasien')
                    ->label('Status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('semua_pasien')
                    ->label('Tampilkan Semua Pasien')
                    ->toggle()
                    ->query(fn (Builder $query) => $query),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
