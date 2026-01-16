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
});
</script>

<template>
  <div class="flex h-96 w-80 justify-center text-center">
    <div
      class="hover:shadow-redShadow w-1/3 flex-auto rounded-lg border border-white bg-white p-6 shadow-xl shadow-gray-200 transition-all duration-1000 lg:w-1/4"
    >
      <NuxtLink :to="link">
        <div class="flex justify-center">
          <img v-if="image" :src="image" :alt="imageAlt" class="mb-4 h-24 w-24 object-contain" />
        </div>
        <BasePropsHeading color="black" type="h5" margin-bottom="mb-12">
          {{ title }}
        </BasePropsHeading>
        <BasePropsParagraph class="mb-2">
          {{ description }}
        </BasePropsParagraph>
      </NuxtLink>
    </div>
  </div>
</template>
