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
    <img v-if="image" :src="image" :alt="name" class="mb-4 h-96 w-full bg-black object-cover" />
    <div v-else class="mb-4 h-96 w-full bg-black"></div>

    <div class="justify-end text-center">
      <h5 class="mb-2 text-slate-900 transition-colors duration-300 hover:text-primary">
        {{ name }}
      </h5>
    </div>

    <div class="text-left" v-html="perex"></div>
  </NuxtLink>
</template>
