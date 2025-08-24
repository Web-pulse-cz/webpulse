<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { MinusSmallIcon, PlusSmallIcon } from '@heroicons/vue/24/outline';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { useApi } from '~/composables/useApi';
import { useAsyncData } from '#app';

const { t, locale } = useI18n();
const route = useRoute();
const localePath = useLocalePath();
const api = useApi();

const pageMeta = ref({
  title: t('faq.title'),
  description: t('faq.meta_description'),
  meta_title: t('faq.meta_title'),
  meta_description: t('faq.meta_description'),
  id: route.params.id,
  slug: route.params.slug,
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData(`faqCategories-${route.params.id}`, () => api.faq.categories(locale.value));

const {
  data: categoryData,
  status: categoryStatus,
  error: categoryError,
  pending: categoryPending,
} = useAsyncData(`faqCategory-${route.params.id}`, () =>
  api.faq.categoryDetail(route.params.id, locale.value).then((data) => {
    pageMeta.value.title = data.name;
    pageMeta.value.meta_title = data.meta_title || data.name;
    pageMeta.value.meta_description = data.meta_description || data.description;
    return data;
  }),
);

const breadcrumbs = computed(() => {
  return [
    { name: t('faq.title'), link: localePath('/faq'), current: false },
    { name: pageMeta.value.title, link: '', current: true },
  ];
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
        `/faq/${t('canonical.category')}/${pageMeta.value.id}/${pageMeta.value.slug}`,
    },
  ],
});
</script>

<template>
  <div>
    <LayoutBreadcrumbs :links="breadcrumbs" />
    <LayoutContainerTwoCols :links="categoriesData" path="faq-category-id-slug">
      <div class="mb-8 flex items-center justify-between">
        <BasePropsHeading type="h1" color="brand">
          {{ pageMeta.title }}
        </BasePropsHeading>
        <NuxtLink
          :to="localePath('/faq')"
          class="mb-8 inline-flex h-7 w-7 items-center justify-center rounded-full transition-colors duration-300 hover:bg-gray-100"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"
            />
          </svg>
        </NuxtLink>
      </div>

      <FaqList v-if="categoryData?.faqs" :faqs="categoryData.faqs" />
    </LayoutContainerTwoCols>
  </div>
</template>
