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

// Zajištění nekonečné rotace šesti témat
const activeTheme = computed(() => themes[props.index % themes.length]);
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
          <img
            :alt="props.post.title"
            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
            :src="
              props.post.imageUrl ||
              'https://lh3.googleusercontent.com/aida-public/AB6AXuC6eNr-ye-7t0x4BL7QCgVLY10DvXrFfZQDCn_VOtwZ84uCD0g3lNoz7k61oK7h1A-Gp-UXSdaK5hWZurS3nXFkE0HKLUoK2y_ZrzF_IO4sR2Hw7SUEtlMkMSZQvcktu7EiskbiVsiJjMFybdgaS5Gnyx5r38DJRTPQDaPNPCnKSax-qpWbjmtQOTbdvlzNpMZ8f_IGQr2Dxb8vdStvFObywpFfLAARmvgZJw5yKcRfOx2V1mo9vrtVIGeLJYR_QMP4PL794AvkT8I'
            "
          />
        </div>

        <div class="p-8">
          <span
            class="mb-4 inline-block rounded-full px-4 py-1 text-xs font-black uppercase tracking-widest"
            :class="activeTheme.tag"
          >
            {{ props.post.category?.name || 'Category' }}
          </span>

          <h3
            class="mb-4 font-display text-3xl font-black leading-tight text-deep-blue transition-colors"
            :class="activeTheme.titleHover"
          >
            {{ props.post.title }}
          </h3>

          <p class="mb-6 font-medium text-deep-blue/70">
            {{ props.post.excerpt }}
          </p>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
