<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();

const pageMeta = ref({
  title: t('general.metaTitle'),
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('categories', () => useApi().blog.categories(locale.value));

const {
  data: postsData,
  status: postsStatus,
  error: postsError,
  pending: postsPending,
} = useAsyncData('posts', () => useApi().blog.posts(locale.value));

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
    <BlogCategoryList :categories="categoriesData || []" />
    <BlogPostList :posts="postsData || []" />
  </div>
</template>
