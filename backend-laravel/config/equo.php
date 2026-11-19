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

    /*
     * Area riservata (task L7).
     * gestione_indirizzi_ammessi: se non vuoto, l'area si apre solo da questi indirizzi o reti (EQUO_GESTIONE_IP, separati da virgola).
     * password_lunghezza_minima: per l'account amministratore.
     */
    'gestione_indirizzi_ammessi' => array_values(array_filter(array_map('trim', explode(',', (string) env('EQUO_GESTIONE_IP', ''))))),

    'password_lunghezza_minima' => 14,

    /*
     * Verifica in due passaggi (codice dall'app del telefono) per l'area riservata.
     * Decisione del PM del 16/11/2026: spenta per ora (EQUO_GESTIONE_DUE_FATTORI=false). Si riaccende con true,
     * senza cambiare il codice; da riaccendere prima di mettere l'area riservata su Internet.
     */
    'gestione_due_fattori' => (bool) env('EQUO_GESTIONE_DUE_FATTORI', true),

    /*
     * Backup (task L8): cartella fuori dalla parte pubblica del sito, programma mysqldump, giorni da conservare
     * (si tengono comunque gli ultimi 5). Backup automatico ogni notte alle 02:30 (routes/console.php).
     */
    'backup' => [
        'cartella' => env('EQUO_BACKUP_DIR', dirname(base_path()).DIRECTORY_SEPARATOR.'database-mysql'.DIRECTORY_SEPARATOR.'backup'),
        'mysqldump' => env('EQUO_MYSQLDUMP', 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqldump.exe'),
        'conserva_giorni' => (int) env('EQUO_BACKUP_GIORNI', 30),
    ],

    /*
     * Import del file Excel dal pannello (task L8): usa lo script Python database-mysql/import/importa_dati.py,
     * con l'utente MySQL equo_import. File massimo 10 MB.
     */
    'import' => [
        'python' => env('EQUO_PYTHON', 'python'),
        'script' => env('EQUO_IMPORT_SCRIPT', dirname(base_path()).DIRECTORY_SEPARATOR.'database-mysql'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.'importa_dati.py'),
        'mysql' => env('EQUO_MYSQL', 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'),
        'dimensione_massima_kb' => 10240,
    ],

];
