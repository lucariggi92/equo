import './Card.css';

export default function Card({ children, title }) {
  return (
    <section className="card">
      {title && <div className="card-title">{title}</div>}
      {children}
    </section>
  );
}
