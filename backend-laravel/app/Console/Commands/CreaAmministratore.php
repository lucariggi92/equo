<?php

namespace App\Console\Commands;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crea l'account amministratore dell'area riservata (decisione D8). Non esiste una pagina "registrati":
 * l'account si crea solo da qui, sul server. La password si digita senza che compaia a schermo e non viene salvata
 * da nessuna parte in chiaro (solo la sua impronta cifrata). La verifica in due passaggi si attiva al primo accesso.
 */
#[Signature('equo:crea-amministratore')]
#[Description('Crea l\'account amministratore dell\'area riservata (chiede nome, email e password)')]
class CreaAmministratore extends Command
{
    public function handle(): int
    {
        $nome = (string) $this->ask('Nome');
        $email = (string) $this->ask('Email');
        $password = (string) $this->secret('Password (non compare mentre la scrivi)');
        $conferma = (string) $this->secret('Ripeti la password');

        $v = Validator::make(
            ['nome' => $nome, 'email' => $email, 'password' => $password, 'password_confirmation' => $conferma],
            [
                'nome' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:190'],
                'password' => ['required', 'confirmed', self::regolaPassword()],
            ],
            [
                'password.confirmed' => 'Le due password non coincidono.',
            ],
        );
        if ($v->fails()) {
            foreach ($v->errors()->all() as $errore) {
                $this->error($errore);
            }

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Esiste già un account con questa email. Per cambiare la password: php artisan equo:recupera-accesso');

            return self::FAILURE;
        }

        $utente = new User(['name' => $nome, 'email' => $email, 'password' => $password]);
        $utente->ruolo = User::RUOLO_AMMINISTRATORE;
        $utente->save();

        AdminAuditLog::create([
            'user_id' => $utente->id, 'azione' => 'account_creato', 'oggetto' => $email,
            'dettagli' => ['da' => 'comando sul server'], 'esito' => 'riuscita',
        ]);
        $this->info("Account creato per {$email}. Al primo accesso a /gestione ti verrà chiesto di attivare il codice dal telefono.");

        return self::SUCCESS;
    }

    /**
     * Almeno 14 caratteri, maiuscole e minuscole, numeri, simboli.
     * (Il controllo sulle password rubate note, ->uncompromised(), chiederebbe a un servizio esterno: non attivato.)
     */
    public static function regolaPassword(): Password
    {
        return Password::min(config('equo.password_lunghezza_minima'))->mixedCase()->numbers()->symbols();
    }
}
