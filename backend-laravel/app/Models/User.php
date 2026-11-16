<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Account dell'area riservata (decisione D8).
 *  - Si legge con l'utente MySQL equo_import: l'API per il sito React (equo_api) non vede questa tabella.
 *  - Entra nel pannello /gestione solo chi ha ruolo "amministratore" (il ruolo non si assegna da modulo: solo da comando).
 *  - Verifica in due passaggi con l'app del telefono; segreto e codici di recupero sono cifrati.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery, Notifiable;

    public const RUOLO_AMMINISTRATORE = 'amministratore';

    protected $connection = 'import';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'gestione' && $this->ruolo === self::RUOLO_AMMINISTRATORE;
    }
}
