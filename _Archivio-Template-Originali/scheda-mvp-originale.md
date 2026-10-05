# Template - Scheda MVP

Compila i campi e usa l'AI con il prompt di sfida per validare i confini.
Mantieni la definizione di MVP a una frase sola.

## Definizione MVP

Una frase che dice cosa costruisco, per chi, e quale nucleo del problema risolve.

MVP:
> (compila in una frase)

Scope da 2 lab:
> (cosa entra realisticamente in 2 lab di build con Lovable + Supabase)

## Prompt di sfida (da incollare in Gemini)

```txt
Ruolo: Sei un PM scettico che aiuta un founder a non far esplodere l'MVP.
Contesto: [incolla qui la tua definizione di MVP + target + scope: 2 lab di build].
Obiettivo: Elenca 5 feature che sembrerebbero utili ma che allargherebbero
           lo scope oltre 2 lab.
Vincoli: max 5 feature; 1 riga di perche' allargano lo scope;
         non proporre feature gia' dentro l'MVP; non inventare numeri.
Formato: elenco numerato con "Feature" e "Perche' allarga".
```

## Non-goals

Mappa qui l'output del prompt di sfida. Decidi tu per ciascuna feature se e' un non-goal o un post-MVP.

| Feature | Non-goal | Post-MVP | Perche' |
| --- | :---: | :---: | --- |
| | | | |
| | | | |
| | | | |
| | | | |
| | | | |

3 non-goals espliciti (feature concrete che non fai affatto):

1.
2.
3.

## User flow essenziale

Il percorso piu' breve dall'apertura al risultato. Una linea, non un albero.

```txt
[Schermata 1] -> [Schermata 2] -> [Schermata 3] -> [Schermata 4]
```

User flow:
> (compila)

## Mappa delle schermate

Una riga per schermata con il suo scopo. Se non sai lo scopo, la schermata non serve.

| Schermata | Scopo | Priorita' |
| --- | --- | :---: |
| | | |
| | | |
| | | |
| | | |

## 3 schermate chiave

Le 3 schermate che coprono il percorso felice: inizio, azione principale, esito.

| Ruolo | Schermata |
| --- | --- |
| Inizio | |
| Azione principale | |
| Esito | |

## Nota di decisione

Cosa hai accettato, modificato o rifiutato dell'output del prompt di sfida, e perche'.

| Output AI | Accettato / Modificato / Rifiutato | Perche' |
| --- | --- | --- |
| | | |
| | | |
