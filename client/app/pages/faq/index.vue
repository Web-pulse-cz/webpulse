<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const localePath = useLocalePath();

const { t, locale } = useI18n();
const api = useApi();

const pageMeta = ref({
  title: t('faq.title'),
  description: t('faq.metaDescription'),
  meta_title: t('faq.metaTitle'),
  meta_description: t('faq.metaDescription'),
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('faqCategories', () => api.faq.categories(locale.value));

const {
  data: faqsData,
  status: faqsStatus,
  error: faqsError,
  pending: faqsPending,
} = useAsyncData('faqList', () => api.faq.faqs(locale.value));

const emit = defineEmits<{
  (e: 'select', categoryId: number | null): void;
}>();

const handleCategoryClick = (categoryId: number | null) => {
  emit('select', categoryId);
};

const props = defineProps<CategoryListProps>();

interface CategoryListProps {
  selectedCategoryId?: number | null;
}

const breadcrumbs = computed(() => {
  return [{ name: t('faq.title'), link: localePath('/faq'), current: false }];
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
        `/faq`,
    },
  ],
});
</script>

<template>
  <div>
    <LayoutContainer>
      <BasePropsHeading type="h2">{{ t('faq.title') }}</BasePropsHeading>
      <FaqList v-if="faqsData" :faqs="faqsData" />
    </LayoutContainer>
  </div>
</template>
