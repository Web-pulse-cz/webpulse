<script setup lang="ts">
import { computed } from 'vue';
import type { Post } from '~/types/Post';

const { t, locale } = useI18n();
const localePath = useLocalePath();

export interface BlogPostCardProps {
  post: Post;
  index: number;
}
const props = defineProps<BlogPostCardProps>();

// Definice 6 unikátních designů z tvého statického návrhu
const themes = [
  {
    wrapper: 'bg-white shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] rounded-[2.5rem]',
    aspect: 'aspect-[4/3]',
    tag: 'bg-primary text-white',
    titleHover: 'group-hover:text-primary',
    avatarBorder: 'border-primary',
  },
  {
    wrapper: 'bg-turquoise/5 shadow-[8px_8px_0px_0px_rgba(78,205,196,1)] rounded-[3rem]',
    aspect: 'aspect-[1/1]',
    tag: 'bg-deep-blue text-white',
    titleHover: 'group-hover:text-turquoise',
    avatarBorder: 'border-turquoise',
  },
  {
    wrapper: 'bg-sunny/10 shadow-[8px_8px_0px_0px_rgba(255,230,109,1)] rounded-[2.5rem]',
    aspect: 'aspect-[3/4]',
    tag: 'bg-primary text-white',
    titleHover: 'group-hover:text-primary',
    avatarBorder: 'border-primary',
  },
  {
    wrapper: 'bg-white shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] rounded-[3rem]',
    aspect: 'aspect-[16/9]',
    tag: 'bg-turquoise text-white',
    titleHover: 'group-hover:text-turquoise',
    avatarBorder: 'border-turquoise',
  },
  {
    wrapper: 'bg-primary/5 shadow-[8px_8px_0px_0px_rgba(255,107,107,1)] rounded-[2.5rem]',
    aspect: 'aspect-[4/5]',
    tag: 'bg-primary text-white',
    titleHover: 'group-hover:text-primary',
    avatarBorder: 'border-primary',
  },
  {
    wrapper: 'bg-white shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] rounded-[3rem]',
    aspect: 'aspect-[4/3]',
    tag: 'bg-deep-blue text-white',
    titleHover: 'group-hover:text-deep-blue',
    avatarBorder: 'border-deep-blue',
  },
];

const activeTheme = computed(() => {
  const safeIndex =
    typeof props.index === 'number' && !isNaN(props.index) ? Math.max(0, props.index) : 0;

  return themes[safeIndex % themes.length];
});
</script>

<template>
  <div class="masonry-item group cursor-pointer">
    <div
      class="relative overflow-hidden border-4 border-deep-blue transition-all hover:translate-x-2 hover:translate-y-2 hover:shadow-none"
      :class="activeTheme.wrapper"
    >
      <NuxtLink
        :to="
          localePath({ name: 'blog-id-slug', params: { id: props.post.id, slug: props.post.slug } })
        "
        class="block"
      >
        <div class="overflow-hidden" :class="activeTheme.aspect">
          <BaseImage
            :alt="props.post.name"
            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
            :src="`/content/images/post/medium/${props.post.image}`"
          />
        </div>

        <div class="p-8">
          <div
            v-if="post.categories && post.categories.length > 0"
            class="mb-4 flex flex-wrap items-center gap-2"
          >
            <span
              v-for="(category, index) in post.categories"
              :key="index"
              class="mb-4 inline-block rounded-full px-4 py-1 text-xs font-black uppercase tracking-widest"
              :class="activeTheme.tag"
            >
              {{ category?.name || 'Category' }}
            </span>
          </div>

          <h3
            class="mb-4 font-display text-3xl font-black leading-tight text-deep-blue transition-colors"
            :class="activeTheme.titleHover"
          >
            {{ props.post.name }}
          </h3>

          <p class="mb-6 font-medium text-deep-blue/70" v-html="props.post.perex" />
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
