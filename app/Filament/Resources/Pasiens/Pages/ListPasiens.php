<?php

namespace App\Filament\Resources\Pasiens\Pages;

use App\Filament\Resources\Pasiens\PasienResource;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListPasiens extends ListRecords
{
    protected static string $resource = PasienResource::class;

    #[Url]
    public ?int $pasien_id = null;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
