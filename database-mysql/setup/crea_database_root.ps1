# EQUO - crea il database `equo` e l'utente `equo_app` (da eseguire una volta).
# Chiede la password di root di MySQL 8.4: va digitata qui, non viene salvata.
$mysql  = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
$script = (Join-Path $PSScriptRoot '00_crea_database.local.sql') -replace '\\', '/'

if (-not (Test-Path $script)) { Write-Host "Manca $script" -ForegroundColor Red; exit 1 }

Write-Host 'Inserisci la password di root di MySQL:' -ForegroundColor Cyan
& $mysql -u root -p -h 127.0.0.1 -P 3306 -e "source $script"

if ($LASTEXITCODE -eq 0) {
    Write-Host 'FATTO: database equo e utente equo_app creati.' -ForegroundColor Green
} else {
    Write-Host 'ERRORE: nulla di creato. Controlla la password e riprova.' -ForegroundColor Red
}
