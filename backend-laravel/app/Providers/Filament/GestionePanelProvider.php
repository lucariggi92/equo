<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Accesso;
use App\Http\Middleware\IndirizziAmmessi;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Area riservata /gestione (decisione D8, task L7).
 *  - accesso con password; codice dall'app del telefono se EQUO_GESTIONE_DUE_FATTORI=true (oggi spento);
 *  - nessuna registrazione, nessun recupero password via email (si recupera da comando sul server);
 *  - solo gli account con ruolo amministratore (User::canAccessPanel);
 *  - facoltativo: solo da indirizzi ammessi (EQUO_GESTIONE_IP);
 *  - ogni accesso, tentativo fallito o bloccato finisce nel registro (App\Listeners\RegistraAccessi, pagina Accesso);
 *  - nessun font o script da siti esterni.
 */
class GestionePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('gestione')
            ->path('gestione')
            ->login(Accesso::class);

        // verifica in due passaggi: accesa o spenta da EQUO_GESTIONE_DUE_FATTORI (oggi spenta, decisione del PM del 16/11/2026)
        if (config('equo.gestione_due_fattori')) {
            $panel = $panel
                ->multiFactorAuthentication([AppAuthentication::make()->recoverable()])
                ->requiresMultiFactorAuthentication();
        }

        return $panel
            ->brandName('EQUO · gestione')
            ->colors(['primary' => Color::Blue])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                IndirizziAmmessi::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
