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
    <div
      class="absolute inset-0 -z-10 scale-110 animate-pulse rounded-[30%_70%_70%_30%/30%_30%_70%_70%] bg-sunny/20 md:scale-125"
    ></div>

    <span
      class="mb-6 inline-block rounded-full border-4 border-deep-blue bg-turquoise px-6 py-2 font-black uppercase tracking-[0.2em] text-deep-blue shadow-[4px_4px_0px_0px_rgba(26,83,92,1)]"
    >
      Kategorie
    </span>

    <h1 class="mb-6 font-display text-6xl font-black leading-[0.9] text-deep-blue md:text-8xl">
      {{ category.name }}
    </h1>

    <p
      v-if="category.description"
      class="mt-4 max-w-2xl text-xl font-medium text-deep-blue/80 md:text-2xl"
    >
      {{ category.description }}
    </p>
  </div>
</template>
