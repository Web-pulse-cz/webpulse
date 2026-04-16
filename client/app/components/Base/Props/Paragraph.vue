<script setup lang="ts">
const sizeClasses = {
  large: 'text-sm md:text-base lg:text-lg',
  base: 'text-xs md:text-md lg:text-base',
  small: 'text-xs md:text-sm lg:text-md',
};
const boldClasses = {
  base: 'font-normal',
  bold: 'font-bold',
  bolder: 'font-extrabold',
};
const colorClasses = {
  primary: 'text-text-primary',
  secondary: 'text-text-secondary',
  brand: 'text-primary',
  light: 'text-slate-400',
  white: 'text-white',
};
const props = defineProps({
  size: {
    type: String,
    default: 'large',
    required: false,
  },
  bold: {
    type: String,
    default: 'base',
    required: false,
  },
  color: {
    type: String,
    default: 'secondary',
    required: false,
  },
  html: {
    type: String,
    default: '',
    required: false,
  },
});
const baseClass = computed(() => {
  return `${sizeClasses[props.size] || ''} ${boldClasses[props.bold] || ''} ${colorClasses[props.color] || ''} leading-6 tracking-tight`;
});
</script>

<template>
  <p v-if="html === ''" :class="baseClass">
    <slot />
  </p>
  <p v-else :class="baseClass" v-html="html" />
</template>
