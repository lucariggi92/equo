<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Registro delle azioni dell'area riservata (decisione D8).
 * Si può solo aggiungere: l'utente MySQL dell'app ha SELECT e INSERT, mai UPDATE o DELETE.
 * Nessuna chiave esterna verso users: il registro deve restare anche se un account viene tolto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Chi ha agito (NULL per i tentativi di accesso falliti)');
            $table->string('azione', 80)->comment('es. accesso, accesso_fallito, import_prova, import_confermato, impostazione_modificata, backup');
            $table->string('oggetto', 120)->nullable()->comment('Su cosa: nome del file, chiave dell\'impostazione...');
            $table->json('dettagli')->nullable()->comment('Valori prima e dopo, conteggi, messaggi');
            $table->enum('esito', ['riuscita', 'fallita']);
            $table->string('indirizzo_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('creato_il')->useCurrent();
            $table->index(['user_id', 'creato_il']);
            $table->index(['azione', 'creato_il']);
            $table->comment('Registro azioni dell\'area riservata: solo aggiunta, mai modifica o cancellazione');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_log');
    }
};
