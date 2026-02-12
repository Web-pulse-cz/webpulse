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
    <BasePropsHeading color="black" type="h4" margin-bottom="lg:mb-2 mb-0">
      {{ title }}
    </BasePropsHeading>

    <BasePropsParagraph class="mb-0 lg:mb-2">
      {{ description }}
    </BasePropsParagraph>

    <div class="logo-carousel-wrapper w-full overflow-hidden">
      <div class="logo-carousel-track flex-wrap justify-center gap-4">
        <template v-for="n in 3" :key="n">
          <div class="flex gap-4 lg:gap-8">
            <NuxtLink
              v-for="(logo, index) in logos"
              :key="index"
              :to="logo.link"
              class="block"
              target="_blank"
              rel="noopener"
            >
              <img
                :src="logo.src"
                :alt="logo.alt"
                class="h-12 w-auto object-contain grayscale filter transition duration-300 hover:filter-none lg:h-16"
              />
            </NuxtLink>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.logo-carousel-wrapper {
  mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
}

.logo-carousel-track {
  display: flex;
  animation: scroll 20s linear infinite;
  width: max-content;
  gap: 2rem;
}

.logo-carousel-wrapper:hover .logo-carousel-track {
  animation-play-state: paused;
}

@keyframes scroll {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-33.333%);
  }
}
</style>
