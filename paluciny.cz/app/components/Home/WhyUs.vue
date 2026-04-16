<script setup lang="ts">
const features = [
  {
    icon: 'explore',
    title: 'Dobrodružství a hra',
    description:
      'Každá schůzka a výprava je plná napínavých her, záhad a výzev, které děti motivují a baví.',
  },
  {
    icon: 'holiday_village',
    title: 'Poznávání přírody',
    description:
      'Učíme děti rozpoznávat stromy, stopy zvířat a orientovat se v krajině. Příroda je naše největší učebna.',
  },
  {
    icon: 'wb_sunny',
    title: 'Rozvoj dovedností',
    description:
      'Spolupráce, odvaha, kreativita a samostatnost. Tyto dovednosti si děti odnáší do života.',
  },
];
</script>

<template>
  <section class="bg-cream px-6 py-16 lg:py-24">
    <div class="mx-auto max-w-[1200px]">
      <h2 class="mb-12 text-center font-display text-3xl font-bold text-earth md:text-4xl">
        Proč Palučiny?
      </h2>

      <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        <div
          v-for="(feature, index) in features"
          :key="index"
          class="rounded-2xl bg-white p-8 text-center shadow-sm transition-all hover:shadow-md"
        >
          <div
            class="mx-auto mb-6 flex size-16 items-center justify-center rounded-full bg-bark-light/20"
          >
            <span class="material-symbols-outlined text-3xl text-bark">{{ feature.icon }}</span>
          </div>

          <h3 class="mb-3 font-display text-xl font-bold text-earth">
            {{ feature.title }}
          </h3>

          <p class="text-sm leading-relaxed text-earth-light">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
