<?php

namespace App\Filament\Resources\Registro\Pages;

use App\Filament\Resources\Registro\RegistroResource;
use Filament\Resources\Pages\ListRecords;

class ElencoRegistro extends ListRecords
{
    protected static string $resource = RegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
