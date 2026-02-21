<script setup lang="ts">
import type { Post } from '~~/types/Post';

export interface BlogPostListProps {
  posts: Post[];
  page: number;
  perPage: number;
  lastPage: number;
  total: number;
}

const props = defineProps<BlogPostListProps>();
const { t } = useI18n();

const emit = defineEmits(['update-page']);
</script>

<template>
  <section class="bg-background-light px-6 py-12 lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <h2 class="mb-16 text-center font-display text-6xl font-black italic text-deep-blue">
        Latest Articles
      </h2>
      <div class="masonry-grid">
        <BlogPostCard v-for="(post, index) in posts" :key="index" :index="index" :post="post" />
      </div>
      <div v-if="page !== lastPage" class="mt-20 flex justify-center">
        <div
          class="group flex cursor-pointer items-center gap-4 rounded-full border-4 border-deep-blue bg-sunny px-12 py-5 text-xl font-black text-deep-blue shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] transition-all hover:translate-x-2 hover:translate-y-2 hover:shadow-none"
          @click="page !== lastPage ? emit('update-page', Number(perPage + perPage)) : null"
        >
          <span>{{ t('paginatation.loadMore') }}</span>
          <span
            class="material-symbols-outlined font-black transition-transform duration-500 group-hover:rotate-180"
            >add</span
          >
        </div>
      </div>
    </div>
  </section>
</template>
