<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

const { t, locale } = useI18n();
const route = useRoute();
const localePath = useLocalePath();

const pageMeta = ref({
  title: t('faq.title'),
  description: t('faq.meta_description'),
  meta_title: t('faq.meta_title'),
  meta_description: t('faq.meta_description'),
  id: route.params.id,
  slug: route.params.slug,
});

const api = useApi();
const {
  data: pageData,
  status: pageStatus,
  error: pageError,
  pending: pagePending,
} = useAsyncData(`page-${route.params.id}`, () => api.page.page(route.params.id, locale.value));

const breadcrumbs = computed(() => {
  return [{ name: pageMeta.value.title, link: '', current: true }];
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
      href:
        useRuntimeConfig().public.appUrl +
        (locale.value !== 'cs' ? `/${locale.value}` : '') +
        `/${t('canonical.info')}/${pageMeta.value.id}/${pageMeta.value.slug}`,
    },
  ],
});
</script>

<template>
  <div>
    <LayoutContainer v-if="!pagePending && !pageError && pageData" class="space-y-12 py-24 pb-24">
      <BasePropsHeading type="h1" class="my-y-24">
        {{ pageData.name }}
      </BasePropsHeading>
      <div class="text-lg" v-html="pageData.text"></div>
    </LayoutContainer>
  </div>
</template>

<style>
ul {
  list-style: disc;
  padding-left: 60px;
  @apply text-sm md:text-base lg:text-lg;
}

p {
  @apply text-sm md:text-base lg:text-lg;
}
</style>
