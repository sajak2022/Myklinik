<?php

namespace App\Filament\Resources\Pasiens;

use App\Filament\Resources\Pasiens\Pages\CreatePasien;
use App\Filament\Resources\Pasiens\Pages\EditPasien;
use App\Filament\Resources\Pasiens\Pages\ListPasiens;
use App\Filament\Resources\Pasiens\Pages\ViewPasien;
use App\Filament\Resources\Pasiens\Schemas\PasienForm;
use App\Filament\Resources\Pasiens\Schemas\PasienInfolist;
use App\Filament\Resources\Pasiens\Tables\PasiensTable;
use App\Models\Pasien;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PasienResource extends Resource
{
    protected static ?string $model = Pasien::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::UserHeart;

    protected static string|\UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function canGloballySearch(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Nonaktifkan global search untuk Dokter dan Perawat
        if ($user->hasRole(['Dokter', 'Perawat'])) {
            return false;
        }

        if ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat'])) {
            return false;
        }

        return true;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['no_rm', 'nama', 'norm_manual', 'nomor_kartu', 'nama_panggilan', 'alamat', 'kontaks.nomor_kontak'];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['kontaks']);
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return "[{$record->no_rm}] {$record->nama}" . ($record->nama_panggilan ? " ({$record->nama_panggilan})" : '');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var Pasien $record */
        $noHp = $record->kontaks?->first()?->nomor_kontak;

        return [
            'Tgl Lahir' => $record->tanggal_lahir ? \Carbon\Carbon::parse($record->tanggal_lahir)->format('d/m/Y') : '-',
            'No. HP'    => ! empty($noHp) ? $noHp : '-',
        ];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        return static::getUrl('index', ['pasien_id' => $record->id]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Pasien';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pasien';
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Sembunyikan menu Master Pasien di sidebar untuk Dokter dan Perawat
        if ($user->hasRole(['Dokter', 'Perawat'])) {
            return false;
        }

        if ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat'])) {
            return false;
        }

        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return PasienForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PasienInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PasiensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPasiens::route('/'),
            'create' => CreatePasien::route('/create'),
            'view' => ViewPasien::route('/{record}'),
            'edit' => EditPasien::route('/{record}/edit'),
        ];
    }
}
