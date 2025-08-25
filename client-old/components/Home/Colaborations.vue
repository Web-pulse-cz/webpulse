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
  logos: {
    type: Array as () => { src: string; alt?: string; link?: string }[],
    default: () => [],
  },
});
</script>

<template>
  <div class="flex flex-col items-center gap-6 text-center">
    <BasePropsHeading color="black" type="h4" margin-bottom="mb-6">
      {{ title }}
    </BasePropsHeading>

    <BasePropsParagraph class="mb-2">
      {{ description }}
    </BasePropsParagraph>

    <div class="mt-4 flex flex-wrap justify-center gap-4">
      <NuxtLink
        v-for="(logo, index) in logos"
        :key="index"
        :to="logo.link"
        class="block"
        target="_blank"
        rel="noopener"
      >
        <!-- when logos from api change sizes -->
        <img
          :src="logo.src"
          :alt="logo.alt"
          class="h-16 w-auto object-contain grayscale filter transition duration-300 hover:filter-none"
        />
      </NuxtLink>
    </div>
  </div>
</template>
