# EQUO - crea i database equo ed equo_test e l'utente equo_schema, che con le migration di Laravel crea le tabelle.
# Gli altri due utenti (equo_api, equo_import) si creano dopo le migration, con crea_utenti_root.ps1.
# Prima va lanciato genera_utenti.py, che scrive 01_utente_schema.local.sql con la password.
# Chiede la password di root di MySQL 8.4: va digitata qui, non viene salvata. Rieseguibile.
$mysql   = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
$db      = (Join-Path $PSScriptRoot '00_crea_database.sql') -replace '\\', '/'
$utenti  = (Join-Path $PSScriptRoot '01_utente_schema.local.sql') -replace '\\', '/'

if (-not (Test-Path $mysql))  { Write-Host "Manca $mysql (serve MySQL Server 8.4)" -ForegroundColor Red; exit 1 }
if (-not (Test-Path $utenti)) {
    Write-Host "Manca $utenti" -ForegroundColor Red
    Write-Host 'Lancia prima, dalla cartella del progetto: python database-mysql/setup/genera_utenti.py'
    exit 1
}

Write-Host 'Inserisci la password di root di MySQL:' -ForegroundColor Cyan
# un comando "source" per riga: sulla stessa riga, il client mysql non esegue il secondo file
& $mysql -u root -p -h 127.0.0.1 -P 3306 -e "source $db`nsource $utenti"

if ($LASTEXITCODE -eq 0) {
    Write-Host 'FATTO: database equo ed equo_test e utente equo_schema. Ora le migration di Laravel, poi crea_utenti_root.ps1.' -ForegroundColor Green
} else {
    Write-Host 'ERRORE: controlla la password di root e riprova.' -ForegroundColor Red
}
