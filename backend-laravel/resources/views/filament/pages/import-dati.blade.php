<x-filament-panels::page>
    <div style="line-height: 1.6">
        <p><strong>Come funziona.</strong> 1) Carichi il file Excel dei dati e parte una <em>prova</em>: ogni riga viene
            controllata (fonte, data, valori ammessi, affidabilità) e caricata in prova, poi si annulla tutto.
            2) Se la prova va bene, <em>confermi</em>: parte un backup del database e poi i dati entrano in un'unica
            operazione, o tutto o niente. Ogni passo finisce nel registro delle azioni.</p>
    </div>

    @if ($nomeFile)
        <p>
            File: <strong>{{ $nomeFile }}</strong> ·
            @if ($caricato)
                <span style="color: #15803d; font-weight: 600">dati caricati</span>
            @elseif ($provaRiuscita)
                <span style="color: #15803d; font-weight: 600">prova riuscita, niente salvato: puoi confermare</span>
            @else
                <span style="color: #b91c1c; font-weight: 600">prova con errori, niente salvato</span>
            @endif
        </p>
    @endif

    @if ($report)
        <pre style="white-space: pre-wrap; font-size: 12px; max-height: 70vh; overflow: auto; padding: 12px; border: 1px solid rgba(127,127,127,.3); border-radius: 8px">{{ $report }}</pre>
    @endif
</x-filament-panels::page>
