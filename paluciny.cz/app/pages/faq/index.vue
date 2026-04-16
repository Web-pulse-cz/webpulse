<script setup lang="ts">
import { useApi } from '~/../app/composables/useApi';

const { t, locale } = useI18n();
const api = useApi();

const pageMeta = ref({
  title: t('faq.title'),
  description: t('faq.metaDescription'),
  meta_title: t('faq.metaTitle'),
  meta_description: t('faq.metaDescription'),
});

useAsyncData('faqCategories', () => api.faq.categories(locale.value));

const { data: faqsData } = useAsyncData('faqList', () => api.faq.faqs(locale.value));

defineEmits<{
  (e: 'select', categoryId: number | null): void;
}>();

defineProps<CategoryListProps>();

interface CategoryListProps {
  selectedCategoryId?: number | null;
}

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
