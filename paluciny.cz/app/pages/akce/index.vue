<script setup lang="ts">
const { locale } = useI18n();
const api = useApi();
const route = useRoute();

const selectedCategory = ref<number | null>(
  route.query.category ? Number(route.query.category) : null,
);

const tableQuery = ref({
  paginate: 12,
  page: 1,
});

const { data: categories } = useAsyncData(
  'eventCategories',
  () => api.event.eventCategories(locale.value),
  { watch: [locale] },
);

const { data: eventsData } = useAsyncData(
  () => `events-${selectedCategory.value}-${tableQuery.value.page}`,
  () =>
    api.event.events(locale.value, tableQuery.value.paginate, selectedCategory.value || undefined),
  {
    watch: [locale, selectedCategory],
  },
);

function selectCategory(id: number | null) {
  selectedCategory.value = id;
  tableQuery.value.page = 1;
}

async function updatePage(page: number) {
  tableQuery.value.page = page;
  eventsData.value = await api.event.events(
    locale.value,
    tableQuery.value.paginate,
    selectedCategory.value || undefined,
  );
}

function formatDate(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('cs-CZ', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

useHead({
  title: 'Akce | Palučiny',
  meta: [
    { name: 'description', content: 'Přehled všech akcí a událostí Palučiny.' },
    { property: 'og:title', content: 'Akce | Palučiny' },
    { property: 'og:description', content: 'Přehled všech akcí a událostí Palučiny.' },
  ],
});
</script>

<template>
  <div class="min-h-screen bg-cream">
    <!-- Header -->
    <div class="bg-forest px-6 py-16 text-center lg:py-24">
      <div class="mx-auto max-w-[1200px]">
        <span
          class="mb-6 inline-block rounded-full bg-white/10 px-5 py-2 text-sm font-semibold uppercase tracking-widest text-white/70"
        >
          Události
        </span>
        <h1 class="font-display text-4xl font-bold text-white md:text-5xl">Akce a události</h1>
      </div>
    </div>

    <!-- Category filter -->
    <div
      v-if="categories && categories.length > 0"
      class="border-b border-cream-dark bg-white px-6"
    >
      <div class="mx-auto flex max-w-[1200px] gap-2 overflow-x-auto py-4">
        <button
          class="shrink-0 rounded-full px-5 py-2 text-sm font-medium transition-colors"
          :class="
            selectedCategory === null
              ? 'bg-forest text-white'
              : 'bg-cream text-earth hover:bg-cream-dark'
          "
          @click="selectCategory(null)"
        >
          Vše
        </button>
        <button
          v-for="cat in categories"
          :key="cat.id"
          class="shrink-0 rounded-full px-5 py-2 text-sm font-medium transition-colors"
          :class="
            selectedCategory === cat.id
              ? 'bg-forest text-white'
              : 'bg-cream text-earth hover:bg-cream-dark'
          "
          @click="selectCategory(cat.id)"
        >
          {{ cat.name }}
        </button>
      </div>
    </div>

    <!-- Events grid -->
    <div class="px-6 py-12 lg:px-20 lg:py-16">
      <div class="mx-auto max-w-[1200px]">
        <div
          v-if="eventsData && eventsData.data && eventsData.data.length > 0"
          class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3"
        >
          <NuxtLink
            v-for="event in eventsData.data"
            :key="event.id"
            :to="localePath({ name: 'akce-id-slug', params: { id: event.id, slug: event.slug } })"
            class="group overflow-hidden rounded-2xl border border-cream-dark bg-white shadow-sm transition-all hover:shadow-md"
          >
            <div v-if="event.image" class="overflow-hidden">
              <BaseImage
                :src="`/content/images/event/medium/${event.image}`"
                :alt="event.name"
                class="aspect-[16/9] w-full object-cover transition-transform group-hover:scale-105"
              />
            </div>

            <div class="p-6">
              <div class="mb-3 flex flex-wrap items-center gap-2">
                <span
                  v-if="event.category"
                  class="rounded-full bg-forest/10 px-3 py-1 text-xs font-medium text-forest"
                >
                  {{ event.category.name }}
                </span>
                <span
                  v-if="event.is_online"
                  class="rounded-full bg-bark-light/20 px-3 py-1 text-xs font-medium text-bark"
                >
                  Online
                </span>
              </div>

              <h2
                class="mb-2 font-display text-xl font-bold text-earth transition-colors group-hover:text-forest"
              >
                {{ event.name }}
              </h2>

              <div class="mb-2 flex items-center gap-1 text-sm text-earth-light">
                <span class="material-symbols-outlined text-base text-bark">calendar_month</span>
                <span v-if="event.start_date">
                  {{ formatDate(event.start_date) }}
                  <template v-if="event.end_date"> – {{ formatDate(event.end_date) }} </template>
                </span>
              </div>

              <div v-if="event.place" class="mb-3 flex items-center gap-1 text-sm text-earth-light">
                <span class="material-symbols-outlined text-base text-bark">location_on</span>
                {{ event.place }}
              </div>

              <p
                v-if="event.perex"
                class="line-clamp-2 text-sm leading-relaxed text-earth-light"
                v-html="event.perex"
              />
            </div>
          </NuxtLink>
        </div>

        <!-- Empty state -->
        <div
          v-else-if="eventsData && eventsData.data && eventsData.data.length === 0"
          class="py-16 text-center"
        >
          <span class="material-symbols-outlined mb-4 text-5xl text-earth-light/40"
            >event_busy</span
          >
          <p class="text-lg text-earth-light">Žádné události v této kategorii.</p>
        </div>

        <!-- Pagination -->
        <div v-if="eventsData && eventsData.lastPage > 1" class="mt-12 flex justify-center gap-2">
          <button
            v-for="page in eventsData.lastPage"
            :key="page"
            class="size-10 rounded-full text-sm font-medium transition-colors"
            :class="
              tableQuery.page === page
                ? 'bg-forest text-white'
                : 'bg-white text-earth hover:bg-cream-dark'
            "
            @click="updatePage(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
