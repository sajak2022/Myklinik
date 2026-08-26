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

    public static function getNavigationLabel(): string
    {
        return 'Pasien';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pasien';
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
