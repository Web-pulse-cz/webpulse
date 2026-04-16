import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  plugins: [forms],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        forest: '#3D5A3E',
        'forest-light': '#5A7D5C',
        'forest-dark': '#2C4230',
        cream: '#F5F0E6',
        'cream-dark': '#E8E0D0',
        bark: '#8B6914',
        'bark-light': '#C4A35A',
        earth: '#3B3730',
        'earth-light': '#6B6560',
        leaf: '#7BA05B',
        moss: '#9CAF88',
      },
      fontFamily: {
        display: ['Playfair Display', 'serif'],
        sans: ['Nunito', 'sans-serif'],
      },
    },
  },
};
