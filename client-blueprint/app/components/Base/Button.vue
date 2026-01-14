<script setup lang="ts">
import { ref, computed } from 'vue';

const sizeClasses = ref({
  sm: 'px-4 lg:px-8 py-3 text-xs shadow-sm rounded-full',
  md: 'px-1.5 py-1.5 lg:px-2.5 py-1 lg:py-1.5 text-xs shadow-sm rounded-full',
  lg: 'px-1.5 lg:px-3 py-1.5 lg:py-2 text-xs lg:text-sm shadow-sm rounded-full',
  xl: 'px-2 lg:px-3.5 py-1.5 lg:py-2.5 text-xs lg:text-sm shadow-sm rounded-full',
  xxl: 'px-6 lg:px-8 py-3 lg:py-4 text-xs lg:text-sm shadow-sm rounded-full',
  long: 'px-5 lg:px-7 py-3 lg:py-4 text-xs lg:text-sm shadow-sm rounded-full',
});
const variantClasses = ref({
  primary:
    'bg-primary hover:bg-secondary hover:text-brand text-beige border border-primary transition duration-300 ',
  secondary:
    'bg-white ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight text-grayDark',
});
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
  return `${sizeClasses.value[props.size] || ''} ${variantClasses.value[props.variant] || ''} font-medium`;
});
</script>

<template>
  <button :class="buttonClasses" class="no-print">
    <slot />
  </button>
</template>
