<script setup lang="ts">
import { ref } from 'vue';
import { useRuntimeConfig, useHead } from '#app';

const { locale, t } = useI18n();

const pageMeta = ref({
  title: t('general.metaTitle'),
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
</script>

<template>
  <div>
    <HomeHero />

    <HomeNovelties />

    <HomeEvents />

    <HomeEventCategories />

    <HomeAbout />

    <HomeWhyUs />

    <HomeContact />
  </div>
</template>
