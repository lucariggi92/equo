<?php

namespace App\Filament\Resources\Modelli\Pages;

use App\Filament\Resources\Modelli\ModelloResource;
use Filament\Resources\Pages\ListRecords;

class ElencoModelli extends ListRecords
{
    protected static string $resource = ModelloResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
