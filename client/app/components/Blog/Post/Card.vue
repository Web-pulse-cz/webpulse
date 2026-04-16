<script setup lang="ts">
import type { Post } from '~/types/Post';

const localePath = useLocalePath();

export interface BlogPostCardProps {
  post: Post;
  index: number;
}
const props = defineProps<BlogPostCardProps>();
</script>

<template>
  <div class="masonry-item group cursor-pointer">
    <div
      class="relative overflow-hidden rounded-2xl bg-white shadow-sm transition-all hover:shadow-lg"
    >
      <NuxtLink
        :to="
          localePath({ name: 'blog-id-slug', params: { id: props.post.id, slug: props.post.slug } })
        "
        class="block"
      >
        <div class="aspect-[4/3] overflow-hidden rounded-t-2xl">
          <BaseImage
            :alt="props.post.name"
            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
            :src="`/content/images/post/medium/${props.post.image}`"
          />
        </div>

        <div class="p-6">
          <div
            v-if="post.categories && post.categories.length > 0"
            class="mb-3 flex flex-wrap items-center gap-2"
          >
            <span
              v-for="(category, index) in post.categories"
              :key="index"
              class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"
            >
              {{ category?.name || 'Category' }}
            </span>
          </div>

          <h3
            class="mb-3 text-xl font-semibold leading-tight text-slate-900 transition-colors group-hover:text-primary"
          >
            {{ props.post.name }}
          </h3>

          <p class="text-sm text-slate-600" v-html="props.post.perex" />
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
