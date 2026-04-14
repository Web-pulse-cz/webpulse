/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require('@tailwindcss/forms')],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#FF6B6B', // Bright Coral
        turquoise: '#4ECDC4',
        sunny: '#FFE66D',
        'deep-blue': '#1A535C',
        'background-light': '#F7FFF7',
        'text-main': '#1A535C',
      },
      fontFamily: {
        display: ['Newsreader', 'serif'],
        sans: ['Noto Sans', 'sans-serif'],
      },
      borderRadius: {
        blob: '30% 70% 70% 30% / 30% 30% 70% 70%',
        portal: '2rem 2rem 5rem 2rem',
      },
    },
  },
};
