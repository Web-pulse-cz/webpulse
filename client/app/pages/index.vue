<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();

const isOpen = ref(false);

const pageMeta = ref({
  title: t('general.metaTitle'),
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

const carouselSlides = Array.from({ length: 3 }, (_, i) => ({
  title: t(`carousel.data.${i}.title`),
  titleColor: t(`carousel.data.${i}.titleColor`),
  description: t(`carousel.data.${i}.description`),
  img: t(`carousel.data.${i}.img`),
}));

const { data, status, error, pending } = useAsyncData('logos', () =>
  useApi().logo.logo(locale.value),
);

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
    <NuxtImg src="/static/img/hero.jpg" class="rounded-2xl" />
  </div>
</template>
