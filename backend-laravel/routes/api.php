<?php

use App\Http\Controllers\Api\OpzioniController;
use App\Http\Controllers\Api\SaluteController;
use App\Http\Controllers\Api\VariantiController;
use App\Http\Controllers\Api\VerificaController;
use Illuminate\Support\Facades\Route;

/*
 * API per il sito React (decisione D8): quattro rotte, nient'altro.
 * Nessuna rotta modifica il catalogo; /verifica aggiunge solo la richiesta anonima.
 */

Route::middleware('throttle:letture')->group(function () {
    Route::get('/opzioni', OpzioniController::class);
    Route::get('/varianti', VariantiController::class);
    Route::get('/salute', SaluteController::class);
});

Route::post('/verifica', VerificaController::class)->middleware('throttle:verifica');
