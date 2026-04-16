<script setup lang="ts">
import type { Post } from '~~/types/Post';

export interface BlogPostListProps {
  posts: Post[];
  page: number;
  perPage: number;
  lastPage: number;
  total: number;
  title?: string;
}

const props = withDefaults(defineProps<BlogPostListProps>(), {
  title: '',
});
const { t } = useI18n();

const emit = defineEmits(['update-page']);

const displayTitle = computed(() => props.title || t('blog.title'));
</script>

<template>
  <section class="bg-cream px-6 py-12 lg:px-20 lg:py-16">
    <div class="mx-auto max-w-[1400px]">
      <h2 class="mb-12 text-center font-display text-3xl font-bold text-earth md:text-4xl">
        {{ displayTitle }}
      </h2>
      <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
        <BlogPostCard v-for="(post, index) in posts" :key="index" :index="index" :post="post" />
      </div>
      <div v-if="page !== lastPage" class="mt-12 flex justify-center">
        <div
          class="group flex cursor-pointer items-center gap-3 rounded-xl bg-forest px-8 py-4 text-base font-semibold text-white transition-colors hover:bg-forest-dark"
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
