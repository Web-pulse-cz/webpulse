<script setup lang="ts">
import { computed } from 'vue';
import type { PostCategory } from '~~/types/PostCategory';

export interface BlogCategoryProps {
  category: PostCategory;
  index: number;
}
const props = defineProps<BlogCategoryProps>();

const localePath = useLocalePath();

const iconMap: Record<string, string> = {
  lifestyle: 'spa',
  tech: 'memory',
  travel: 'flight_takeoff',
  food: 'restaurant',
  wellness: 'self_improvement',
  design: 'palette',
};

const categoryIcon = computed(() => iconMap[props.category.slug] || 'category');
</script>

<template>
  <NuxtLink
    :to="
      localePath({
        name: 'blog-category-id-slug',
        params: { id: category.id, slug: category.slug },
      })
    "
    class="group flex flex-col items-center rounded-xl border border-cream-dark p-8 text-center transition-all hover:border-forest hover:shadow-md"
  >
    <div
      class="mb-4 flex size-16 items-center justify-center rounded-lg bg-forest/10 text-forest transition-all group-hover:scale-110"
    >
      <span class="material-symbols-outlined text-3xl">{{ categoryIcon }}</span>
    </div>
    <span class="font-semibold text-earth">{{ category.name }}</span>
  </NuxtLink>
</template>
