<?php

namespace App\Filament\Resources\Impostazioni\Pages;

use App\Filament\Resources\Impostazioni\ImpostazioneResource;
use Filament\Resources\Pages\ListRecords;

class ElencoImpostazioni extends ListRecords
{
    protected static string $resource = ImpostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
