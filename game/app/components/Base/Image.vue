<script setup lang="ts">
import { ref } from 'vue';
import FsLightbox from 'fslightbox-vue';

const props = defineProps({
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
      :key="image"
      :toggler="toggler"
      :sources="[`/content/images/${type}/screen/${image}`]"
    />
  </div>
</template>
