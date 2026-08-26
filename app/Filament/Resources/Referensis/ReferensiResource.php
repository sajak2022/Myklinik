<?php

namespace App\Filament\Resources\Referensis;

use App\Filament\Resources\Referensis\Pages\CreateReferensi;
use App\Filament\Resources\Referensis\Pages\EditReferensi;
use App\Filament\Resources\Referensis\Pages\ListReferensis;
use App\Filament\Resources\Referensis\RelationManagers\DetailsRelationManager;
use App\Filament\Resources\Referensis\Schemas\ReferensiForm;
use App\Filament\Resources\Referensis\Tables\ReferensisTable;
use App\Models\Referensi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReferensiResource extends Resource
{
    protected static ?string $model = Referensi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string|\UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $modelLabel = 'Referensi';

    protected static ?string $pluralModelLabel = 'Daftar Referensi';

    protected static ?string $navigationLabel = 'Referensi';

    public static function form(Schema $schema): Schema
    {
        return ReferensiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReferensisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferensis::route('/'),
            'create' => CreateReferensi::route('/create'),
            'edit' => EditReferensi::route('/{record}/edit'),
        ];
    }
}
