# EQUO - esporta i dati del catalogo in seed/03_catalogo.sql (su git), per chi installa il progetto da zero.
#
# Uso, da qualsiasi cartella:
#   powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\esporta_catalogo.ps1
#
# Contiene solo i dati delle 18 tabelle del catalogo (auto, interventi, ricambi, prezzi con le loro fonti, ore,
# tariffe, ricarichi, valori di mercato, impostazioni). NON contiene: account dell'area riservata, sessioni,
# registro delle azioni, richieste anonime degli utenti (quote_requests), procedure (stanno in queries/).
# Le tabelle si creano prima con le migration di Laravel; questo file le riempie (le svuota prima).
# Da rilanciare dopo ogni import, se si vuole aggiornare la versione pubblicata.
param([string]$Database = 'equo')

$ErrorActionPreference = 'Stop'
. "$PSScriptRoot\comune.ps1"
Test-Requisiti

$tabelle = @('settings', 'regions', 'sources', 'brands', 'car_models', 'variants', 'interventions', 'components',
             'intervention_components', 'spare_parts', 'part_qualities', 'part_prices', 'price_samples',
             'reference_prices', 'labor_hours', 'labor_rates', 'markups', 'market_benchmarks')
$file = Join-Path $DbDir 'seed\03_catalogo.sql'
$tmp  = "$file.tmp"

# --no-create-info: solo i dati (la struttura viene dalle migration)
# --complete-insert: elenca le colonne, cosi' le colonne calcolate restano fuori e MySQL le ricalcola
# --skip-dump-date: il file cambia solo se cambiano i dati
# --skip-add-locks, --skip-disable-keys: niente LOCK TABLES / ALTER TABLE, che equo_import non ha il permesso di fare
& $Dump "--defaults-extra-file=$Cnf" --default-character-set=utf8mb4 --single-transaction --no-create-info `
    --complete-insert --skip-dump-date --no-tablespaces --set-gtid-purged=OFF --skip-triggers --hex-blob `
    --skip-add-locks --skip-disable-keys `
    "--result-file=$tmp" $Database @tabelle
if ($LASTEXITCODE -ne 0) {
    if (Test-Path $tmp) { Remove-Item $tmp }
    Write-Host 'ERRORE: esportazione non riuscita, nessun file cambiato.' -ForegroundColor Red
    exit 1
}

# prima dei dati: svuota le tabelle (in ordine inverso), cosi' il file si puo' ricaricare piu' volte
$intestazione = @(
    '-- EQUO - dati del catalogo (generato da maintenance/esporta_catalogo.ps1: non modificare a mano).',
    '-- Si carica dopo le migration di Laravel, con l''utente equo_import o equo_schema. Svuota e riempie 18 tabelle.',
    '-- Nessun dato personale: niente account, sessioni, registro, richieste degli utenti.',
    'SET NAMES utf8mb4;',
    'SET FOREIGN_KEY_CHECKS = 0;'
) + @($tabelle[($tabelle.Count - 1)..0] | ForEach-Object { "DELETE FROM ``$_``;" })
$corpo = Get-Content -Path $tmp -Encoding UTF8
# il dump di mysqldump comincia con righe di commento che contengono nome utente e host: si tolgono
$corpo = $corpo | Where-Object { $_ -notmatch '^-- (MySQL dump|Host:|Server version)' }
$utf8 = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllLines($file, [string[]]($intestazione + $corpo + 'SET FOREIGN_KEY_CHECKS = 1;'), $utf8)
Remove-Item $tmp

$kb = [math]::Round((Get-Item $file).Length / 1KB)
Write-Host "FATTO: $file ($kb KB, $($tabelle.Count) tabelle)" -ForegroundColor Green
