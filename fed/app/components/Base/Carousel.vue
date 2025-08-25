<script setup>
// If you are using PurgeCSS, make sure to whitelist the carousel CSS classes
import 'vue3-carousel/carousel.css';
import { Carousel, Slide, Navigation } from 'vue3-carousel';

const carouselConfig = {
  itemsToShow: 3.5,
  wrapAround: true,
};

const images = ref([
  'https://i.pinimg.com/736x/2c/d0/c6/2cd0c6bac68142b16a71f813fb055824.jpg',
  'https://i.pinimg.com/1200x/75/7e/33/757e33dd6a27cc3be9c4b6da1e222604.jpg',
  'https://i.pinimg.com/736x/a3/a9/7f/a3a97ffe1741a2ee460baa2b1567c6c0.jpg',
  'https://i.pinimg.com/736x/f0/34/c0/f034c0e217445d0e5c6e5e044387a985.jpg',
]);
</script>

<template>
  <Carousel v-bind="carouselConfig" :autoplay="3000" class="carousel" :gap="0">
    <Slide v-for="slide in images" :key="slide">
      <div class="carousel__item">
        <NuxtImg
          :src="slide"
          alt="Carousel image"
          class="xl:h-128 h-48 w-full rounded-xl object-cover sm:h-64 md:h-72 lg:h-96"
        />
      </div>
    </Slide>

    <template #addons>
      <Navigation />
    </template>
  </Carousel>
</template>
