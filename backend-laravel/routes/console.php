<?php

use Illuminate\Support\Facades\Schedule;

/*
 * Lavori pianificati di EQUO (task L8). Sul server serve un'operazione pianificata che lanci
 * "php artisan schedule:run" ogni minuto (cron su Linux, Utilità di pianificazione su Windows).
 */

// backup completo ogni notte alle 02:30, ora italiana; mai due insieme
Schedule::command('equo:backup')->dailyAt('02:30')->timezone('Europe/Rome')->withoutOverlapping();
