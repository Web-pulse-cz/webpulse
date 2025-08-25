<script setup lang="ts">
import type { FaqCategory } from '~/types/FaqCategory';

const { t, locale } = useI18n();
const localePath = useLocalePath();

interface categoryProps {
  category: FaqCategory;
}

const props = defineProps<categoryProps>();
</script>

<template>
  <NuxtLink
    :to="
      localePath({
        name: 'faq-category-id-slug',
        params: { id: props.category.id, slug: props.category.slug },
      })
    "
    class="flex aspect-square items-center justify-center rounded-lg p-2 shadow transition-all duration-1000 hover:bg-gray-50 hover:shadow-lg hover:shadow-redShadow"
  >
    <BasePropsHeading type="h5">
      {{ category.name }}
    </BasePropsHeading>
  </NuxtLink>
</template>
