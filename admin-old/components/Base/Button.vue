<script setup lang="ts">
import { ref, computed } from 'vue';

const sizeClasses = ref({
  sm: 'px-1 lg:px-2 py-1 text-xs shadow-sm rounded',
  md: 'px-1.5 py-1.5 lg:px-2.5 py-1 lg:py-1.5 text-xs shadow-sm rounded',
  lg: 'px-1.5 lg:px-3 py-1.5 lg:py-2 text-xs lg:text-sm shadow-sm rounded-md',
  xl: 'px-2 lg:px-3.5 py-1.5 lg:py-2.5 text-xs lg:text-sm shadow-sm rounded-md',
});
const variantClasses = ref({
  primary: 'bg-primaryCustom hover:bg-primaryLight focus-visible:outline-primaryLight text-white',
  secondary:
    'bg-white ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight text-grayDark',
  success: 'bg-success hover:bg-successLight focus-visible:outline-successLight text-white',
  danger: 'bg-danger hover:bg-dangerLight focus-visible:outline-dangerLight text-white',
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
  return `${sizeClasses.value[props.size] || ''} ${variantClasses.value[props.variant] || ''} font-semibold`;
});
</script>

<template>
  <button :class="buttonClasses" class="no-print">
    <slot />
  </button>
</template>
