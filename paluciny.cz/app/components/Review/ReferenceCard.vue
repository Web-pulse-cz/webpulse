<script setup lang="ts">
const localePath = useLocalePath();

const props = defineProps<{
  image: string;
  name: string;
  perex: string;
  reviewsData: { data?: { id: number; name: string; slug: string }[] };
}>();
const matchingReview = computed(() =>
  props.reviewsData?.data?.find((review: { name: string }) => review.name === props.name),
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
    class="flex flex-col items-center overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:shadow-lg"
  >
    <img
      v-if="image"
      :src="image"
      :alt="name"
      class="mb-4 h-96 w-full bg-cream-dark object-cover"
    />
    <div v-else class="mb-4 h-96 w-full bg-cream-dark"></div>

    <div class="justify-end text-center">
      <h5 class="mb-2 text-earth transition-colors duration-300 hover:text-forest">
        {{ name }}
      </h5>
    </div>

    <div class="text-left text-earth-light" v-html="perex"></div>
  </NuxtLink>
</template>
