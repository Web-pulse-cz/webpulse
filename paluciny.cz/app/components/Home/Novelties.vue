<script setup lang="ts">
const { locale } = useI18n();
const api = useApi();

const { data: novelties } = useAsyncData(
  'novelties',
  () => api.novelty.novelties(locale.value, 3),
  {
    watch: [locale],
  },
);
</script>

<template>
  <section
    v-if="novelties && novelties.data && novelties.data.length > 0"
    class="bg-cream px-6 py-16 lg:px-20 lg:py-24"
  >
    <div class="mx-auto max-w-[1200px]">
      <h2 class="mb-12 text-center font-display text-3xl font-bold text-earth md:text-4xl">
        Bleskovky
      </h2>

      <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        <div
          v-for="novelty in novelties.data"
          :key="novelty.id"
          class="rounded-2xl border border-cream-dark bg-white p-6 shadow-sm transition-all hover:shadow-md"
        >
          <div v-if="novelty.image" class="mb-4 overflow-hidden rounded-xl">
            <BaseImage
              :src="`/content/images/novelty/medium/${novelty.image}`"
              :alt="novelty.name"
              class="aspect-[16/9] w-full object-cover"
            />
          </div>

          <h3 class="mb-2 font-display text-lg font-bold text-earth">
            {{ novelty.name }}
          </h3>

          <p
            v-if="novelty.perex"
            class="mb-4 text-sm leading-relaxed text-earth-light"
            v-html="novelty.perex"
          />
        </div>
      </div>
    </div>
  </section>
</template>
