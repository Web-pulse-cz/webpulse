<script setup lang="ts">
defineProps({
  image: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'large',
  },
  fit: {
    type: String,
    default: 'cover',
  },
  width: {
    type: Number,
    default: 128,
  },
  height: {
    type: Number,
    default: 128,
  },
});
</script>

<template>
  <NuxtPicture
    :src="`/content/images/${type}/${size}/${image}`"
    :fit="fit"
    :width="width"
    :height="height"
    preload
  />
</template>
