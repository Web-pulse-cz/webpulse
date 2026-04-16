import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  plugins: [forms],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#4f46e5',
        'primary-light': '#818cf8',
        'primary-dark': '#3730a3',
        secondary: '#0ea5e9',
        accent: '#f59e0b',
        surface: '#f8fafc',
        'text-primary': '#0f172a',
        'text-secondary': '#475569',
      },
      fontFamily: {
        display: ['Inter', 'sans-serif'],
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
};
