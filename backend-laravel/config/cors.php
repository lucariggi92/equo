<?php

/*
 * CORS (decisione D8): il browser accetta le risposte dell'API solo se la pagina che le chiede
 * è il sito React di EQUO. Gli indirizzi ammessi stanno in FRONTEND_URL (separati da virgola).
 * Solo GET e POST, solo le intestazioni che servono, nessun cookie.
 */
return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URL', 'http://localhost:5173'))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => ['X-Request-Id', 'Retry-After'],

    'max_age' => 3600,

    'supports_credentials' => false,

];
