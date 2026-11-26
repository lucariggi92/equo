import './ListRow.css';

export default function ListRow({ label, sub, selected, onClick }) {
  return (
    <button type="button" onClick={onClick} aria-pressed={Boolean(selected)} className={`row${selected ? ' row-selected' : ''}`}>
      <span className="row-main">
        <span className="row-label">{label}</span>
        {sub && <span className="row-sub">{sub}</span>}
      </span>
      {/* freccia, oppure la spunta se la voce è quella scelta */}
      <span className="row-arrow" aria-hidden="true">
        <svg width={selected ? 16 : 20} height={selected ? 16 : 20} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={selected ? 3 : 2} strokeLinecap="round" strokeLinejoin="round">
          <path d={selected ? 'M5 12l5 5 9-10' : 'M9 6l6 6-6 6'} />
        </svg>
      </span>
    </button>
  );
}
