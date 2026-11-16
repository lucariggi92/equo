# EQUO - crea (o riallinea) i tre utenti MySQL separati: equo_api, equo_import, equo_schema (task L1).
# Chiede la password di root di MySQL 8.4: va digitata qui, non viene salvata.
# Usa 01_utenti_separati.local.sql (password vere, escluso da git).
$mysql  = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
$script = (Join-Path $PSScriptRoot '01_utenti_separati.local.sql') -replace '\\', '/'

if (-not (Test-Path $script)) { Write-Host "Manca $script" -ForegroundColor Red; exit 1 }

Write-Host 'Inserisci la password di root di MySQL:' -ForegroundColor Cyan
& $mysql -u root -p -h 127.0.0.1 -P 3306 -e "source $script"

if ($LASTEXITCODE -eq 0) {
    Write-Host 'FATTO: utenti equo_api, equo_import, equo_schema creati con i loro permessi.' -ForegroundColor Green
} else {
    Write-Host 'ERRORE: controlla la password di root e riprova.' -ForegroundColor Red
}
