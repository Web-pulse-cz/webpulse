<script setup lang="ts">
const { locale } = useI18n();
const localePath = useLocalePath();
const api = useApi();

const { data: categories } = useAsyncData(
  'homeEventCategories',
  () => api.event.eventCategories(locale.value),
  {
    watch: [locale],
  },
);

const categoryIcons = ['forest', 'camping', 'sunny', 'hiking', 'park', 'eco'];
</script>

<template>
  <section
    v-if="categories && categories.length > 0"
    class="bg-cream-dark/50 px-6 py-16 lg:px-20 lg:py-24"
  >
    <div class="mx-auto max-w-[1200px]">
      <h2 class="mb-12 text-center font-display text-3xl font-bold text-earth md:text-4xl">
        Typy akcí
      </h2>

      <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4">
        <NuxtLink
          v-for="(category, index) in categories"
          :key="category.id"
          :to="localePath({ name: 'akce', query: { category: category.id } })"
          class="group rounded-2xl bg-white p-6 text-center shadow-sm transition-all hover:shadow-md"
        >
          <div
            class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-forest/10 transition-colors group-hover:bg-forest/20"
          >
            <span class="material-symbols-outlined text-2xl text-forest">
              {{ categoryIcons[index % categoryIcons.length] }}
            </span>
          </div>

          <h3
            class="font-display text-base font-bold text-earth group-hover:text-forest md:text-lg"
          >
            {{ category.name }}
          </h3>

          <p
            v-if="category.perex"
            class="mt-2 line-clamp-2 text-xs leading-relaxed text-earth-light"
            v-html="category.perex"
          />
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
