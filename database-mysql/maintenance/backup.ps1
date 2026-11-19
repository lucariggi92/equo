# EQUO - backup del database (task M9).
#
# Uso, da qualsiasi cartella:
#   powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\backup.ps1
#   powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\backup.ps1 -Database equo
#
# Crea in database-mysql\backup\ (cartella esclusa da git):
#   equo-AAAAMMGG-HHMM.sql            dump completo: tabelle, dati, procedure
#   equo-AAAAMMGG-HHMM.inventario.txt righe di ogni tabella e procedure presenti, per il controllo del ripristino
#
# Il dump non contiene CREATE DATABASE / USE: si puo' ripristinare in qualunque database (ripristina.ps1).
param([string]$Database = 'equo')

$ErrorActionPreference = 'Stop'
. "$PSScriptRoot\comune.ps1"
Test-Requisiti

$stamp = Get-Date -Format 'yyyyMMdd-HHmm'
$file  = Join-Path $BackupDir "$Database-$stamp.sql"
$inv   = Join-Path $BackupDir "$Database-$stamp.inventario.txt"

# --single-transaction: copia coerente senza bloccare le tabelle (InnoDB)
# --routines: include le procedure (calcola_stima, controlli_copertura, taratura...)
# --no-tablespaces: non serve il privilegio PROCESS, che gli utenti di EQUO non hanno
& $Dump "--defaults-extra-file=$Cnf" --default-character-set=utf8mb4 --single-transaction --routines --triggers `
    --no-tablespaces --set-gtid-purged=OFF --hex-blob "--result-file=$file" $Database
if ($LASTEXITCODE -ne 0) {
    if (Test-Path $file) { Remove-Item $file }
    Write-Host 'ERRORE: backup non riuscito, nessun file creato.' -ForegroundColor Red
    exit 1
}

$righe = Get-Inventario $Database
$righe | Set-Content -Path $inv -Encoding UTF8

$mb = [math]::Round((Get-Item $file).Length / 1MB, 2)
$nt = @($righe | Where-Object { $_ -like 'tabella*' }).Count
$np = @($righe | Where-Object { $_ -like 'procedura*' }).Count
Write-Host "FATTO: $file ($mb MB, $nt tabelle, $np procedure)" -ForegroundColor Green
Write-Host "Inventario: $inv"
