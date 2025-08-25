<script setup>
// If you are using PurgeCSS, make sure to whitelist the carousel CSS classes
import 'vue3-carousel/carousel.css';
import { Carousel, Slide, Navigation } from 'vue3-carousel';

const carouselConfig = {
  wrapAround: true,
  autoplay: 5000,
  gap: 0,
  breakpoints: {
    1440: {
      itemsToShow: 4,
      height: 300,
    },
    1024: {
      itemsToShow: 3,
      height: 400,
    },
    640: {
      itemsToShow: 2,
      height: 400,
    },
    0: {
      itemsToShow: 1,
      height: 400,
    },
  },
};

const images = ref([
  'https://i.pinimg.com/736x/2c/d0/c6/2cd0c6bac68142b16a71f813fb055824.jpg',
  'https://i.pinimg.com/1200x/75/7e/33/757e33dd6a27cc3be9c4b6da1e222604.jpg',
  'https://i.pinimg.com/736x/a3/a9/7f/a3a97ffe1741a2ee460baa2b1567c6c0.jpg',
  'https://i.pinimg.com/736x/f0/34/c0/f034c0e217445d0e5c6e5e044387a985.jpg',
]);
</script>

<template>
  <Carousel v-bind="carouselConfig" class="carousel">
    <Slide v-for="slide in images" :key="slide">
      <div class="carousel__item">
        <NuxtImg :src="slide" alt="Carousel image" class="h-auto w-full rounded-lg object-cover" />
      </div>
    </Slide>

    <template #addons>
      <Navigation />
    </template>
  </Carousel>
</template>
