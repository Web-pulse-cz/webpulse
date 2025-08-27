<script setup lang="ts">
defineProps<{
  // Define any props if needed
  text: string;
  imageUrl: string;
  linkUrl: string;
}>();
</script>

<template>
  <NuxtLink
    :to="linkUrl"
    class="m-2 w-full rounded-lg bg-white p-4 font-winky shadow-lg transition-transform duration-300 hover:scale-105"
  >
    <div class="flex flex-col items-center">
      <img :src="imageUrl" :alt="text" class="mb-4 h-40 w-40 rounded-lg object-cover" />
      <h2 class="text-center text-xl font-bold text-gray-800">{{ text }}</h2>
    </div>
  </NuxtLink>
</template>
