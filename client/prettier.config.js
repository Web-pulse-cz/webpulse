/** @type {import("prettier").Config} */
const config = {
  singleQuote: true,
  printWidth: 100,
  tailwindConfig: './tailwind.config.ts',
  plugins: ['prettier-plugin-tailwindcss'],
  endOfLine: 'auto',
};

export default config;
