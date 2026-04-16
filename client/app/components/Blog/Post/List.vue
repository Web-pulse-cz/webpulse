<script setup lang="ts">
import type { Post } from '~~/types/Post';

export interface BlogPostListProps {
  posts: Post[];
  page: number;
  perPage: number;
  lastPage: number;
  total: number;
}

defineProps<BlogPostListProps>();
const { t } = useI18n();

const emit = defineEmits(['update-page']);
</script>

<template>
  <section class="bg-white px-6 py-12 lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <h2 class="mb-16 text-center text-4xl font-bold text-slate-900">
        {{ t('blog.title') }}
      </h2>
      <div class="masonry-grid">
        <BlogPostCard v-for="(post, index) in posts" :key="index" :index="index" :post="post" />
      </div>
      <div v-if="page !== lastPage" class="mt-16 flex justify-center">
        <div
          class="group flex cursor-pointer items-center gap-3 rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-white transition-colors hover:bg-primary-dark"
          @click="page !== lastPage ? emit('update-page', Number(perPage + perPage)) : null"
        >
          <span>{{ t('paginatation.loadMore') }}</span>
          <span
            class="material-symbols-outlined transition-transform duration-500 group-hover:rotate-180"
            >add</span
          >
        </div>
      </div>
    </div>
  </section>
</template>
