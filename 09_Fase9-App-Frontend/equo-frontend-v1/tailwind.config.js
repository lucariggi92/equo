/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        night: '#0B0F19',
        steel: '#1A365D',
        steelhover: '#234574',
        line: '#2D4A7A',
        mint: '#38A169',
        minthover: '#2F855A',
        mintactive: '#276749',
        muted: '#A0AEC0',
        warn: '#ECC94B',
        danger: '#FC8181',
        disabled: '#2D3748',
        disabledtext: '#718096',
      },
      borderRadius: {
        card: '12px',
        ctl: '10px',
      },
      minHeight: {
        tap: '48px',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
