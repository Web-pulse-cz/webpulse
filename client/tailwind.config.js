/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require("@tailwindcss/forms")],
  darkMode: false,
  theme: {
    extend: {
      colors: {
        primary: "#083344",
        secondary: "#164e63",
        light: "#06b6d4",
        danger: "#ef4444",
      },
      fontFamily: {
        sans: ["Montserrat", "sans-serif"],
      },
    },
  },
};
