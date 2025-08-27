<script setup lang="ts">
const { t, locale } = useI18n();
const localePath = useLocalePath();

const props = defineProps<{
  image: string;
  name: string;
  perex: string;
  reviewsData: any;
}>();
const matchingReview = computed(() =>
  props.reviewsData?.data?.find((review: any) => review.name === props.name),
);
</script>

<template>
  <NuxtLink
    v-if="matchingReview"
    :to="
      localePath({
        name: 'review-id-slug',
        params: {
          id: matchingReview.id,
          slug: matchingReview.slug || 'test',
        },
      })
    "
    class="flex flex-col items-center overflow-hidden rounded-lg bg-white shadow-xl backdrop-blur-lg transition-all duration-1000 hover:shadow-xl hover:shadow-redShadow"
  >
    <img v-if="image" :src="image" :alt="name" class="mb-4 h-96 w-full bg-black object-cover" />
    <div v-else class="mb-4 h-96 w-full bg-black"></div>

    <div class="justify-end text-center">
      <h5 class="mb-2 text-black transition-all duration-1000 hover:text-brand">
        {{ name }}
      </h5>
    </div>

    <div class="text-left" v-html="perex"></div>
  </NuxtLink>
</template>
