// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    [
      'nuxt-auth-sanctum',
      {
        baseUrl: process.env.API_URL ?? 'https://web-pulse.cz/',
        mode: 'token',
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
        domains: ['martinhanzl.cz', 'web-pulse.cz'],
        formats: ['webp', 'jpg', 'png', 'jpeg', 'svg'],
        alias: { img: 'https://web-pulse.cz/content/images/' },
      },
    ],
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
      apiUrl: process.env.API_URL ?? 'https://web-pulse.cz',
      appUrl: process.env.APP_URL ?? 'https://web-pulse.cz',
      supabase: {
        url: 'https://tuesjhaxnsguyakzyflb.supabase.co',
        key: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR1ZXNqaGF4bnNndXlha3p5ZmxiIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTYzMTk4ODcsImV4cCI6MjA3MTg5NTg4N30.YpuybsHsN5sMBwL2axkbSIcmxM668MR6ymKAGcuiSCQ',
      },
    },
  },

  routeRules: {
    '/api/**': {
      proxy: `${process.env.API_URL ?? 'https://web-pulse.cz/'}/api/**`,
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
    pages: {
      'blog/index': {
        en: '/blog',
        cs: '/blog',
        sk: '/blog',
        de: '/blog',
        pl: '/blog',
      },
      'blog/category/[id]/[slug]/index': {
        en: '/blog/categories/[id]/[slug]',
        cs: '/blog/kategorie/[id]/[slug]',
        sk: '/blog/kategorie/[id]/[slug]',
        de: '/blog/kategorien/[id]/[slug]',
        pl: '/blog/kategorie/[id]/[slug]',
      },
      'blog/[id]/[slug]/index': {
        en: '/blog/posts/[id]/[slug]',
        cs: '/blog/clanky/[id]/[slug]',
        sk: '/blog/clanky/[id]/[slug]',
        de: '/blog/beitrage/[id]/[slug]',
        pl: '/blog/artykuly/[id]/[slug]',
      },
      'info/[id]/[slug]/index': {
        en: '/info/[id]/[slug]',
        cs: '/info/[id]/[slug]',
        sk: '/info/[id]/[slug]',
        de: '/info/[id]/[slug]',
        pl: '/info/[id]/[slug]',
      },
      'faq/index': {
        cs: '/faq',
        sk: '/faq',
        en: '/faq',
        de: '/faq',
        pl: '/faq',
      },
      'faq/category/[id]/[slug]/index': {
        cs: '/faq/kategorie/[id]/[slug]',
        sk: '/faq/kategorie/[id]/[slug]',
        en: '/faq/categories/[id]/[slug]',
        de: '/faq/kategorien/[id]/[slug]',
        pl: '/faq/kategorie/[id]/[slug]',
      },
      'review/index': {
        cs: '/reference',
        sk: '/referencie',
        en: '/reference',
        de: '/referenz',
        pl: '/odniesienie',
      },
      'review/category/[id]/[slug]/index': {
        cs: '/reference/kategorie/[id]/[slug]',
        sk: '/referencie/kategorie/[id]/[slug]',
        en: '/reference/categories/[id]/[slug]',
        de: '/referenz/kategorien/[id]/[slug]',
        pl: '/odniesienie/kategorie/[id]/[slug]',
      },
      'review/[id]/[slug]/index': {
        cs: '/reference/[id]/[slug]',
        sk: '/referencie/[id]/[slug]',
        en: '/reference/[id]/[slug]',
        de: '/referenz/[id]/[slug]',
        pl: '/odniesienie/[id]/[slug]',
      },
    },
  },

  vgsap: {
    presets: [],
    breakpoint: 768,
    scroller: '',
    composable: true,
  },
});
