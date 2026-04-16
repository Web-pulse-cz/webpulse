<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale } = useI18n();

const { data: categoriesData } = useAsyncData(
  'categories',
  () => useApi().blog.categories(locale.value),
  {
    watch: [locale],
  },
);
</script>

<template>
  <section class="bg-cream px-6 py-16 lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <div class="mb-12">
        <h2 class="text-center font-display text-3xl font-bold text-earth">Fotogalerie</h2>
      </div>
      <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
        <BlogCategoryCard
          v-for="(category, index) in categoriesData"
          :key="index"
          :category="category"
          :index="index"
        />
      </div>
    </div>
  </section>
</template>
