<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();
const api = useApi();

const pageMeta = ref({
  title: t('blog.title'),
  description: t('blog.meta_description'),
  meta_title: t('blog.meta_title'),
  meta_description: t('blog.meta_description'),
});

const { data: categoriesData } = useAsyncData('categories', () =>
  api.blog.categories(locale.value),
);

const getPosts = () => {
  return api.blog.posts(
    tableQuery.value.page,
    tableQuery.value.paginate,
    locale.value,
    route.params.id,
  );
};

const route = useRoute();

const tableQuery = ref({
  paginate: 12 as number,
  page: 1 as number,
});

const { data: postsData } = useAsyncData(
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
    { name: 'description', content: pageMeta.value.meta_description },
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
        <span
          class="mb-6 inline-block rounded-full bg-primary/10 px-5 py-2 text-sm font-semibold uppercase tracking-widest text-primary"
        >
          Blog
        </span>

        <h1 class="mb-6 text-4xl font-bold leading-tight text-slate-900 md:text-5xl">
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
