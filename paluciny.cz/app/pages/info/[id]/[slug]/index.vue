<script setup lang="ts">
import { useApi } from '~/../app/composables/useApi';

const { t, locale } = useI18n();
const route = useRoute();
const api = useApi();

// 1. STAZENI DAT S HLIDANIM ZMEN (watch)
const {
  data: pageData,
  error: pageError,
  pending: pagePending,
} = useAsyncData(
  () => `page-${route.params.id}`,
  () =>
    api.page
      .page(route.params.id, locale.value)
      .then()
      .catch(() => {
        console.log('Page not found, throwing error');
        throw createError({
          statusCode: 404,
          statusMessage: 'Page Not Found test',
        });
      }),
  {
    watch: [() => route.params.id, locale],
  },
);

// 2. FUNKCE PRO KANONICKOU URL
function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/${t('canonical.info')}`;
  string += pageData.value && pageData.value.id ? `/${pageData.value.id}` : `/${route.params.id}`;
  string +=
    pageData.value && pageData.value.slug ? `/${pageData.value.slug}` : `/${route.params.slug}`;

  return string;
}

// 3. REAKTIVNI METADATA PRES COMPUTED
const pageMeta = computed(() => {
  const defaultTitle = t('info.title');
  const defaultDesc = t('info.meta_description');

  return {
    title: pageData.value?.name ? `${pageData.value.name} | ${defaultTitle}` : defaultTitle,
    description: pageData.value?.description || defaultDesc,
    meta_title: pageData.value?.meta_title || pageData.value?.name || defaultTitle,
    meta_description:
      pageData.value?.meta_description || pageData.value?.description || defaultDesc,
    id: route.params.id,
    slug: route.params.slug,
  };
});

// 4. REAKTIVNI USEHEAD
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
      v-if="!pagePending && !pageError && pageData"
      class="relative mx-auto max-w-4xl px-6 py-16 lg:py-24"
    >
      <article class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm md:p-16">
        <header class="mb-12">
          <h1 class="mb-8 font-display text-4xl font-bold leading-tight text-earth md:text-5xl">
            {{ pageData.name }}
          </h1>
        </header>

        <div
          class="article-content text-lg font-medium leading-relaxed text-earth-light md:text-xl"
          v-html="pageData.text"
        ></div>
      </article>
    </LayoutContainer>

    <div v-else-if="pagePending" class="flex min-h-[50vh] items-center justify-center">
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
