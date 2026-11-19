<x-filament-panels::page>
    <p>
        Un backup automatico parte ogni notte alle 02:30. Si tengono i backup degli ultimi
        {{ config('equo.backup.conserva_giorni') }} giorni (e comunque gli ultimi 5).
        Il ripristino si fa solo dal server, con <code>database-mysql/maintenance/ripristina.ps1</code>.
    </p>

    {{ $this->table }}
</x-filament-panels::page>
