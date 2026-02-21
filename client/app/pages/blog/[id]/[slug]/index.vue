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

// 1. DYNAMICKÝ KLÍČ A SLEDOVÁNÍ ZMĚN
const {
  data: postData,
  status: postStatus,
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

// Opravená funkce (přidáno .value u postData)
function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/blog/${t('canonical.post')}`;
  string += postData.value && postData.value.id ? `/${postData.value.id}` : `/${route.params.id}`;
  string +=
    postData.value && postData.value.slug ? `/${postData.value.slug}` : `/${route.params.slug}`;

  return string;
}

// 2. REAKTIVNÍ METADATA PŘES COMPUTED
const pageMeta = computed(() => {
  const defaultTitle = t('blog.title');
  const defaultDesc = t('blog.meta_description');

  return {
    title: postData.value?.name ? `${postData.value.name} | ${defaultTitle}` : defaultTitle,
    // U článku se často místo "description" používá "perex" nebo úvodní text. Pokud máš v API jiný název klíče, jen ho tu přepiš:
    description: postData.value?.perex || postData.value?.description || defaultDesc,
    meta_title: postData.value?.meta_title || postData.value?.name || defaultTitle,
    meta_description: postData.value?.meta_description || postData.value?.perex || defaultDesc,
    id: route.params.id,
    slug: route.params.slug,
  };
});

// 3. REAKTIVNÍ USEHEAD S FUNKCÍ
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
      href: canonicalUrl(), // Propojili jsme tu tvoji existující funkci
    },
  ],
}));
</script>

<template>
  <div class="min-h-screen bg-background-light">
    <LayoutContainer
      v-if="!postPending && !postError && postData"
      class="relative mx-auto max-w-4xl px-6 py-16 lg:py-24"
    >
      <div
        class="absolute -right-10 -top-10 -z-10 hidden size-64 animate-pulse rounded-[30%_70%_70%_30%/30%_30%_70%_70%] bg-sunny/20 md:block"
      ></div>

      <article
        class="relative overflow-hidden rounded-[3rem] border-4 border-deep-blue bg-white p-8 shadow-[12px_12px_0px_0px_rgba(26,83,92,1)] md:p-16"
      >
        <header class="mb-10">
          <div
            v-if="postData && postData.categories && postData.categories.length > 0"
            class="mb-6 flex items-center gap-4"
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
              class="inline-block rounded-full border-2 border-deep-blue bg-primary px-6 py-2 text-sm font-black uppercase tracking-widest text-white shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] transition-all hover:translate-x-1 hover:translate-y-1 hover:shadow-none"
            >
              {{ category.name }}
            </NuxtLink>
          </div>
          <span
            v-else
            class="mb-6 inline-block rounded-full border-2 border-deep-blue bg-primary px-6 py-2 text-sm font-black uppercase tracking-widest text-white shadow-[4px_4px_0px_0px_rgba(26,83,92,1)]"
          >
            Article
          </span>

          <h1
            class="mb-8 font-display text-5xl font-black leading-[1.1] text-deep-blue md:text-7xl"
          >
            {{ postData.name }}
          </h1>

          <div class="flex items-center gap-4 border-t-2 border-dotted border-deep-blue/20 pt-6">
            <div
              class="flex size-14 items-center justify-center rounded-[30%_70%_70%_30%/30%_30%_70%_70%] border-2 border-deep-blue bg-turquoise text-white shadow-sm"
            >
              <span class="material-symbols-outlined text-2xl">edit_document</span>
            </div>
            <div class="flex flex-col">
              <span class="mt-1 text-xs font-bold uppercase tracking-widest text-deep-blue/50">
                {{ dayjs(postData.createdAt).format('DD.MM.YYYY') }} • 5 min read
              </span>
            </div>
          </div>
        </header>

        <div
          v-if="postData.image"
          class="group mb-12 overflow-hidden rounded-[2.5rem] border-4 border-deep-blue shadow-[8px_8px_0px_0px_rgba(26,83,92,1)]"
        >
          <BaseImage
              :src="`/content/images/post/large/${postData.image}`"
            :alt="postData.name"
            class="aspect-video w-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
        </div>

        <div
          class="article-content text-lg font-medium leading-relaxed text-deep-blue/80 md:text-xl"
          v-html="postData.text"
        ></div>
      </article>
    </LayoutContainer>

    <div v-else-if="postPending" class="flex min-h-[50vh] items-center justify-center">
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
