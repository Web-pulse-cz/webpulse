<script setup lang="ts">
defineProps({
  title: {
    type: String,
    default: 'Default Title',
  },
  description: {
    type: String,
    default: 'Default Description',
  },
});
</script>

<template>
  <div class="w-1/3 flex-auto rounded-lg border border-secondary bg-primary p-6 lg:w-1/4">
    <BasePropsParagraph color="light" size="base" bold="bold" class="mb-2">
      {{ title }}
    </BasePropsParagraph>
    <BasePropsParagraph color="light" size="base">
      {{ description }}
    </BasePropsParagraph>
  </div>
</template>
