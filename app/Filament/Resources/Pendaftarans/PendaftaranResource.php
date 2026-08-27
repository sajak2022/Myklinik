<?php

namespace App\Filament\Resources\Pendaftarans;

use App\Filament\Resources\Pendaftarans\Pages\CreatePendaftaran;
use App\Filament\Resources\Pendaftarans\Pages\EditPendaftaran;
use App\Filament\Resources\Pendaftarans\Pages\ListPendaftarans;
use App\Filament\Resources\Pendaftarans\Pages\ViewPendaftaran;
use App\Filament\Resources\Pendaftarans\Schemas\PendaftaranForm;
use App\Filament\Resources\Pendaftarans\Schemas\PendaftaranInfolist;
use App\Filament\Resources\Pendaftarans\Tables\PendaftaransTable;
use App\Models\Pendaftaran;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PendaftaranResource extends Resource
{
    protected static ?string $model = Pendaftaran::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::ReportMedical;

    protected static ?string $modelLabel = 'Pengunjung';

    protected static ?string $pluralModelLabel = 'Pengunjung';

    protected static ?string $navigationLabel = 'Pengunjung';

    protected static ?string $slug = 'pengunjung';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PendaftaranForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PendaftaranInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PendaftaransTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPendaftarans::route('/'),
            'create' => CreatePendaftaran::route('/create'),
            'view'   => ViewPendaftaran::route('/{record}'),
            'edit'   => EditPendaftaran::route('/{record}/edit'),
        ];
    }
}

