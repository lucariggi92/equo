# EQUO - funzioni comuni a backup.ps1 e ripristina.ps1 (si include con: . "$PSScriptRoot\comune.ps1")
# Usa l'utente equo_app tramite equo_app.local.cnf: nessuna password nei comandi o nei file su git.

$Script:BinMysql = 'C:\Program Files\MySQL\MySQL Server 8.4\bin'
$Script:Mysql    = Join-Path $BinMysql 'mysql.exe'
$Script:Dump     = Join-Path $BinMysql 'mysqldump.exe'
$Script:DbDir    = Split-Path $PSScriptRoot -Parent
$Script:Cnf      = Join-Path $DbDir 'equo_app.local.cnf'
$Script:BackupDir = Join-Path $DbDir 'backup'

function Test-Requisiti {
    foreach ($f in @($Mysql, $Dump, $Cnf)) {
        if (-not (Test-Path $f)) { throw "Manca $f" }
    }
}

function Invoke-Sql([string]$Database, [string]$Query) {
    # restituisce le righe del risultato, senza intestazioni, separate da tabulazione
    $out = & $Mysql "--defaults-extra-file=$Cnf" --default-character-set=utf8mb4 -N -B -e $Query $Database
    if ($LASTEXITCODE -ne 0) { throw "Errore MySQL sulla query: $Query" }
    return $out
}

function Get-Inventario([string]$Database) {
    # righe esatte di ogni tabella e nomi delle procedure: servono a confrontare backup e ripristino
    $tabelle = @(Invoke-Sql $Database "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' ORDER BY table_name")
    $righe = @()
    if ($tabelle.Count -gt 0) {
        $q = ($tabelle | ForEach-Object { "SELECT '$_', COUNT(*) FROM ``$_``" }) -join ' UNION ALL '
        $righe = @(Invoke-Sql $Database $q | ForEach-Object { "tabella`t$_" })
    }
    $proc = @(Invoke-Sql $Database "SELECT routine_name FROM information_schema.routines WHERE routine_schema = DATABASE() ORDER BY routine_name" |
              ForEach-Object { "procedura`t$_" })
    return @($righe + $proc)
}
