<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { t, locale } = useI18n();
const route = useRoute();
const localePath = useLocalePath();
const api = useApi();
const { data: reviewCategoriesData } = useAsyncData('reviewCategories', () =>
  api.review.categories(locale.value),
);
const tableQuery = ref({
  paginate: 15 as number,
  page: 1 as number,
  categoryId: null as number | null,
});

const pageMeta = ref({
  title: t('blog.title'),
  description: t('blog.meta_description'),
  meta_title: t('blog.meta_title'),
  meta_description: t('blog.meta_description'),
  id: route.params.id,
  slug: route.params.slug,
});

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('categories', () => api.blog.categories(locale.value));

const {
  data: categoryData,
  status: categoryStatus,
  error: categoryError,
  pending: categoryPending,
} = useAsyncData('category', () => api.blog.categoryDetail(route.params.id, locale.value));

const getPosts = () =>
  api.blog.posts(tableQuery.value.page, tableQuery.value.paginate, locale.value, route.params.id);
const {
  data: postsData,
  status: postsStatus,
  error: postsError,
  pending: postsPending,
} = useAsyncData('categoriesPosts', () =>
  api.blog.posts(tableQuery.value.page, tableQuery.value.paginate, locale.value, route.params.id),
);

const { data: reviewsData } = useAsyncData('reviews', () =>
  api.review.reviews(
    tableQuery.value.page,
    tableQuery.value.paginate,
    locale.value,
    tableQuery.value.categoryId,
  ),
);

const getReviews = () => {
  return api.review.reviews(
    tableQuery.value.page,
    tableQuery.value.paginate,
    locale.value,
    tableQuery.value.categoryId,
  );
};

async function updatePage(page: number) {
  tableQuery.value.page = page;
  const newPosts = getPosts();
  postsData.value = await newPosts;

  const reviews = getReviews();
  reviewsData.value = await reviews;
}

function canonicalUrl() {
  const appUrl = useRuntimeConfig().public.appUrl;
  let string = locale.value !== 'cs' ? `${appUrl}/${locale.value}` : appUrl;
  string += `/review/${t('canonical.category')}`;
  string += categoryData && categoryData.id ? `/${categoryData.id}` : `/${route.params.id}`;
  string += categoryData && categoryData.slug ? `/${categoryData.slug}` : `/${route.params.slug}`;

  return string;
}

watch(
  () => tableQuery.value.categoryId,
  () => {
    tableQuery.value.page = 1;
    getReviews();
  },
);

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
        `/${t('canonical.review')}/${t('canonical.category')}/${pageMeta.value.id}/${pageMeta.value.slug}`,
    },
  ],
});
</script>

<template>
  <LayoutContainer>
    <BasePropsHeading type="h1">
      {{ t('review.title') }}
    </BasePropsHeading>

    <div v-if="reviewCategoriesData" class="mb-8 flex flex-wrap gap-4">
      <NuxtLink
        :to="localePath({ name: 'review' })"
        class="cursor-pointer"
        @click="tableQuery.categoryId = null"
      >
        <BasePropsBadge :color="!route.params.id ? 'unknown' : 'gray'"> All </BasePropsBadge>
      </NuxtLink>

      <!--  @click="tableQuery.categoryId = category.id" maybe useless-color is handling route -->
      <NuxtLink
        v-for="(category, index) in reviewCategoriesData"
        :key="index"
        :to="
          localePath({
            name: 'review-category-id-slug',
            params: { id: category.id, slug: category.slug },
          })
        "
        class="cursor-pointer"
        @click="tableQuery.categoryId = category.id"
      >
        <BasePropsBadge :color="Number(route.params.id) === category.id ? 'unknown' : 'gray'">
          {{ category.name }}
        </BasePropsBadge>
      </NuxtLink>
    </div>
    <div v-if="reviewsData && reviewsData.data">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
        <ReviewReferenceCard
          v-for="review in reviewsData.data"
          :key="review.id"
          :image="review.image"
          :name="review.name"
          :perex="review.perex"
          :reviews-data="reviewsData"
        />
      </div>

      <BasePagination
        v-if="reviewsData"
        :page="tableQuery.page"
        :paginate="tableQuery.paginate"
        :total="reviewsData?.total || 0"
        @update-page="updatePage"
      />
    </div>
  </LayoutContainer>
</template>
