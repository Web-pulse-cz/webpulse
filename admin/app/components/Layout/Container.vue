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
      backgroundShow
        ? 'bg-white shadow-xl shadow-slate-200/50 ring-1 ring-slate-200'
        : 'bg-white/40 ring-1 ring-white/30 backdrop-blur-md',
      'max-w-8xl mx-auto mt-10 rounded-3xl p-6 transition-all duration-300 sm:p-8',
    ]"
  >
    <slot />
  </div>
</template>
