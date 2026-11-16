<?php

namespace App\Console\Commands;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Recupero dell'accesso all'area riservata, solo dal server (non esiste il recupero via email: decisione del 16/11/2026).
 * Cambia la password e, se richiesto, azzera la verifica in due passaggi (da riattivare al prossimo accesso).
 * Chiude anche tutte le sessioni aperte dell'account. Ogni uso finisce nel registro.
 */
#[Signature('equo:recupera-accesso {email : email dell\'account} {--azzera-due-fattori : toglie il codice dal telefono, da riattivare al prossimo accesso}')]
#[Description('Nuova password per l\'account amministratore ed eventuale azzeramento della verifica in due passaggi')]
class RecuperaAccesso extends Command
{
    public function handle(): int
    {
        $utente = User::where('email', $this->argument('email'))->first();
        if ($utente === null) {
            $this->error('Nessun account con questa email.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Nuova password (non compare mentre la scrivi)');
        $conferma = (string) $this->secret('Ripeti la nuova password');
        $v = Validator::make(['password' => $password, 'password_confirmation' => $conferma],
            ['password' => ['required', 'confirmed', CreaAmministratore::regolaPassword()]],
            ['password.confirmed' => 'Le due password non coincidono.']);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $errore) {
                $this->error($errore);
            }

            return self::FAILURE;
        }

        $utente->password = $password;
        $utente->setRememberToken(null);
        $azzera = (bool) $this->option('azzera-due-fattori');
        if ($azzera) {
            $utente->app_authentication_secret = null;
            $utente->app_authentication_recovery_codes = null;
        }
        $utente->save();
        DB::connection('import')->table('sessions')->where('user_id', $utente->id)->delete();

        AdminAuditLog::create([
            'user_id' => $utente->id, 'azione' => 'accesso_recuperato', 'oggetto' => $utente->email,
            'dettagli' => ['da' => 'comando sul server', 'due_fattori_azzerati' => $azzera], 'esito' => 'riuscita',
        ]);
        $this->info('Password cambiata e sessioni aperte chiuse.'.($azzera ? ' Al prossimo accesso riattiva il codice dal telefono.' : ''));

        return self::SUCCESS;
    }
}
