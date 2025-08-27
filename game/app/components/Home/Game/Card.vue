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
    class="m-2 flex h-56 w-full items-end rounded-lg p-4 font-winky shadow-lg transition-transform duration-300 hover:scale-105"
    :style="`background-image: url('${imageUrl}'); background-size: cover; background-position: center;`"
  >
    <div class="flex flex-col items-center rounded-lg bg-white bg-opacity-85 p-2">
      <h2 class="text-center text-xl font-bold text-gray-800">{{ text }}</h2>
    </div>
  </NuxtLink>
</template>
