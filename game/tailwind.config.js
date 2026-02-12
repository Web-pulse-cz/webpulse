/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require('@tailwindcss/forms')],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        primaryLight: '#FFB6C1',
        primary: '#FF69B4',
        primaryDark: '#C71585',
        secondaryLight: '#ADD8E6',
        secondary: '#1E90FF',
        secondaryDark: '#00008B',
        accentLight: '#90EE90',
        accent: '#32CD32',
        accentDark: '#006400',
        backgroundLight: '#FFFFFF',
        background: '#F0F0F0',
        backgroundDark: '#1A1A1A',
        textLight: '#000000',
        text: '#333333',
        textDark: '#FFFFFF',
        new: 'oklch(95.1% 0.026 236.824)',
        danger: 'oklch(93.6% 0.032 17.717)',
        warning: 'oklch(97.3% 0.071 103.193)',
        success: 'oklch(96.2% 0.044 156.743)',
      },
      fontFamily: {
        winky: ['Winky Rough', 'cursive'],
        sans: ['Comfortaa', 'sans-serif'],
        quicksand: ['Quicksand', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
    },
  },
};
