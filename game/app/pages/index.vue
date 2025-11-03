<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const pageMeta = ref({
  title: 'Herní centrum',
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

useHead({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.meta_description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href: useRuntimeConfig().public.appUrl + (locale.value !== 'cs' ? `/${locale.value}` : ''),
    },
  ],
});

const games = ref([
  {
    name: 'Kvízy',
    imageUrl: '/static/img/cards/kvizy.png',
    linkUrl: 'kvizy',
  },
  {
    name: 'Piškvorky',
    imageUrl: '/static/img/cards/piskvorky.png',
    linkUrl: '/piskvorky',
  },
  {
    name: 'Slepá mapa',
    imageUrl: '/static/img/cards/mapa.png',
    linkUrl: '/slepa-mapa',
  },
  {
    name: 'Aréna',
    imageUrl: '/static/img/cards/arena.png',
    linkUrl: '/arena',
  },
]);
</script>

<template>
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
    <HomeGameCard
      v-for="game in games"
      :key="game.name"
      class="col-span-1"
      :text="game.name"
      :image-url="game.imageUrl"
      :link-url="game.linkUrl"
    />
  </div>
</template>
