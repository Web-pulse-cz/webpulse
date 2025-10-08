<script setup lang="ts">
import { computed } from 'vue';
import { useToast } from '~/composables/useToast';

const { items, position } = useToast();

const posClass = computed(() => {
  switch (position.value) {
    case 'top-left':
      return 'top-4 left-4';
    case 'bottom-right':
      return 'bottom-4 right-4';
    case 'bottom-left':
      return 'bottom-4 left-4';
    default:
      return 'bottom-4 left-1/2 -translate-x-1/2';
  }
});
</script>

<template>
  <div
    class="pointer-events-none fixed z-[9999] flex flex-col gap-3"
    :class="posClass"
    role="region"
    aria-live="polite"
    aria-atomic="true"
  >
    <TransitionGroup name="toast" tag="div" class="flex flex-col gap-3">
      <UiToastItem v-for="t in items" :key="t.id" :toast="t" />
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.18s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-6px) scale(0.98);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.98);
}
</style>
