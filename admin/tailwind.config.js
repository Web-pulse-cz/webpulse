/** @type {import('tailwindcss').Config} */

// Semantic token helper — values live as RGB channels in CSS variables
// (see app/assets/css/style.css :root), so opacity modifiers like bg-primary-600/50 work.
const token = (name) => `rgb(var(--color-${name}) / <alpha-value>)`;
const scale = (name) =>
  Object.fromEntries(
    [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].map((step) => [
      step,
      token(`${name}-${step}`),
    ]),
  );

export default {
  plugins: [require('@tailwindcss/forms')],
  // Dark mode intentionally not enabled yet ('class' = no dark: variant applies unless .dark is set).
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // --- Semantic design tokens (preferred) ---
        primary: { DEFAULT: token('primary-600'), ...scale('primary') },
        background: token('background'),
        foreground: token('foreground'),
        surface: {
          DEFAULT: token('surface'),
          subtle: token('surface-subtle'),
          inverted: token('surface-inverted'),
        },
        muted: {
          DEFAULT: token('muted'),
          foreground: token('muted-foreground'),
        },
        border: token('border'),

        // --- Legacy colors ---
        // @deprecated — keep for backwards compatibility only, do not use in new code.
        // Prefer semantic tokens above or plain Tailwind palette (indigo/slate/emerald/red/amber).
        primaryCustom: '#0c4a6e',
        primaryLight: '#075985',
        secondary: '#7dd3fc',
        secondaryLight: '#bae6fd',
        dark: '#0f172a',
        light: '#cbd5e1',
        success: '#16a34a',
        successLight: '#22c55e',
        warning: '#ca8a04',
        warningLight: '#eab308',
        danger: '#dc2626',
        dangerLight: '#ef4444',
        grayDark: '#111827',
        grayCustom: '#6b7280',
        grayLight: '#d1d5db',
      },
      fontSize: {
        xxs: '0.625rem', // 10px
        xs: '0.75rem', // 12px
        '2xl': '1.5rem', // 24px
        '3xl': '1.875rem', // 30px
        '4xl': '2.25rem', // 36px
        '5xl': '3rem', // 48px
      },
    },
  },
};
