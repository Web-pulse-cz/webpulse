<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData, useRoute, useRuntimeConfig, useHead } from '#app';

const { t, locale } = useI18n();
const route = useRoute();
const localePath = useLocalePath();
const api = useApi();

const tableQuery = ref({
  paginate: 12 as number,
  page: 1 as number,
});

// 1. STAŽENÍ DAT KATEGORIE S DYNAMICKÝM KLÍČEM A SLEDOVÁNÍM
const {
  data: categoryData,
  status: categoryStatus,
  error: categoryError,
  pending: categoryPending,
} = useAsyncData(
  () => `category-${route.params.id}`,
  () =>
    api.blog
      .categoryDetail(route.params.id, locale.value)
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

// 2. STAŽENÍ ČLÁNKŮ S DYNAMICKÝM KLÍČEM A SLEDOVÁNÍM
const getPosts = () =>
  api.blog.posts(tableQuery.value.page, tableQuery.value.paginate, locale.value, route.params.id);

const {
  data: postsData,
  status: postsStatus,
  error: postsError,
  pending: postsPending,
} = useAsyncData(
  () => `categoriesPosts-${route.params.id}`,
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

function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/blog/${t('canonical.category')}`;
  string +=
    categoryData.value && categoryData.value.id
      ? `/${categoryData.value.id}`
      : `/${route.params.id}`;
  string +=
    categoryData.value && categoryData.value.slug
      ? `/${categoryData.value.slug}`
      : `/${route.params.slug}`;

  return string;
}

// 3. REAKTIVNÍ METADATA POMOCÍ COMPUTED
// Pokud ještě nemáme data z API, vložíme výchozí překlady (fallback).
// Jakmile data dorazí, computed se přepočítá.
const pageMeta = computed(() => {
  const defaultTitle = t('blog.title');
  const defaultDesc = t('blog.meta_description');

  return {
    title: categoryData.value?.name ? `${categoryData.value.name} | ${defaultTitle}` : defaultTitle,
    description: categoryData.value?.description || defaultDesc,
    meta_title: categoryData.value?.meta_title || categoryData.value?.name || defaultTitle,
    meta_description:
      categoryData.value?.meta_description || categoryData.value?.description || defaultDesc,
  };
});

// 4. REAKTIVNÍ USEHEAD (Zabalené do arrow funkce)
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
      href: canonicalUrl(), // Tady nyní správně voláme tvou existující funkci!
    },
  ],
}));
</script>

<template>
  <div>
    <LayoutContainer>
      <BlogCategoryHeading v-if="categoryData" :category="categoryData" class="mb-8 text-center" />
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
    <BlogCategoryList />
  </div>
</template>
