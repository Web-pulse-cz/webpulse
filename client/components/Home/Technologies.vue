<script setup lang="ts">
const { t } = useI18n();

const technologies = [
  { src: '/static/img/icon/technologies/laravel.svg', alt: 'Laravel' },
  { src: '/static/img/icon/technologies/docker.svg', alt: 'Docker' },
  { src: '/static/img/icon/technologies/aws.svg', alt: 'Amazon web services' },
  { src: '/static/img/icon/technologies/nodejs.svg', alt: 'Node.js' },
  { src: '/static/img/icon/technologies/npm.svg', alt: 'Npm' },
  { src: '/static/img/icon/technologies/php.svg', alt: 'PHP' },
  { src: '/static/img/icon/technologies/nuxt.svg', alt: 'Nuxt.js' },
  { src: '/static/img/icon/technologies/js.svg', alt: 'JavaScript' },
  { src: '/static/img/icon/technologies/github.svg', alt: 'Github' },
  { src: '/static/img/icon/technologies/figma.svg', alt: 'Figma' },
  { src: '/static/img/icon/technologies/mysql.svg', alt: 'MySQL' },
];
</script>

<template>
  <div class="text-center">
    <BasePropsHeading type="h2" class="mb-6 text-center">
      {{ t('home.technologiesTitle') }}
    </BasePropsHeading>
    <div class="flex flex-wrap justify-center gap-x-6">
      <div v-for="(tech, index) in technologies" :key="index" class="img-container">
        <NuxtImg :src="tech.src" :alt="tech.alt" :lazyload="true" format="webp" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.img-container {
  width: 96px;
  height: 96px;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.img-container img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>
