import svgLoader from 'vite-svg-loader';

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
        baseUrl: process.env.API_URL ?? 'https://api.web-pulse.cz/',
        mode: 'token',
        // login() při existující session přesměruje na onLogin místo vyhození
        // "User is already authenticated" (to se v UI zobrazovalo jako neplatné údaje)
        redirectIfAuthenticated: true,
        redirect: {
          onLogin: '/',
          onLogout: '/login',
          onAuthOnly: '/login',
          onGuestOnly: '/',
        },
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
    '@nuxt/image',
    '@vee-validate/nuxt',
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
  ],
  devtools: { enabled: false },

  app: {
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover',
      title: 'Web Pulse',
      titleTemplate: '%s | Web Pulse',
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
    '/kontakty/nastenka': { redirect: '/nastenka' },
    '/api/**': {
      proxy: `${process.env.API_URL ?? 'https://api.web-pulse.cz/'}/api/**`,
    },
    '/content/**': {
      proxy: `${process.env.API_URL ?? 'https://api.web-pulse.cz/'}/content/**`,
    },
  },
  compatibilityDate: '2024-11-01',

  vite: {
    plugins: [svgLoader()],
  },

  eslint: {
    config: {
      stylistic: {
        indent: 'tab',
        semi: true,
      },
    },
  },
});
