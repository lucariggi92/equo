<?php

namespace App\Filament\Resources\Interventi\Pages;

use App\Filament\Resources\Interventi\InterventoResource;
use Filament\Resources\Pages\ListRecords;

class ElencoInterventi extends ListRecords
{
    protected static string $resource = InterventoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
