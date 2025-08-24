<script setup lang="ts">
import { ref } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';

const rating = defineModel('rating', {
  type: Number,
  default: 1,
});

const props = defineProps({
  maxStars: {
    type: Number,
    default: 5,
  },
});
</script>

<template>
  <div class="flex space-x-1.5">
    <div v-for="star in maxStars" :key="star" class="cursor-pointer focus:outline-none">
      <StarIcon
        :class="`h-6 w-6 ${star <= rating ? 'text-yellow-400' : 'text-grayLight'}`"
        @click="rating = star"
      />
    </div>
  </div>
</template>
