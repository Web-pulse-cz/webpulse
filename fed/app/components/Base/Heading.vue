<script setup lang="ts">
const colorClasses = {
  black: 'text-textBlack',
  brand: 'text-brand',
  white: 'text-white',
};

const typeClasses = {
  h1: 'text-[28px] font-semibold  lg:text-[44px] xl:text-[64px]',
  h2: 'text-[20px] font-semibold  lg:text-[40px] xl:text-[56px]',
  h3: 'text-4xl font-semibold  lg:text-5xl',
  h4: 'text-2xl font-medium  lg:text-[32px] xl:text-4xl',
  h5: 'text-[22px] font-medium  lg:text-xl xl:text-2xl',
  h6: 'text-lg font-medium  lg:text-xl xl:text-xl',
  h7: 'text-lg font-medium  lg:text-xl',
  h8: 'text-base font-semibold  lg:text-lg',
  h9: 'text-sm font-semibold  lg:text-base',
  h10: 'text-xs font-medium  lg:text-sm',
};

const props = defineProps({
  type: {
    type: String,
    default: 'h1',
    required: false,
  },
  color: {
    type: String,
    default: 'black',
    required: false,
  },
  marginBottom: {
    type: String,
    default: 'mb-8',
    required: false,
  },
});

const baseClass = computed(() => {
  return `${typeClasses[props.type] || typeClasses.h1} ${colorClasses[props.color] || colorClasses.black} text-balance text-center ${props.marginBottom} leading-tight tracking-normal`;
});
</script>

<template>
  <h1 v-if="type === 'h1'" :class="baseClass">
    <slot />
  </h1>
  <h2 v-if="type === 'h2'" :class="baseClass">
    <slot />
  </h2>
  <h3 v-if="type === 'h3'" :class="baseClass">
    <slot />
  </h3>
  <h4 v-if="type === 'h4'" :class="baseClass">
    <slot />
  </h4>
  <h5 v-if="type === 'h5'" :class="baseClass">
    <slot />
  </h5>
  <h6 v-if="type === 'h6'" :class="baseClass">
    <slot />
  </h6>
</template>
