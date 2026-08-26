<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Clusters\Cluster;

class DetailKunjungan extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = TablerIcon::Stethoscope;

    protected static ?string $navigationLabel = 'Detail Kunjungan';

    protected static ?string $clusterBreadcrumb = 'Detail Kunjungan';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
