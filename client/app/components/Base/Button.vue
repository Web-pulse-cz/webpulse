<script setup lang="ts">
import { computed } from 'vue';

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs rounded-lg shadow-sm',
  md: 'px-4 py-2 text-sm rounded-lg shadow-sm',
  lg: 'px-5 py-2.5 text-sm rounded-lg shadow-sm',
  xl: 'px-6 py-3 text-base rounded-lg shadow-sm',
};
const variantClasses = {
  primary:
    'bg-primary hover:bg-primary-dark text-white border border-primary transition duration-300',
  secondary:
    'bg-white ring-1 ring-inset ring-slate-300 hover:bg-slate-50 text-slate-700 transition duration-300',
  outline:
    'border border-primary text-primary hover:bg-primary hover:text-white transition duration-300',
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
