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
          href: 'https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
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
        'EsAtU1TeCVJacAf6GCK84G7GPI15uZjPWKDtcYf8kJaFNaF88UrQIgp5qpqQnWmfrN3Y7c3GZQKDIL2jC2M4A8LlT9gROxmpaPYwaOwXfrVUJCYzKkhzfQU8aKUMMGlA',
      supabase: {
        url: 'https://tuesjhaxnsguyakzyflb.supabase.co',
        key: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR1ZXNqaGF4bnNndXlha3p5ZmxiIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTYzMTk4ODcsImV4cCI6MjA3MTg5NTg4N30.YpuybsHsN5sMBwL2axkbSIcmxM668MR6ymKAGcuiSCQ',
      },
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
