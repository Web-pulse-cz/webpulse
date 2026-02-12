/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require('@tailwindcss/forms')],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        primary: '#1B211A',
        secondary: '#628141',
        green: '#8BAE66',
        beige: '#EBD5AB',
      },
      fontFamily: {
        default: ['Chakra Petch', 'sans-serif'],
      },
    },
  },
};
