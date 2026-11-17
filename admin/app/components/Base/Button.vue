<script setup lang="ts">
import { computed } from 'vue';

// Moderní, vzdušnější velikosti s hezkým zaoblením
const sizeClasses: Record<string, string> = {
  sm: 'px-3 py-1.5 text-xs rounded-lg',
  md: 'px-4 py-2 text-sm rounded-xl',
  lg: 'px-5 py-2.5 text-sm rounded-xl',
  xl: 'px-6 py-3 text-base rounded-xl',
};

// Ikonová tlačítka (bez textu) – čtvercový padding
const iconSizeClasses: Record<string, string> = {
  sm: 'p-1.5 rounded-lg',
  md: 'p-2 rounded-xl',
  lg: 'p-2.5 rounded-xl',
  xl: 'p-3 rounded-xl',
};

// Sjednocené barvy ladící se zbytkem dashboardu
const variantClasses: Record<string, string> = {
  primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
  secondary:
    'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 hover:text-slate-900 focus-visible:outline-slate-600',
  success: 'bg-emerald-600 text-white hover:bg-emerald-500 focus-visible:outline-emerald-600',
  danger: 'bg-red-500 text-white hover:bg-red-600 focus-visible:outline-red-500',
  warning: 'bg-amber-400 text-slate-900 hover:bg-amber-500 focus-visible:outline-amber-400',
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
  type: {
    type: String as () => 'button' | 'submit' | 'reset',
    default: 'button',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  // Ikonové tlačítko bez textu – volající musí předat aria-label
  icon: {
    type: Boolean,
    default: false,
  },
});

const isDisabled = computed(() => props.disabled || props.loading);

const buttonClasses = computed(() => {
  const size = props.icon ? iconSizeClasses[props.size] : sizeClasses[props.size];
  return `${size || ''} ${variantClasses[props.variant] || ''}`;
});
</script>

<template>
  <button
    :type="type"
    :disabled="isDisabled"
    :aria-busy="loading ? 'true' : undefined"
    :class="[
      buttonClasses,
      isDisabled ? 'cursor-not-allowed opacity-60' : 'active:scale-95',
      'no-print inline-flex items-center justify-center font-semibold shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
    ]"
  >
    <svg
      v-if="loading"
      :class="[icon ? '' : '-ml-0.5 mr-2', 'size-4 shrink-0 animate-spin']"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
    <slot v-if="!(loading && icon)" />
  </button>
</template>
