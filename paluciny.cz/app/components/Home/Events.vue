<script setup lang="ts">
const { locale } = useI18n();
const localePath = useLocalePath();
const api = useApi();

const { data: events } = useAsyncData('homeEvents', () => api.event.events(locale.value, 3), {
  watch: [locale],
});

function formatDate(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('cs-CZ', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}
</script>

<template>
  <section
    v-if="events && events.data && events.data.length > 0"
    id="akce"
    class="bg-white px-6 py-16 lg:px-20 lg:py-24"
  >
    <div class="mx-auto max-w-[1200px]">
      <h2 class="mb-12 text-center font-display text-3xl font-bold text-earth md:text-4xl">
        Nejbližší události
      </h2>

      <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        <NuxtLink
          v-for="event in events.data"
          :key="event.id"
          :to="localePath({ name: 'akce-id-slug', params: { id: event.id, slug: event.slug } })"
          class="group rounded-2xl border border-cream-dark bg-cream/50 shadow-sm transition-all hover:shadow-md"
        >
          <div v-if="event.image" class="overflow-hidden rounded-t-2xl">
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

            <h3 class="mb-2 font-display text-lg font-bold text-earth group-hover:text-forest">
              {{ event.name }}
            </h3>

            <div class="mb-3 flex items-center gap-4 text-sm text-earth-light">
              <span v-if="event.start_date" class="flex items-center gap-1">
                <span class="material-symbols-outlined text-base text-bark">calendar_month</span>
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
    </div>
  </section>
</template>
