import './ListRow.css';

export default function ListRow({ label, sub, selected, onClick }) {
  return (
    <button onClick={onClick} className={`row${selected ? ' row-selected' : ''}`}>
      <span className="row-main">
        <span className="row-label">{label}</span>
        {sub && <span className="row-sub">{sub}</span>}
      </span>
      <span className="row-arrow" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M9 6l6 6-6 6" />
        </svg>
      </span>
    </button>
  );
}
