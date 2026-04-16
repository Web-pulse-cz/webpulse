<script setup lang="ts">
import type { PostCategory } from '~~/types/PostCategory';

export interface BlogCategoryHeadingProps {
  category: PostCategory;
}

const props = defineProps<BlogCategoryHeadingProps>();
const { category } = props;
</script>

<template>
  <div
    class="relative z-10 mx-auto flex w-full max-w-4xl flex-col items-center justify-center py-8 text-center md:py-12"
  >
    <span
      class="mb-6 inline-block rounded-full bg-forest/10 px-5 py-2 text-sm font-semibold uppercase tracking-widest text-forest"
    >
      Kategorie
    </span>

    <h1 class="mb-6 font-display text-4xl font-bold leading-tight text-earth md:text-5xl">
      {{ category.name }}
    </h1>

    <p v-if="category.description" class="mt-4 max-w-2xl text-lg text-earth-light md:text-xl">
      {{ category.description }}
    </p>
  </div>
</template>
