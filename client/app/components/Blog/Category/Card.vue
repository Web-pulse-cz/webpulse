<script setup lang="ts">
import { computed } from 'vue';
import type { PostCategory } from '~~/types/PostCategory';

export interface BlogCategoryProps {
  category: PostCategory;
  index: number;
}
const props = defineProps<BlogCategoryProps>();

const localePath = useLocalePath();

// Volitelne: Bezpecnostni mapovani ikon podle slugu kategorie
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
    class="group flex flex-col items-center rounded-xl border border-slate-200 p-8 text-center transition-all hover:border-primary hover:shadow-md"
  >
    <div
      class="mb-4 flex size-16 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110"
    >
      <span class="material-symbols-outlined text-3xl">{{ categoryIcon }}</span>
    </div>
    <span class="font-semibold text-slate-900">{{ category.name }}</span>
  </NuxtLink>
</template>
