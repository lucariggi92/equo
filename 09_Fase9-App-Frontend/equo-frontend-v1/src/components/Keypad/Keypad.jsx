import './Keypad.css';

export default function Keypad({ onDigit, onBack, onComma }) {
  const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', ',', '0', '⌫'];
  return (
    <div className="keypad">
      {keys.map((k) => (
        <button
          key={k}
          className="key"
          onClick={() => {
            if (k === '⌫') onBack();
            else if (k === ',') onComma();
            else onDigit(k);
          }}
        >
          {k}
        </button>
      ))}
    </div>
  );
}
