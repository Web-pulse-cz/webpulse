// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    [
      'nuxt-auth-sanctum',
      {
        ssr: true,
        nitro: {
          preset: 'node',
        },
        baseUrl: process.env.API_URL ?? 'https://api.martinhanzl.cz/',
        mode: 'token',
        endpoints: {
          csrf: '/sanctum/csrf-cookie',
          login: '/api/admin/auth/login',
          logout: '/api/admin/auth/logout',
          user: '/api/admin/auth/me',
        },
        csrf: {
          cookie: 'XSRF-TOKEN',
          header: 'X-XSRF-TOKEN',
        },
      },
    ],
    '@nuxt/eslint',
    [
      '@nuxt/image',
      {
        domains: ['martinhanzl.cz', 'api.martinhanzl.cz'],
        formats: ['webp', 'jpg', 'png', 'jpeg', 'svg'],
        provider: 'ipx',
      },
    ],
    '@vee-validate/nuxt',
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
    '@nuxtjs/i18n',
    'v-gsap-nuxt',
  ],

  plugins: ['~/plugins/formatPrice.client.ts'],
  devtools: { enabled: false },

  app: {
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1, shrink-to-fit=no',
      title: 'Martin Hanzl',
      titleTemplate: '%s | Martin Hanzl',
      htmlAttrs: {
        lang: 'cs',
      },
      link: [
        {
          rel: 'apple-touch-icon',
          sizes: '180x180',
          href: '/apple-touch-icon.png',
        },
        {
          rel: 'icon',
          type: 'image/png',
          sizes: '32x32',
          href: '/favicon-32x32.png',
        },
        {
          rel: 'icon',
          type: 'image/png',
          sizes: '16x16',
          href: '/favicon-16x16.png',
        },
        {
          rel: 'shortcut icon',
          type: 'image/x-icon',
          href: '/favicon.ico',
        },
      ],
    },
  },

  css: ['~/assets/css/style.css'],

  routeRules: {
    '/api/**': {
      proxy: `${process.env.API_URL ?? 'https://api.martinhanzl.cz/'}/api/**`,
    },
    '/content/**': {
      proxy: process.env.API_URL
        ? process.env.API_URL + '/content/**'
        : 'http://api.martinhanzl/content/**',
    },
    '/files/**': {
      proxy: process.env.API_URL
        ? process.env.API_URL + '/files/**'
        : 'http://api.martinhanzl/content/files/**',
    },
  },
  compatibilityDate: '2024-11-01',

  vite: {
    plugins: [require('vite-svg-loader')()],
  },

  eslint: {
    config: {
      stylistic: {
        indent: 'tab',
        semi: true,
      },
    },
  },

  i18n: {
    lazy: true,
    defaultLocale: 'cs',
    locales: [
      {
        code: 'cs',
        name: 'Čeština',
        file: 'cs.ts',
      },
      {
        code: 'sk',
        name: 'Slovenčina',
        file: 'sk.ts',
      },
      {
        code: 'en',
        name: 'English',
        file: 'en.ts',
      },
      {
        code: 'de',
        name: 'Deutsch',
        file: 'de.ts',
      },
      {
        code: 'pl',
        name: 'Polski',
        file: 'pl.ts',
      },
    ],
  },

  vgsap: {
    presets: [],
    breakpoint: 768,
    scroller: '',
    composable: true,
  },
});
