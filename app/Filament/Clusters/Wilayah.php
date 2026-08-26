<?php

namespace App\Filament\Clusters;

use App\Filament\Clusters\Wilayah\Pages\Districts;
use App\Filament\Clusters\Wilayah\Pages\Provinces;
use App\Filament\Clusters\Wilayah\Pages\Regencies;
use App\Filament\Clusters\Wilayah\Pages\Villages;
use BackedEnum;
use Filament\Clusters\Cluster;
use UnitEnum;

class Wilayah extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Wilayah';

    protected static ?string $title = 'Data Wilayah Indonesia';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Provinces::canAccess()
            || Regencies::canAccess()
            || Districts::canAccess()
            || Villages::canAccess();
    }
}
