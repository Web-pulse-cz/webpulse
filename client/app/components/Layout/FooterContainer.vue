<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const containerRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      isVisible.value = entry.isIntersecting;
    },
    {
      threshold: 0.1, // Trigger when 10% of the component is in view
    },
  );

  if (containerRef.value) observer.observe(containerRef.value);

  onUnmounted(() => {
    if (containerRef.value) observer.unobserve(containerRef.value);
  });
});
</script>

<template>
  <div
    ref="containerRef"
    class="mx-auto max-w-[1880px] px-8 py-8 transition-opacity duration-1000 lg:px-8 lg:py-16 2xl:px-72"
    :class="{ 'opacity-100': isVisible, 'opacity-0': !isVisible }"
  >
    <slot />
  </div>
</template>
