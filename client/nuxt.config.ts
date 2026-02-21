// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    [
      'nuxt-auth-sanctum',
      {
        baseUrl: process.env.API_URL ?? 'https://api.web-pulse.cz/',
        mode: 'token',
        csrf: {
          cookie: 'XSRF-TOKEN',
          header: 'X-XSRF-TOKEN',
        },
      },
    ],
    '@nuxt/eslint',
    '@nuxt/image',
    '@vee-validate/nuxt',
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
    '@nuxtjs/i18n',
    'v-gsap-nuxt',
    'nuxt-toast',
  ],

  plugins: ['~/plugins/formatPrice.client.ts'],
  ssr: false,
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
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
        },
      ],
    },
  },

  css: ['~/assets/css/style.css'],
  runtimeConfig: {
    public: {
      apiUrl: process.env.API_URL ?? 'https://api.web-pulse.cz',
      appUrl: process.env.APP_URL ?? 'https://api.web-pulse.cz',
      siteHash:
        '8FdMaHTAizMcmODA6LlTIqi2uxl14GYUS6W4xH4eVYiWn2kGlmkSVZiHwWyp3eTfdR3KeluV5lFETLjz2zf2vblWmkaom3M7sDBh1nBcoMjVRSYnZ7hnv77PWXRw1tVy',
    },
  },

  routeRules: {
    '/api/**': {
      proxy: `${process.env.API_URL ?? 'https://api.web-pulse.cz/'}/api/**`,
    },
    '/content/**': {
      proxy: `${process.env.API_URL ?? 'https://api.web-pulse.cz/'}/content/**`,
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
    compilation: {
      strictMessage: false,
    },
    strategy: 'prefix_except_default',
    customRoutes: 'config',
    detectBrowserLanguage: true,
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

  image: {
    domains: ['https://api.web-pulse.cz/'],
    alias: {
      content: 'https://api.web-pulse.cz/content',
    },
  },

  vgsap: {
    presets: [],
    breakpoint: 768,
    scroller: '',
    composable: true,
  },
});
