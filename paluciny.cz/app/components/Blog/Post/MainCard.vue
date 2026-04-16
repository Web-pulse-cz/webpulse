<script setup lang="ts">
import dayjs from 'dayjs';
import type { Post } from '~/types/Post';

const localePath = useLocalePath();

export interface BlogMainCardProps {
  post: Post;
}

const props = defineProps<BlogMainCardProps>();
const formattedDate = computed(() => dayjs(props.post.created_at).format('DD. MM. YYYY'));
</script>

<template>
  <NuxtLink
    :to="localePath({ name: 'blog-id-slug', params: { id: post.id, slug: post.slug } })"
    class="group block overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-500 hover:shadow-lg"
  >
    <div
      class="flex flex-col justify-end bg-cover bg-center"
      :style="{
        backgroundImage: `url(${post.image})`,
        paddingTop: '480px',
      }"
    >
      <div class="flex flex-col gap-8 bg-forest-dark/60 p-8 backdrop-blur-lg">
        <div class="flex max-w-[800px] flex-col gap-4 text-white">
          <h2 class="font-display leading-9 text-white">
            {{ post.name }}
          </h2>
          <div class="text-base font-normal leading-relaxed" v-html="post.perex"></div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 text-white">
          <div class="flex items-center gap-6">
            <div>{{ formattedDate }}</div>
          </div>

          <div
            v-if="post.categories && post.categories.length > 0"
            class="inline-flex items-center justify-center gap-1 overflow-hidden rounded-full bg-white/10 px-3 py-1.5 backdrop-blur-sm"
          >
            <div
              class="flex flex-wrap justify-start gap-2 text-center text-xs font-semibold leading-none"
            >
              <span v-for="category in post.categories" :key="category.id" class="text-white">
                {{ category.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLink>
</template>
