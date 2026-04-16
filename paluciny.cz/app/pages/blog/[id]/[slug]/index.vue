<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';
import { computed } from 'vue';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData, useRoute, useRuntimeConfig, useHead, createError } from '#app';

const localePath = useLocalePath();
const { t, locale } = useI18n();
const route = useRoute();
const api = useApi();

// 1. DYNAMICKY KLIC A SLEDOVANI ZMEN
const {
  data: postData,
  error: postError,
  pending: postPending,
} = useAsyncData(
  () => `postDetail-${route.params.id}`,
  () =>
    api.blog
      .postDetail(route.params.id, locale.value)
      .then()
      .catch(() => {
        throw createError({
          statusCode: 404,
          statusMessage: 'Page Not Found',
        });
      }),
  {
    watch: [() => route.params.id, locale],
  },
);

// Opravena funkce (pridano .value u postData)
function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/blog/${t('canonical.post')}`;
  string += postData.value && postData.value.id ? `/${postData.value.id}` : `/${route.params.id}`;
  string +=
    postData.value && postData.value.slug ? `/${postData.value.slug}` : `/${route.params.slug}`;

  return string;
}

// 2. REAKTIVNI METADATA PRES COMPUTED
const pageMeta = computed(() => {
  const defaultTitle = t('blog.title');
  const defaultDesc = t('blog.meta_description');

  return {
    title: postData.value?.name ? `${postData.value.name} | ${defaultTitle}` : defaultTitle,
    description: postData.value?.perex || postData.value?.description || defaultDesc,
    meta_title: postData.value?.meta_title || postData.value?.name || defaultTitle,
    meta_description: postData.value?.meta_description || postData.value?.perex || defaultDesc,
    id: route.params.id,
    slug: route.params.slug,
  };
});

// 3. REAKTIVNI USEHEAD S FUNKCI
useHead(() => ({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href: canonicalUrl(),
    },
  ],
}));
</script>

<template>
  <div class="min-h-screen bg-cream">
    <LayoutContainer
      v-if="!postPending && !postError && postData"
      class="relative mx-auto max-w-4xl px-6 py-16 lg:py-24"
    >
      <article class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm md:p-16">
        <header class="mb-10">
          <div
            v-if="postData && postData.categories && postData.categories.length > 0"
            class="mb-6 flex items-center gap-3"
          >
            <NuxtLink
              v-for="(category, index) in postData.categories"
              :key="index"
              :to="
                localePath({
                  name: 'blog-category-id-slug',
                  params: { id: category.id, slug: category.slug },
                })
              "
              class="inline-block rounded-full bg-forest px-4 py-1.5 text-sm font-medium text-white transition-colors hover:bg-forest-dark"
            >
              {{ category.name }}
            </NuxtLink>
          </div>
          <span
            v-else
            class="mb-6 inline-block rounded-full bg-forest px-4 py-1.5 text-sm font-medium text-white"
          >
            Článek
          </span>

          <h1 class="mb-8 font-display text-4xl font-bold leading-tight text-earth md:text-5xl">
            {{ postData.name }}
          </h1>

          <div class="flex items-center gap-4 border-t border-cream-dark pt-6">
            <div
              class="flex size-12 items-center justify-center rounded-xl bg-forest/10 text-forest"
            >
              <span class="material-symbols-outlined text-xl">edit_document</span>
            </div>
            <div class="flex flex-col">
              <span class="mt-1 text-xs font-medium uppercase tracking-widest text-earth-light">
                {{ dayjs(postData.createdAt).format('DD.MM.YYYY') }} • 5 min read
              </span>
            </div>
          </div>
        </header>

        <div v-if="postData.image" class="group mb-12 overflow-hidden rounded-xl">
          <BaseImage
            :src="`/content/images/post/large/${postData.image}`"
            :alt="postData.name"
            class="aspect-video w-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
        </div>

        <div
          class="article-content text-lg font-medium leading-relaxed text-earth-light md:text-xl"
          v-html="postData.text"
        ></div>
      </article>
    </LayoutContainer>

    <div v-else-if="postPending" class="flex min-h-[50vh] items-center justify-center">
      <div
        class="size-16 animate-spin rounded-full border-4 border-forest/20 border-t-forest"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.article-content :deep(h2) {
  @apply mb-6 mt-12 font-display text-3xl font-bold leading-tight text-earth;
}

.article-content :deep(h3) {
  @apply mb-4 mt-10 font-display text-2xl font-bold text-earth;
}

.article-content :deep(p) {
  @apply mb-0;
}

.article-content :deep(a) {
  @apply font-semibold text-forest underline decoration-forest/40 decoration-2 underline-offset-4 transition-all hover:decoration-forest;
}

.article-content :deep(ul) {
  @apply mb-0 list-inside list-disc space-y-2;
}

.article-content :deep(li) {
  @apply pl-2;
}

.article-content :deep(blockquote) {
  @apply my-8 rounded-r-xl border-l-4 border-leaf bg-leaf/10 p-6 text-xl italic text-earth;
}

.article-content :deep(img) {
  @apply my-10 h-auto max-w-full rounded-xl;
}

.article-content :deep(strong) {
  @apply font-bold text-earth;
}
</style>
