<script setup lang="ts">
import { ref, computed } from 'vue';

const variantClasses = ref({
  primary:
    'bg-primary text-primaryDark hover:bg-transparent hover:text-brand text-white border border-brand transition duration-300 ',
  secondary:
    'bg-white ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight text-grayDark',
  success: 'bg-success hover:bg-successLight focus-visible:outline-successLight text-white',
  danger: 'bg-danger hover:bg-dangerLight focus-visible:outline-dangerLight text-white',
  reverse: 'bg-clear hover:bg-brand hover:text-white text-black border border-brand',
});
const sizeClasses = ref({
  sm: 'text-sm',
  md: 'text-base',
  lg: 'text-lg',
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
  return `${sizeClasses.value[props.size] || ''} font-medium rounded-2xl w-auto border p-3 transition hover:shadow`;
});
</script>

<template>
  <button :class="buttonClasses" class="no-print">
    <slot />
  </button>
</template>
