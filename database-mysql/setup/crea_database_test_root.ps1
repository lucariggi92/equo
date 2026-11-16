# EQUO - crea il database di prova equo_test e lo affida a equo_schema (task L2).
# Chiede la password di root di MySQL 8.4: va digitata qui, non viene salvata.
$mysql  = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
$script = (Join-Path $PSScriptRoot '02_database_test.sql') -replace '\\', '/'

if (-not (Test-Path $script)) { Write-Host "Manca $script" -ForegroundColor Red; exit 1 }

Write-Host 'Inserisci la password di root di MySQL:' -ForegroundColor Cyan
& $mysql -u root -p -h 127.0.0.1 -P 3306 -e "source $script"

if ($LASTEXITCODE -eq 0) {
    Write-Host 'FATTO: database equo_test creato, equo_schema puo costruirlo.' -ForegroundColor Green
} else {
    Write-Host 'ERRORE: controlla la password di root e riprova.' -ForegroundColor Red
}
