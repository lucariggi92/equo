<?php

/*
 * Impostazioni di EQUO per l'API del sito React (task L5, decisione D8).
 */
return [

    /*
     * Nel form compaiono solo i modelli con car_models.info_completed = 1 (dati completi).
     * Durante lo sviluppo si può mostrare tutto mettendo EQUO_SOLO_MODELLI_COMPLETI=false nel file .env.
     * In produzione deve restare true.
     */
    'solo_modelli_completi' => (bool) env('EQUO_SOLO_MODELLI_COMPLETI', true),

    /*
     * Limiti di traffico per visitatore (decisione D8): valori iniziali, da rivedere con i dati veri.
     */
    'limiti' => [
        'letture_al_minuto' => 120,
        'verifiche_al_minuto' => 20,
        'verifiche_al_giorno' => 200,
    ],

    /*
     * Importo massimo accettato per un preventivo, in euro: oltre è quasi certamente un errore di battitura.
     */
    'preventivo_massimo' => 20000,

    /*
     * Sicurezza delle richieste (task L6).
     * forza_https: ogni richiesta non cifrata viene rimandata su HTTPS (GET) o rifiutata (il resto).
     * Di default è attivo in produzione e spento sul computer di sviluppo, che usa http://localhost.
     */
    'forza_https' => (bool) env('EQUO_FORZA_HTTPS', env('APP_ENV') === 'production'),

    /* Dimensione massima di una richiesta all'API: una verifica pesa poche centinaia di byte. */
    'dimensione_massima_richiesta' => 16 * 1024,

];
