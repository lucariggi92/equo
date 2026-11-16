<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Area riservata (task L7, decisione D8): ruolo dell'account e verifica in due passaggi con l'app del telefono.
 * Il segreto e i codici di recupero si salvano cifrati con la chiave dell'app (APP_KEY): chi legge il database non li vede.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ruolo', ['amministratore'])->nullable()->after('email')
                ->comment('Solo gli account con ruolo amministratore entrano nell\'area riservata');
            $table->text('app_authentication_secret')->nullable()->after('password')
                ->comment('Segreto della verifica in due passaggi (cifrato)');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret')
                ->comment('Codici di recupero della verifica in due passaggi (cifrati)');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ruolo', 'app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
