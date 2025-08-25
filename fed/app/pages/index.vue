<script setup lang="ts">
import { useI18n } from 'vue-i18n';

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
    <BaseHeading type="h1"> 1000 KM1 loading...</BaseHeading>
    <BaseCountdown class="mb-6" />
    <div class="mt-6" aria-hidden="true">
      <div class="overflow-hidden rounded-full bg-white/10 ring-1 ring-sky-600/10">
        <div
          class="inline-flex h-7 items-center justify-center rounded-full bg-sky-500"
          style="width: 26.9%"
        >
          <span class="text-xs">0 z 1000</span>
        </div>
      </div>
      <div class="mt-6 hidden grid-cols-2 text-sm font-medium text-gray-400 ring-sky-600 sm:grid">
        <div class="text-sky-400">
          <p>Builder letní akce</p>
          <NuxtImg src="/img/builder.jpg" alt="Builder logo" class="mt-2 h-6 w-auto" />
        </div>
        <div class="text-right font-bold text-sky-950">
          <p>Free enterprise day 1. 11. 2025</p>
          <NuxtImg src="/img/fed.jpeg" alt="FED logo" class="mt-2 inline h-6 w-auto" />
        </div>
      </div>
    </div>
  </div>
</template>
