<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData, useRoute, useRuntimeConfig, useHead, createError } from '#app';

const { t, locale } = useI18n();
const route = useRoute();
const api = useApi();

// 1. STAŽENÍ DAT S HLÍDÁNÍM ZMĚN (watch)
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
      // Hlídáme změnu URL a jazyka, aby Nuxt věděl, že má načíst nová data
      watch: [() => route.params.id, locale],
    }
);

// 2. FUNKCE PRO KANONICKOU URL (Vyčištěno pro lepší čitelnost)
function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/${t('canonical.info')}`;
  string += pageData.value && pageData.value.id ? `/${pageData.value.id}` : `/${route.params.id}`;
  string += pageData.value && pageData.value.slug ? `/${pageData.value.slug}` : `/${route.params.slug}`;

  return string;
}

// 3. REAKTIVNÍ METADATA PŘES COMPUTED
const pageMeta = computed(() => {
  const defaultTitle = t('info.title');
  const defaultDesc = t('info.meta_description');

  // Předpokládám, že tvé API vrací 'name' nebo 'title' a 'description'.
  // Pokud se to jmenuje jinak, jen to tu uprav.
  return {
    title: pageData.value?.name
        ? `${pageData.value.name} | ${defaultTitle}`
        : defaultTitle,
    description: pageData.value?.description || defaultDesc,
    meta_title: pageData.value?.meta_title || pageData.value?.name || defaultTitle,
    meta_description: pageData.value?.meta_description || pageData.value?.description || defaultDesc,
    id: route.params.id,
    slug: route.params.slug,
  };
});

// 4. REAKTIVNÍ USEHEAD (Zabalené do arrow funkce)
useHead(() => ({
  title: pageMeta.value.title,
  meta: [
    // Opravil jsem ti tu content na .description (měl jsi tam dvakrát .meta_description)
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
  <div class="min-h-screen bg-background-light">
    <LayoutContainer
      v-if="!pagePending && !pageError && pageData"
      class="relative mx-auto max-w-4xl px-6 py-16 lg:py-24"
    >
      <div
        class="absolute -right-10 -top-10 -z-10 hidden size-64 animate-pulse rounded-[30%_70%_70%_30%/30%_30%_70%_70%] bg-sunny/20 md:block"
      ></div>

      <article
        class="relative overflow-hidden rounded-[3rem] border-4 border-deep-blue bg-white p-8 shadow-[12px_12px_0px_0px_rgba(26,83,92,1)] md:p-16"
      >
        <header class="mb-12">
          <h1
            class="mb-8 font-display text-5xl font-black leading-[1.1] text-deep-blue md:text-7xl"
          >
            {{ pageData.name }}
          </h1>
        </header>

        <div
          class="article-content text-lg font-medium leading-relaxed text-deep-blue/80 md:text-xl"
          v-html="pageData.text"
        ></div>
      </article>
    </LayoutContainer>

    <div v-else-if="pagePending" class="flex min-h-[50vh] items-center justify-center">
      <div
        class="size-16 animate-spin rounded-full border-4 border-deep-blue border-t-primary"
      ></div>
    </div>
  </div>
</template>

<style scoped>
/* Protože používáš v-html, Tailwind třídy se automaticky neaplikují na vložené tagy.
  Pomocí selektoru :deep() nastavíme styly přímo pro HTML tagy uvnitř článku,
  aby text odpovídal tvému specifickému designu.
*/

.article-content :deep(h2) {
  @apply mb-6 mt-12 font-display text-4xl font-black italic leading-tight text-deep-blue;
}

.article-content :deep(h3) {
  @apply mb-4 mt-10 font-display text-3xl font-black text-deep-blue;
}

.article-content :deep(p) {
  @apply mb-0;
}

.article-content :deep(a) {
  @apply font-bold text-deep-blue underline decoration-primary decoration-4 underline-offset-4 transition-all hover:bg-primary hover:text-white;
}

.article-content :deep(ul) {
  @apply mb-0 list-inside list-disc space-y-2;
}

.article-content :deep(li) {
  @apply pl-2;
}

.article-content :deep(blockquote) {
  @apply my-8 rounded-r-2xl border-l-8 border-turquoise bg-turquoise/10 p-6 font-display text-2xl italic text-deep-blue;
}

.article-content :deep(img) {
  @apply my-10 h-auto max-w-full rounded-3xl border-4 border-deep-blue shadow-[8px_8px_0px_0px_rgba(26,83,92,1)];
}

.article-content :deep(strong) {
  @apply font-black text-deep-blue;
}
</style>