// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    [
      'nuxt-auth-sanctum',
      {
        baseUrl: process.env.API_URL ?? 'https://api.martinhanzl.cz/',
        mode: 'token',
        endpoints: {
          csrf: '/sanctum/csrf-cookie',
          login: '/api/auth/login',
          logout: '/api/auth/logout',
          user: '/api/auth/me',
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
        alias: { img: 'http://api.martinhanzl.cz/content/images/' },
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
      title: 'Inovujeme svět pasportizace',
      // titleTemplate: '%s | Martin Hanzl',
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
      apiUrl: process.env.API_URL ?? 'https://api.martinhanzl.cz',
      appUrl: process.env.APP_URL ?? 'https://chpp.cz',
    },
  },

  routeRules: {
    '/api/**': {
      proxy: `${process.env.API_URL ?? 'https://api.martinhanzl.cz/'}/api/**`,
    },
    '/files/**': {
      proxy: process.env.API_URL
        ? process.env.API_URL + '/files/**'
        : 'http://api.martinhanzl.cz/content/files/**',
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
    },
  },

  vgsap: {
    presets: [],
    breakpoint: 768,
    scroller: '',
    composable: true,
  },
});
