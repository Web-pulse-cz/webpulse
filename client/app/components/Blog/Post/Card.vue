<script setup lang="ts">
import dayjs from 'dayjs';
import type { Post } from '~/types/Post';

const { t, locale } = useI18n();

const localePath = useLocalePath();
export interface BlogPostCardProps {
  post: Post;
}
const props = defineProps<BlogPostCardProps>();
</script>

<template>
  <NuxtLink
    :to="localePath({ name: 'blog-id-slug', params: { id: props.post.id, slug: props.post.slug } })"
    class="group block overflow-hidden rounded-lg bg-white shadow-xl transition-all duration-1000 hover:shadow-xl hover:shadow-redShadow"
  >
    <div class="inline-flex w-96 flex-col items-start justify-end gap-5 overflow-hidden">
      <img class="relative h-56 self-stretch rounded-3xl" :src="post.image" :alt="post.name" />
      <div class="flex flex-col items-start justify-start gap-2 self-stretch">
        <div
          v-if="post.categories && post.categories.length > 0"
          class="inline-flex items-center justify-center gap-1 overflow-hidden rounded-full bg-indigo-50 px-2 py-1"
        >
          <div
            class="flex flex-wrap justify-start gap-2 text-center text-xs font-semibold leading-none"
          >
            <span v-for="category in post.categories" :key="category.id" class="text-brand">
              {{ category.name }}
            </span>
          </div>
        </div>
        <div class="justify-start self-stretch text-2xl font-bold leading-loose text-textBlack">
          {{ post.name }}
        </div>
        <div
          class="justify-start self-stretch text-base font-normal leading-relaxed text-textDescription"
          v-html="post.perex"
        ></div>
      </div>
      <div class="inline-flex items-center justify-start gap-4 self-stretch">
        <div class="justify-start self-stretch text-sm font-medium leading-tight text-slate-400">
          {{ dayjs(post.created_at).format('DD. MM. YYYY') }}
        </div>
      </div>
    </div>
  </NuxtLink>
</template>
