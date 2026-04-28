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
  <section class="border-b border-slate-200 bg-white px-6 py-16 lg:px-20">
    <div class="mb-12 flex flex-col justify-between gap-4 md:flex-row md:items-end">
      <div>
        <h2 class="text-3xl font-bold text-slate-900">Browse by Category</h2>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
      <BlogCategoryCard
        v-for="(category, index) in categoriesData"
        :key="index"
        :category="category"
        :index="index"
      />
    </div>
  </section>
</template>
