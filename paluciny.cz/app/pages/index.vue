<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData, useRuntimeConfig, useHead } from '#app';

const { locale, t } = useI18n();
const api = useApi();

const pageMeta = ref({
  title: t('general.metaTitle'),
  description: t('general.metaDescription'),
  meta_title: t('general.metaTitle'),
  meta_description: t('general.metaDescription'),
});

const tableQuery = ref({
  paginate: 3 as number,
  page: 1 as number,
});

const getPosts = () => {
  return api.blog.posts(tableQuery.value.page, tableQuery.value.paginate, locale.value, null, '');
};

const { data: postsData } = useAsyncData('posts', () => getPosts(), {
  watch: [locale, () => tableQuery.value.page, () => tableQuery.value.paginate],
});

async function updatePage(paginate: number) {
  tableQuery.value.paginate = paginate;
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
      href: useRuntimeConfig().public.appUrl + (locale.value !== 'cs' ? `/${locale.value}` : ''),
    },
  ],
});
</script>

<template>
  <div>
    <HomeHero />

    <BlogPostList
      v-if="postsData && postsData.data"
      id="bleskovky"
      class="blog-posts"
      :posts="postsData.data || []"
      :page="tableQuery.page"
      :per-page="tableQuery.paginate"
      :last-page="postsData.lastPage"
      :total="postsData.total"
      title="Bleskovky"
      @update-page="updatePage"
    />

    <HomeAbout />

    <HomeWhyUs />

    <BlogCategoryList />

    <HomeContact />
  </div>
</template>
