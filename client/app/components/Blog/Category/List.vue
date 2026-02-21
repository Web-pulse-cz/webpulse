<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useApi } from '~/../app/composables/useApi';
import { useAsyncData } from '#app';

const { locale, t } = useI18n();

const {
  data: categoriesData,
  status: categoriesStatus,
  error: categoriesError,
  pending: categoriesPending,
} = useAsyncData('categories', () => useApi().blog.categories(locale.value), {
  watch: [locale],
});
</script>

<template>
  <section class="border-y-4 border-deep-blue bg-white px-6 py-16 lg:px-20">
    <div class="mb-12 flex flex-col justify-between gap-4 md:flex-row md:items-end">
      <div>
        <span class="text-sm font-black uppercase tracking-[0.3em] text-primary">Pick a path</span>
        <h2 class="font-display text-5xl font-black text-deep-blue">Browse by Category</h2>
      </div>
      <a
        class="group flex items-center gap-2 rounded-xl border-2 border-deep-blue bg-turquoise px-6 py-3 font-black shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] transition-all hover:translate-x-1 hover:translate-y-1 hover:shadow-none"
        href="#"
      >
        View all
        <span class="material-symbols-outlined transition-transform group-hover:translate-x-1"
          >arrow_forward</span
        >
      </a>
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
