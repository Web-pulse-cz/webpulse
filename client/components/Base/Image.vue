<script setup lang="ts">
const props = defineProps({
  src: {
    type: String,
    required: true,
  },
  alt: {
    type: String,
    default: 'Image',
  },
  lazy: {
    type: Boolean,
    default: true,
  },
  width: {
    type: String,
    default: '100%',
  },
  height: {
    type: String,
    default: 'auto',
  },
});
</script>

<template>
  <NuxtImg
    :src="`http://api.martinhanzl.cz${src}`"
    :alt="alt"
    :class="`w-[${width}] h-[${height}]`"
  />
</template>
