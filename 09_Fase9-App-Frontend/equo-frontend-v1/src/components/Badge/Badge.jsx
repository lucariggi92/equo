import './Badge.css';

export default function Badge({ tone = 'ok', children }) {
  return <span className={`badge badge-${tone}`}>{children}</span>;
}
