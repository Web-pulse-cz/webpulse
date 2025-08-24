<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const localePath = useLocalePath();
const route = useRoute();
const { t, locale } = useI18n();
const api = useApi();
const tableQuery = ref({
  paginate: 15 as number,
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
);

async function updatePage(page: number) {
  tableQuery.value.page = page;
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
    <LayoutContainerTwoCols :sidebar="true" :links="categoriesData" path="blog-category-id-slug">
      <BasePropsHeading type="h1">
        {{ t('blog.title') }}
      </BasePropsHeading>
      <div class="space-y-8">
        <div
          v-for="(post, index) in postsData.data"
          v-if="postsData && postsData.data"
          :key="index"
          class="mb-8"
        >
          <BlogPostMainCard
            :post="post"
            class="block overflow-hidden rounded-[32px] bg-white shadow-sm transition hover:shadow-md"
          />
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div
            v-for="(post, index) in postsData.data"
            v-if="postsData && postsData.data"
            :key="index"
          >
            <BlogPostCard
              :post="post"
              class="mb-2 block rounded-lg bg-gray-100 p-4 hover:bg-gray-200"
            >
              {{ post.name }}
            </BlogPostCard>
          </div>
        </div>
        <BasePagination
          v-if="postsData"
          :page="tableQuery.page"
          :per-page="postsData.perPage"
          :last-page="postsData.lastPage"
          :total="postsData.total"
          @update-page="updatePage"
        />
      </div>
    </LayoutContainerTwoCols>
  </div>
</template>
