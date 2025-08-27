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
    name: 'Kraje ČR',
    imageUrl:
      'https://media.canva.com/v2/image-resize/format:JPG/height:896/quality:92/uri:ifs%3A%2F%2FM%2F6cb22c7142484c0b937793553e92cd6f/watermark:F/width:1600?csig=AAAAAAAAAAAAAAAAAAAAANuCyJohDD7qCRxNG9yh9_0cfe5kwhGZ7OKYNKW-6_D2&exp=1756335301&osig=AAAAAAAAAAAAAAAAAAAAAL5omqzdpfps7zpvHP09VezFhMJenWI-2Ls6NJvAhLMC&signer=media-rpc&x-canva-quality=screen_2x',
    linkUrl: '/slepa-mapa/cr/kraje',
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
