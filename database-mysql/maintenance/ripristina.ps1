# EQUO - ripristino di un backup (task M9).
#
# Uso:
#   powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\ripristina.ps1                 # ultimo backup in equo
#   powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\ripristina.ps1 -File <dump.sql> -Database equo
#
# ATTENZIONE: svuota il database di destinazione (tabelle e procedure) e lo ricarica dal dump.
# Chiede di scrivere il nome del database per conferma; -Forza salta la domanda (per gli script).
# Alla fine confronta righe di ogni tabella e procedure con l'inventario salvato dal backup:
# esce con codice 0 se sono identici, 1 se c'e' una differenza.
param(
    [string]$File,
    [string]$Database = 'equo',
    [switch]$Forza
)

$ErrorActionPreference = 'Stop'
. "$PSScriptRoot\comune.ps1"
Test-Requisiti

if (-not $File) {
    $ultimo = Get-ChildItem $BackupDir -Filter '*.sql' | Sort-Object LastWriteTime -Descending | Select-Object -First 1
    if (-not $ultimo) { Write-Host "Nessun backup in $BackupDir" -ForegroundColor Red; exit 1 }
    $File = $ultimo.FullName
}
if (-not (Test-Path $File)) { Write-Host "Manca $File" -ForegroundColor Red; exit 1 }
$inv = $File -replace '\.sql$', '.inventario.txt'

Write-Host "Ripristino di $File nel database $Database" -ForegroundColor Cyan
if (-not $Forza) {
    $conferma = Read-Host "Il database $Database verra' svuotato. Scrivi il suo nome per confermare"
    if ($conferma -ne $Database) { Write-Host 'Annullato: nessuna modifica.' -ForegroundColor Yellow; exit 1 }
}

# 1. svuota la destinazione: procedure e tabelle (anche quelle che il dump non conosce)
$proc = @(Invoke-Sql $Database "SELECT routine_name FROM information_schema.routines WHERE routine_schema = DATABASE()")
foreach ($p in $proc) { Invoke-Sql $Database "DROP PROCEDURE IF EXISTS ``$p``" | Out-Null }
$tab = @(Invoke-Sql $Database "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()")
if ($tab.Count -gt 0) {
    $lista = ($tab | ForEach-Object { "``$_``" }) -join ', '
    Invoke-Sql $Database "SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS $lista; SET FOREIGN_KEY_CHECKS = 1;" | Out-Null
}
Write-Host "Database $Database svuotato ($($tab.Count) tabelle, $($proc.Count) procedure)."

# 2. carica il dump
$src = $File -replace '\\', '/'
& $Mysql "--defaults-extra-file=$Cnf" --default-character-set=utf8mb4 -e "source $src" $Database
if ($LASTEXITCODE -ne 0) { Write-Host 'ERRORE durante il caricamento del dump.' -ForegroundColor Red; exit 1 }

# 3. confronto con l'inventario del backup
$dopo = Get-Inventario $Database
if (-not (Test-Path $inv)) {
    Write-Host "Ripristino fatto, ma manca l'inventario ${inv}: confronto non possibile." -ForegroundColor Yellow
    $dopo | ForEach-Object { Write-Host "  $_" }
    exit 1
}
$prima = @(Get-Content $inv -Encoding UTF8 | Where-Object { $_ -ne '' })
$diff = Compare-Object $prima $dopo
if ($diff) {
    Write-Host 'DIFFERENZE tra backup e ripristino:' -ForegroundColor Red
    $diff | ForEach-Object { Write-Host ("  {0} {1}" -f $(if ($_.SideIndicator -eq '<=') { 'nel backup:  ' } else { 'ripristinato:' }), $_.InputObject) }
    exit 1
}
$nt = @($dopo | Where-Object { $_ -like 'tabella*' }).Count
$np = @($dopo | Where-Object { $_ -like 'procedura*' }).Count
Write-Host "FATTO: ripristino identico al backup ($nt tabelle con le stesse righe, $np procedure)." -ForegroundColor Green
exit 0
