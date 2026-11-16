<?php

namespace App\Filament\Resources\Richieste\Pages;

use App\Filament\Resources\Richieste\RichiestaResource;
use Filament\Resources\Pages\ListRecords;

class ElencoRichieste extends ListRecords
{
    protected static string $resource = RichiestaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
