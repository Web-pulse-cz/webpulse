/** @type {import("prettier").Config} */
const config = {
  singleQuote: true,
  printWidth: 100,
  tailwindConfig: './tailwind.config.js',
  plugins: ['prettier-plugin-tailwindcss'],
  endOfLine: 'auto',
};

export default config;
