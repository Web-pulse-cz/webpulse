<script setup lang="ts">
defineProps({
  backgroundShow: {
    type: Boolean,
    required: false,
    default: true,
  },
});
</script>

<template>
  <div
    :class="[
      backgroundShow ? 'bg-white shadow' : 'bg-white/25 backdrop-blur-sm',
      'max-w-8xl mx-auto mt-10 rounded-lg py-6 pb-6 pl-8 pr-8',
    ]"
  >
    <slot />
  </div>
</template>
