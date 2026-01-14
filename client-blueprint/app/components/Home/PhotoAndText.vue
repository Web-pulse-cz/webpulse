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

  image: {
    type: String,
    default: '',
  },
  imageAlt: {
    type: String,
    default: '',
  },

  link: {
    type: String,
    default: '',
  },
  photoPosition: {
    type: String,
    default: 'left',
  },
});
</script>

<template>
  <NuxtLink :to="link" class="flex flex-col items-center gap-4 md:flex-row">
    <div v-if="image && photoPosition === 'left'" class="w-full md:w-1/2">
      <img :src="image" :alt="imageAlt" class="h-96 w-96 object-contain" />
    </div>

    <div class="flex w-full flex-col text-center md:w-1/2 md:text-left">
      <BasePropsHeading color="black" type="h4" margin-bottom="lg:mb-12 mb-4">
        {{ title }}
      </BasePropsHeading>
      <BasePropsParagraph size="large" class="mb-2">
        {{ description }}
      </BasePropsParagraph>
    </div>

    <div v-if="image && photoPosition === 'right'" class="w-full md:w-1/2">
      <img :src="image" :alt="imageAlt" class="h-24 w-24 object-contain" />
    </div>
  </NuxtLink>
</template>

<style scoped></style>
