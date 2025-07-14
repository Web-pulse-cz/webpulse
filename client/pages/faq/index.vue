<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '@/composables/useApi';
import { useAsyncData } from '#app';

const localePath = useLocalePath();

const { t, locale } = useI18n();
const api = useApi();

const pageMeta = ref({
  title: t('faq.title'),
  description: t('faq.meta_description'),
  meta_title: t('faq.meta_title'),
  meta_description: t('faq.meta_description'),
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('faqCategories', () => api.faq.categories(locale.value));

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
    <LayoutContainerTwoCols :links="categoriesData" path="faq-category-id-slug">
      <BasePropsHeading type="h1" color="brand" margin-bottom="mb-8">
        {{ t('faq.title') }}
      </BasePropsHeading>
      <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        <FaqCategoryCard
          v-for="(category, index) in categoriesData"
          :key="index"
          :category="category"
          class="col-span-1"
        />
      </div>
    </LayoutContainerTwoCols>
  </div>
</template>
