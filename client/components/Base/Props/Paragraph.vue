<script setup lang="ts">
const sizeClasses = ref({
  base: 'text-xs md:text-md lg:text-base',
  small: 'text-xs md:text-sm lg:text-md',
});
const boldClasses = ref({
  base: 'font-normal',
  bold: 'font-bold',
  bolder: 'font-extrabold',
});
const colorClasses = ref({
  primary: 'text-primary',
  secondary: 'text-secondary',
  success: 'text-success',
  warning: 'text-warning',
  danger: 'text-danger',
  light: 'text-light',
});
const props = defineProps({
  size: {
    type: String,
    default: 'base',
    required: false,
  },
  bold: {
    type: String,
    default: 'base',
    required: false,
  },
  color: {
    type: String,
    default: 'primary',
    required: false,
  },
  html: {
    type: String,
    default: '',
    required: false,
  },
});
const baseClass = computed(() => {
  return `${sizeClasses.value[props.size] || ''} ${boldClasses.value[props.bold] || ''} ${colorClasses.value[props.color] || ''} leading-6 tracking-tight`;
});
</script>

<template>
  <p v-if="html === ''" :class="baseClass">
    <slot />
  </p>
  <p v-else :class="baseClass" v-html="html" />
</template>
