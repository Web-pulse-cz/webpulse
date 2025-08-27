/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require('@tailwindcss/forms')],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        primary: '#083344',
        secondary: '#164e63',
        light: '#06b6d4',
        danger: '#ef4444',
        brand: '#e20e17',
        redShadow: '#ffede9',
        chppGray: '#F4F6FA',
        footerGray: '#263238',
        textBlack: '#263238',
        textDescription: '#717171',
        textWhiteFooter: '#F5F7FA',
      },
      fontFamily: {
        sans: ['Comfortaa', 'sans-serif'],
        quicksand: ['Quicksand', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
    },
  },
};
