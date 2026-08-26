<?php

namespace App\Filament\Resources\UnitEksternals;

use App\Filament\Resources\UnitEksternals\Pages\CreateUnitEksternal;
use App\Filament\Resources\UnitEksternals\Pages\EditUnitEksternal;
use App\Filament\Resources\UnitEksternals\Pages\ListUnitEksternals;
use App\Filament\Resources\UnitEksternals\RelationManagers\SubUnitsRelationManager;
use App\Filament\Resources\UnitEksternals\Schemas\UnitEksternalForm;
use App\Filament\Resources\UnitEksternals\Tables\UnitEksternalsTable;
use App\Models\UnitEksternal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnitEksternalResource extends Resource
{
    protected static ?string $model = UnitEksternal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
    protected static string|\UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $modelLabel = 'Unit Kerja';

    protected static ?string $pluralModelLabel = 'Daftar Unit Kerja';

    protected static ?string $navigationLabel = 'Unit Kerja';

    public static function form(Schema $schema): Schema
    {
        return UnitEksternalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitEksternalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubUnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitEksternals::route('/'),
            'create' => CreateUnitEksternal::route('/create'),
            'edit' => EditUnitEksternal::route('/{record}/edit'),
        ];
    }
}
