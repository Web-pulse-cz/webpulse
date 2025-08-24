<script setup lang="ts">
const { t, locale } = useI18n();
const route = useRoute();
const localePath = useLocalePath();
const pageMeta = ref({
  title: t('review.title'),
  description: t('review.meta_description'),
  meta_title: t('review.meta_title'),
  meta_description: t('review.meta_description'),
});

const api = useApi();
const {
  data: reviewCategoriesData,
  pending: reviewCategoriesPending,
  error: reviewCategoriesError,
  status: reviewCategoriesStatus,
} = useAsyncData('reviewCategories', () => api.review.categories());

const {
  data: reviewData,
  pending: reviewPending,
  error: reviewError,
  status: reviewStatus,
} = useAsyncData('reviewDetail', () =>
  api.review
    .reviewDetail(route.params.id, locale.value)
    .then()
    .catch(() => {
      throw createError({
        statusCode: 404,
        statusMessage: 'Page Not Found',
      });
    }),
);

const tableQuery = ref({
  paginate: 15 as number,
  page: 1 as number,
  categoryId: null as number | null,
});

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

const randomReviews = computed(() => {
  const data = reviewsData.value?.data || [];
  if (data.length <= 3) return data;

  const chosenIndices = new Set<number>();
  while (chosenIndices.size < 3) {
    const randomIndex = Math.floor(Math.random() * data.length);
    chosenIndices.add(randomIndex);
  }

  return Array.from(chosenIndices).map((i) => data[i]);
});
</script>

<!-- TODO random reviews,they used to work, but now are not -->
<template>
  <div>
    <div class="w-full rounded-br-[160px] bg-chppGray md:rounded-br-[300px]">
      <LayoutContainer>
        <div
          class="relative grid h-full min-h-[512px] w-full grid-cols-1 items-center gap-6 py-6 md:grid-cols-2"
        >
          <div class="flex flex-col justify-center gap-3 px-4">
            <BasePropsHeading v-if="reviewData" color="black" type="h1" class="mb-2 line-clamp-2">
              {{ reviewData.name }}
            </BasePropsHeading>

            <div
              v-if="reviewData?.perex"
              class="mb-2 text-base text-gray-800"
              v-html="reviewData.perex"
            ></div>

            <div v-if="reviewData?.categories?.length" class="flex flex-wrap gap-2">
              <NuxtLink
                v-for="category in reviewData.categories"
                :key="category.id"
                :to="
                  localePath({
                    name: 'review-category-id-slug',
                    params: { id: category.id, slug: category.slug },
                  })
                "
                class="cursor-pointer"
              >
                <BasePropsBadge color="gray" class="text-xs">
                  {{ category.name }}
                </BasePropsBadge>
              </NuxtLink>
            </div>
          </div>

          <div class="flex items-center justify-center">
            <img
              src="https://blog.architizer.com/wp-content/uploads/Heydar-ALiyev-Center-in-Baku_cropped.jpg"
              alt="FrontPhoto"
              class="max-h-[320px] w-auto rounded-xl object-contain shadow md:max-h-[420px] lg:max-h-[520px]"
            />
          </div>
        </div>
      </LayoutContainer>
    </div>

    <LayoutContainer v-if="reviewData">
      <div class="review-text" v-html="reviewData.text"></div>

      <div
        v-if="reviewData?.images?.length"
        class="my-8 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3"
      >
        <img
          v-for="(img, index) in reviewData.images"
          :key="index"
          :src="img"
          class="h-64 w-full rounded-lg object-cover shadow-lg"
          alt=""
        />
      </div>

      <hr class="mt-4" />
      <p class="mt-4 text-center text-gray-600">Dalsi reference</p>

      <div v-if="randomReviews.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
        <ReviewReferenceCard
          v-for="review in randomReviews"
          :key="review.id"
          :image="review.image"
          :name="review.name"
          :perex="review.perex"
          :reviews-data="reviewData"
        />
      </div>

      <pre>{{ 'todo images' }}</pre>
      <pre>{{ reviewData }}</pre>
    </LayoutContainer>
  </div>
</template>

<style scoped>
.review-text ::v-deep h2 {
  margin-top: 1.5rem;
}
</style>
