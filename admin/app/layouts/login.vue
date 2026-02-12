<script setup lang="ts">
import { ref, onMounted } from 'vue';

const backgrounds = ref([
  'bg1.jpg',
  'bg2.jpg',
  'bg3.jpg',
  'bg4.jpg',
  'bg5.jpg',
  'bg6.jpg',
  'bg7.jpg',
  'bg8.jpg',
  'bg9.jpg',
  'bg10.jpg',
]);

const currentBackground = ref('');

onMounted(() => {
  const random = backgrounds.value[Math.floor(Math.random() * backgrounds.value.length)];
  currentBackground.value = `static/img/${random}`;
});
</script>

<template>
  <div
    class="relative mx-auto flex h-full w-full items-center justify-center px-4 sm:px-6 lg:px-8"
    :style="{
      backgroundImage: `url(${currentBackground})`,
      minHeight: '100vh',
      backgroundSize: 'cover',
    }"
  >
    <slot />
  </div>
</template>
