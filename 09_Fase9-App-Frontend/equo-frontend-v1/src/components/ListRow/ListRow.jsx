import './ListRow.css';

export default function ListRow({ label, sub, selected, onClick }) {
  return (
    <button onClick={onClick} className={`row${selected ? ' row-selected' : ''}`}>
      <span className="row-main">
        <span className="row-label">{label}</span>
        {sub && <span className="row-sub">{sub}</span>}
      </span>
      <span className="row-arrow">›</span>
    </button>
  );
}
