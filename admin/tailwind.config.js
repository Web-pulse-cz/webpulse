/** @type {import('tailwindcss').Config} */
export default {
  plugins: [require('@tailwindcss/forms')],
  darkMode: false,
  theme: {
    extend: {
      colors: {
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
