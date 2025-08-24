<script setup lang="ts">
const { t, locale } = useI18n();
const route = useRoute();
const tableQuery = ref({
  paginate: 15 as number,
  page: 1 as number,
  categoryId: null as number | null,
});

const localePath = useLocalePath();

const pageMeta = ref({
  title: t('review.title'),
  description: t('review.meta_description'),
  meta_title: t('review.meta_title'),
  meta_description: t('review.meta_description'),
});

const api = useApi();
const { data: reviewCategoriesData } = useAsyncData('reviewCategories', () =>
  api.review.categories(locale.value),
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
  const reviews = getReviews();
  reviewsData.value = await reviews;
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
        `/${t('canonical.review')}`,
    },
  ],
});
definePageMeta({
  name: 'review-index',
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
      <div
        v-if="reviewsData && reviewsData.data"
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3"
      >
        <ReviewReferenceCard
          v-for="review in reviewsData.data"
          :key="review.id"
          :image="review.image"
          :name="review.name"
          :perex="review.perex"
          :reviews-data="reviewsData"
        />
      </div>

      <p>TODO: doplnit kartičky</p>
      <NuxtLink
        v-for="(review, index) in reviewsData.data"
        :key="index"
        :to="
          localePath({
            name: 'review-id-slug',
            params: {
              id: review.id,
              slug: 'test', // nebo review.slug pokud máš
            },
          })
        "
        class="text-brand"
      >
        {{ review.name }}
      </NuxtLink>
      <pre>{{ reviewsData }}</pre>
    </div>
    <BasePagination
      v-if="reviewsData"
      :page="tableQuery.page"
      :paginate="tableQuery.paginate"
      :total="reviewsData?.total || 0"
      @update-page="updatePage"
    />
  </LayoutContainer>
</template>
