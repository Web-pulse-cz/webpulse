<script setup lang="ts">
import { computed } from 'vue';
import type { PostCategory } from '~~/types/PostCategory';

export interface BlogCategoryProps {
  category: PostCategory;
  index: number; // Přidali jsme index pro bezpečné střídání barev
}
const props = defineProps<BlogCategoryProps>();

const localePath = useLocalePath();

// Definice vizuálních variant z tvého návrhu
const themes = [
  {
    wrapper: 'border-transparent bg-turquoise/10 hover:border-turquoise hover:bg-turquoise/20',
    blob: 'bg-turquoise text-white group-hover:rotate-6',
  },
  {
    wrapper: 'border-transparent bg-primary/10 hover:border-primary hover:bg-primary/20',
    blob: 'bg-primary text-white group-hover:-rotate-6',
  },
  {
    wrapper: 'border-transparent bg-sunny/20 hover:border-sunny hover:bg-sunny/40',
    blob: 'bg-sunny text-deep-blue group-hover:rotate-12',
  },
  {
    wrapper: 'border-transparent bg-deep-blue/5 hover:border-deep-blue hover:bg-deep-blue/10',
    blob: 'bg-deep-blue text-white group-hover:-rotate-12',
  }
];

// Vybere téma podle indexu a zajistí zacyklení (0, 1, 2, 3, 0, 1...)
const activeTheme = computed(() => themes[props.index % themes.length]);

// Volitelné: Bezpečnostní mapování ikon podle slugu kategorie,
// pokud ikonu nemáš rovnou v databázi v typu PostCategory
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
      class="group flex flex-col items-center rounded-3xl border-2 p-8 text-center transition-all"
      :class="activeTheme.wrapper"
  >
    <div
        class="mb-4 flex size-16 items-center justify-center rounded-blob shadow-lg transition-all group-hover:scale-110"
        :class="activeTheme.blob"
    >
      <span class="material-symbols-outlined text-3xl">{{ categoryIcon }}</span>
    </div>
    <span class="text-lg font-black text-deep-blue">{{ category.name }}</span>
  </NuxtLink>
</template>