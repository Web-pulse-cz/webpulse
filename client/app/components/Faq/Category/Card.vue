<script setup lang="ts">
import type { FaqCategory } from '~/types/FaqCategory';

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
    class="flex aspect-square items-center justify-center rounded-xl border border-slate-200 p-2 transition-all duration-300 hover:border-primary hover:bg-slate-50 hover:shadow-md"
  >
    <BasePropsHeading type="h5">
      {{ category.name }}
    </BasePropsHeading>
  </NuxtLink>
</template>
