/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/views/**/*.php',
    './public/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        'ds-navy': {
          50:  '#eef4fb',
          100: '#dde8f6',
          500: '#2952a8',
          600: '#1e3d7a',
          700: '#162f5c',
          800: '#0f2044',
          900: '#0a1628',
          950: '#020d1a',
        },
        'ds-teal': {
          50:  '#edfaf7',
          100: '#d4f7ef',
          300: '#65e3c6',
          400: '#26d4ae',
          500: '#00c49a',
          600: '#009d7d',
        },
        'ds-slate': {
          50:  '#f8fafc',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94a3b8',
          500: '#64748b',
          600: '#475569',
          700: '#334155',
          800: '#1e293b',
          900: '#0f172a',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      borderRadius: {
        'ds-sm':  '6px',
        'ds-md':  '8px',
        'ds-lg':  '12px',
        'ds-xl':  '16px',
        'ds-2xl': '20px',
        'ds-3xl': '24px',
      },
    },
  },
  plugins: [],
}
