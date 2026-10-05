import './SearchBar.css';

export default function SearchBar({ value, onChange, placeholder = 'Cerca...' }) {
  return (
    <input
      className="search"
      value={value}
      onChange={(e) => onChange(e.target.value)}
      placeholder={placeholder}
    />
  );
}
