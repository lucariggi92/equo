<?php

/*
 * Messaggi di controllo in italiano (task L7). Solo quelli che EQUO usa; i messaggi specifici
 * dei campi dell'API stanno nelle loro FormRequest.
 */
return [
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_without' => 'Il campo :attribute è obbligatorio se manca :values.',
    'string' => 'Il campo :attribute deve essere un testo.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'confirmed' => 'Le due password non coincidono.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'boolean' => 'Il campo :attribute deve essere sì o no.',
    'in' => 'Il valore di :attribute non è tra quelli ammessi.',
    'exists' => 'Il valore di :attribute non esiste.',
    'between' => ['numeric' => 'Il campo :attribute deve essere tra :min e :max.'],
    'min' => [
        'string' => 'Il campo :attribute deve avere almeno :min caratteri.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
    ],
    'max' => [
        'string' => 'Il campo :attribute può avere al massimo :max caratteri.',
        'numeric' => 'Il campo :attribute può essere al massimo :max.',
    ],
    'password' => [
        'letters' => 'La password deve contenere almeno una lettera.',
        'mixed' => 'La password deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'La password deve contenere almeno un numero.',
        'symbols' => 'La password deve contenere almeno un simbolo (es. ! ? - _ . @ #).',
        'uncompromised' => 'Questa password compare tra quelle rubate note: scegline un\'altra.',
    ],

    'attributes' => [
        'password' => 'password',
        'email' => 'email',
        'nome' => 'nome',
    ],
];
