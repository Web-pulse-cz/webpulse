<script setup lang="ts">
import { computed } from 'vue';

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs rounded-xl shadow-sm',
  md: 'px-4 py-2 text-sm rounded-xl shadow-sm',
  lg: 'px-5 py-2.5 text-sm rounded-xl shadow-sm',
  xl: 'px-6 py-3 text-base rounded-xl shadow-sm',
};
const variantClasses = {
  primary: 'bg-forest hover:bg-forest-dark text-white border border-forest transition duration-300',
  secondary:
    'bg-cream-dark text-earth hover:bg-bark-light/20 border border-bark-light/30 transition duration-300',
  outline:
    'border-2 border-forest text-forest hover:bg-forest hover:text-white transition duration-300',
  accent: 'bg-bark text-white hover:bg-bark-light transition duration-300',
};
const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
  },
  size: {
    type: String,
    default: 'md',
  },
});

const buttonClasses = computed(() => {
  return `${sizeClasses[props.size] || sizeClasses.md} ${variantClasses[props.variant] || variantClasses.primary} font-medium`;
});
</script>

<template>
  <button :class="buttonClasses" class="no-print">
    <slot />
  </button>
</template>
