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
  <div class="flex items-center space-x-1">
    <button
      v-for="star in maxStars"
      :key="star"
      type="button"
      class="group relative flex items-center justify-center rounded-md p-1 transition-transform duration-200 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 active:scale-95"
      @click="rating = star"
    >
      <StarIcon
        :class="[
          star <= rating ? 'text-amber-400' : 'text-slate-200',
          'size-6 fill-current transition-colors duration-200',
        ]"
        aria-hidden="true"
      />
      <span class="sr-only">Ohodnotit {{ star }} z {{ maxStars }}</span>
    </button>
  </div>
</template>
