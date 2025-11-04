<script setup lang="ts">
import FsLightbox from 'fslightbox-vue';

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
const toggler = ref(false);
</script>

<template>
  <div>
    <NuxtPicture
      :src="`/content/images/${type}/${size}/${image}`"
      :fit="fit"
      :width="width"
      :height="height"
      preload
      @click="toggler = !toggler"
    />
    <FsLightbox
      :toggler="toggler"
      :sources="['https://martinhanzl.cz/content/images/quiz/screen/6909eb2d5e43b8.17377525.jpg']"
    />
  </div>
</template>
