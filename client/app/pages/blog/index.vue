<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const localePath = useLocalePath();
const route = useRoute();
const { t, locale } = useI18n();
const api = useApi();
const tableQuery = ref({
  paginate: 12 as number,
  page: 1 as number,
});

const pageMeta = ref({
  title: t('blog.title'),
  description: t('blog.meta_description'),
  meta_title: t('blog.meta_title'),
  meta_description: t('blog.meta_description'),
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('categories', () => api.blog.categories(locale.value));

const getPosts = () => {
  return api.blog.posts(
    tableQuery.value.page,
    tableQuery.value.paginate,
    locale.value,
    route.params.id, // Add the category ID parameter
  );
};

const {
  data: postsData,
  status: postsStatus,
  error: postsError,
  pending: postsPending,
} = useAsyncData(
  // Use a unique key that includes the category ID
  () => `posts-${route.params.id}`,
  () =>
    api.blog.posts(tableQuery.value.page, tableQuery.value.paginate, locale.value, route.params.id),
  {
    watch: [locale],
  },
);

async function updatePage(paginate: number) {
  tableQuery.value.paginate = paginate;
  const newPosts = getPosts();
  postsData.value = await newPosts;
}

useHead({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href:
        useRuntimeConfig().public.appUrl +
        (locale.value !== 'cs' ? `/${locale.value}` : '') +
        `/${t('canonical.blog')}`,
    },
  ],
});
</script>

<template>
  <div>
    <LayoutContainer>
      <div
        class="relative z-10 mx-auto flex w-full max-w-4xl flex-col items-center justify-center py-8 text-center md:py-12"
      >
        <div
          class="absolute inset-0 -z-10 scale-110 animate-pulse rounded-[30%_70%_70%_30%/30%_30%_70%_70%] bg-turquoise/20 md:scale-125"
        ></div>

        <span
          class="mb-6 inline-block rounded-full border-4 border-deep-blue bg-primary px-6 py-2 font-black uppercase tracking-[0.2em] text-white shadow-[4px_4px_0px_0px_rgba(26,83,92,1)]"
        >
          Naše Články
        </span>

        <h1
          class="mb-6 font-display text-6xl font-black italic leading-[0.9] text-deep-blue md:text-8xl"
        >
          {{ t('blog.title') }}
        </h1>
      </div>
    </LayoutContainer>
    <BlogPostList
      v-if="postsData && postsData.data"
      :posts="postsData.data"
      :page="tableQuery.page"
      :per-page="tableQuery.paginate"
      :last-page="postsData.lastPage"
      :total="postsData.total"
      @update-page="updatePage"
    />
    <BlogCategoryList v-if="categoriesData" :categories="categoriesData" />
  </div>
</template>
