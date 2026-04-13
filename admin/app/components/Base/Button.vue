<script setup lang="ts">
import { ref, computed } from 'vue';

// Moderní, vzdušnější velikosti s hezkým zaoblením
const sizeClasses = ref({
  sm: 'px-3 py-1.5 text-xs rounded-lg',
  md: 'px-4 py-2 text-sm rounded-xl',
  lg: 'px-5 py-2.5 text-sm rounded-xl',
  xl: 'px-6 py-3 text-base rounded-xl',
});

// Sjednocené barvy ladící se zbytkem dashboardu
const variantClasses = ref({
  primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
  secondary:
    'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 hover:text-slate-900 focus-visible:outline-slate-600',
  success: 'bg-emerald-600 text-white hover:bg-emerald-500 focus-visible:outline-emerald-600',
  danger: 'bg-red-500 text-white hover:bg-red-600 focus-visible:outline-red-500',
  warning: 'bg-amber-400 text-slate-900 hover:bg-amber-500 focus-visible:outline-amber-400',
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
  return `${sizeClasses.value[props.size] || ''} ${variantClasses.value[props.variant] || ''}`;
});
</script>

<template>
  <button
    :class="[
      buttonClasses,
      'no-print inline-flex items-center justify-center font-semibold shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 active:scale-95',
    ]"
  >
    <slot />
  </button>
</template>
