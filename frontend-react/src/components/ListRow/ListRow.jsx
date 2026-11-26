import './ListRow.css';

// Una voce da scegliere in un elenco.
// scheda: voce a scheda separata con il pallino di selezione a sinistra e la freccia sempre a destra
// (senza: riga semplice, con la spunta al posto della freccia quando è scelta).
export default function ListRow({ label, sub, selected, onClick, scheda = false }) {
  const spunta = selected && !scheda;
  return (
    <button
      type="button"
      onClick={onClick}
      aria-pressed={Boolean(selected)}
      className={`row${scheda ? ' row-scheda' : ''}${selected ? ' row-selected' : ''}`}
    >
      {scheda && <span className="row-radio" aria-hidden="true" />}
      <span className="row-main">
        <span className="row-label">{label}</span>
        {sub && <span className="row-sub">{sub}</span>}
      </span>
      {/* freccia, oppure la spunta se la voce è quella scelta */}
      <span className="row-arrow" aria-hidden="true">
        <svg width={spunta ? 16 : 20} height={spunta ? 16 : 20} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={spunta ? 3 : 2} strokeLinecap="round" strokeLinejoin="round">
          <path d={spunta ? 'M5 12l5 5 9-10' : 'M9 6l6 6-6 6'} />
        </svg>
      </span>
    </button>
  );
}
