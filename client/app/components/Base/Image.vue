<script setup lang="ts">
import type { PropType } from 'vue';

const props = defineProps({
  src: String as PropType<string>,
  alt: String,
  width: String,
  height: String,
  loading: String,
  quality: String,
  class: String,
  fit: String,
});
</script>

<template>
  <NuxtImg
    :src="src"
    :alt="alt"
    :width="width"
    :height="height"
    :fit="fit ?? 'cover'"
    :loading="loading ?? 'lazy'"
    :quality="quality ?? 100"
    :placeholder="[50, 25]"
    :class="props.class"
  />
</template>
