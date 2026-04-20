<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute, useRuntimeConfig, useHead, createError } from '#app';

const localePath = useLocalePath();
const { locale } = useI18n();
const route = useRoute();
const api = useApi();

const {
  data: eventData,
  error: eventError,
  pending: eventPending,
} = useAsyncData(
  () => `eventDetail-${route.params.id}`,
  () =>
    api.event.eventDetail(route.params.id, locale.value).catch(() => {
      throw createError({
        statusCode: 404,
        statusMessage: 'Page Not Found',
      });
    }),
  {
    watch: [() => route.params.id, locale],
  },
);

// Registration form
const regForm = ref({
  firstname: '',
  lastname: '',
  email: '',
});
const regSending = ref(false);
const regSent = ref(false);
const regError = ref('');

async function handleRegister() {
  if (regSending.value || !eventData.value) return;
  regSending.value = true;
  regError.value = '';

  try {
    await api.event.eventRegister({ ...regForm.value, event_id: eventData.value.id }, locale.value);
    regSent.value = true;
    regForm.value = { firstname: '', lastname: '', email: '' };
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } };
    regError.value = err?.data?.message || 'Nepodařilo se odeslat registraci.';
  } finally {
    regSending.value = false;
  }
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

function formatDateTime(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('cs-CZ', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

const pageMeta = computed(() => ({
  title: eventData.value?.name ? `${eventData.value.name} | Palučiny` : 'Událost | Palučiny',
  description: eventData.value?.perex || 'Detail události Palučiny.',
  meta_title: eventData.value?.meta_title || eventData.value?.name || 'Událost',
  meta_description: eventData.value?.meta_description || eventData.value?.perex || '',
}));

useHead(() => ({
  title: pageMeta.value.title,
  meta: [
    { name: 'description', content: pageMeta.value.description },
    { property: 'og:title', content: pageMeta.value.meta_title },
    { property: 'og:description', content: pageMeta.value.meta_description },
  ],
  link: [
    {
      rel: 'canonical',
      href: `${useRuntimeConfig().public.appUrl}/akce/${route.params.id}/${route.params.slug}`,
    },
  ],
}));
</script>

<template>
  <div class="min-h-screen bg-cream">
    <!-- Loading -->
    <div v-if="eventPending" class="flex min-h-[50vh] items-center justify-center">
      <div
        class="size-16 animate-spin rounded-full border-4 border-forest/20 border-t-forest"
      ></div>
    </div>

    <template v-else-if="!eventError && eventData">
      <!-- Hero image -->
      <div v-if="eventData.image" class="relative h-[40vh] md:h-[50vh]">
        <BaseImage
          :src="`/content/images/event/large/${eventData.image}`"
          :alt="eventData.name"
          class="absolute inset-0 h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-earth/60 via-earth/20 to-transparent" />
      </div>

      <div class="mx-auto max-w-[1200px] px-6 lg:px-20">
        <div class="relative -mt-24 pb-16 lg:pb-24" :class="{ 'pt-16 lg:pt-24': !eventData.image }">
          <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2">
              <article class="overflow-hidden rounded-2xl bg-white p-8 shadow-sm md:p-12">
                <div class="mb-6 flex flex-wrap items-center gap-3">
                  <NuxtLink
                    v-if="eventData.category"
                    :to="localePath({ name: 'akce', query: { category: eventData.category.id } })"
                    class="rounded-full bg-forest px-4 py-1.5 text-sm font-medium text-white transition-colors hover:bg-forest-dark"
                  >
                    {{ eventData.category.name }}
                  </NuxtLink>
                  <span
                    v-if="eventData.is_online"
                    class="rounded-full bg-bark-light/20 px-4 py-1.5 text-sm font-medium text-bark"
                  >
                    Online
                  </span>
                </div>

                <h1
                  class="mb-6 font-display text-3xl font-bold leading-tight text-earth md:text-4xl lg:text-5xl"
                >
                  {{ eventData.name }}
                </h1>

                <p
                  v-if="eventData.perex"
                  class="mb-8 text-lg leading-relaxed text-earth-light"
                  v-html="eventData.perex"
                />

                <div
                  v-if="eventData.text"
                  class="article-content text-base leading-relaxed text-earth-light md:text-lg"
                  v-html="eventData.text"
                />
              </article>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
              <!-- Info card -->
              <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-5 font-display text-lg font-bold text-earth">Informace</h3>

                <div class="space-y-4">
                  <div v-if="eventData.start_date" class="flex items-start gap-3">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-bark"
                      >calendar_month</span
                    >
                    <div>
                      <p class="text-sm font-medium text-earth">Datum</p>
                      <p class="text-sm text-earth-light">
                        {{ formatDate(eventData.start_date) }}
                        <template v-if="eventData.end_date">
                          – {{ formatDate(eventData.end_date) }}
                        </template>
                      </p>
                    </div>
                  </div>

                  <div v-if="eventData.place" class="flex items-start gap-3">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-bark"
                      >location_on</span
                    >
                    <div>
                      <p class="text-sm font-medium text-earth">Místo</p>
                      <p class="text-sm text-earth-light">{{ eventData.place }}</p>
                    </div>
                  </div>

                  <div v-if="eventData.price" class="flex items-start gap-3">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-bark">payments</span>
                    <div>
                      <p class="text-sm font-medium text-earth">Cena</p>
                      <p class="text-sm text-earth-light">
                        {{ eventData.price }}
                        {{ eventData.currency ? eventData.currency.code : 'Kč' }}
                      </p>
                    </div>
                  </div>

                  <div v-if="eventData.max_participants" class="flex items-start gap-3">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-bark">group</span>
                    <div>
                      <p class="text-sm font-medium text-earth">Kapacita</p>
                      <p class="text-sm text-earth-light">
                        max. {{ eventData.max_participants }} účastníků
                      </p>
                    </div>
                  </div>

                  <div v-if="eventData.registration_from" class="flex items-start gap-3">
                    <span class="material-symbols-outlined mt-0.5 text-xl text-bark">schedule</span>
                    <div>
                      <p class="text-sm font-medium text-earth">Registrace od</p>
                      <p class="text-sm text-earth-light">
                        {{ formatDateTime(eventData.registration_from) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Registration form -->
              <div
                v-if="eventData.registration_required"
                class="overflow-hidden rounded-2xl bg-forest p-6 shadow-sm"
              >
                <h3 class="mb-5 font-display text-lg font-bold text-white">Registrace</h3>

                <div v-if="regSent" class="py-4 text-center">
                  <span class="material-symbols-outlined mb-3 text-4xl text-leaf"
                    >check_circle</span
                  >
                  <p class="text-white">Registrace proběhla úspěšně!</p>
                </div>

                <form v-else class="space-y-4" @submit.prevent="handleRegister">
                  <div>
                    <input
                      v-model="regForm.firstname"
                      type="text"
                      required
                      placeholder="Jméno"
                      class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
                    />
                  </div>
                  <div>
                    <input
                      v-model="regForm.lastname"
                      type="text"
                      required
                      placeholder="Příjmení"
                      class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
                    />
                  </div>
                  <div>
                    <input
                      v-model="regForm.email"
                      type="email"
                      required
                      placeholder="E-mail"
                      class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/50 focus:border-leaf focus:ring-leaf"
                    />
                  </div>

                  <p v-if="regError" class="text-sm text-red-300">{{ regError }}</p>

                  <button
                    type="submit"
                    :disabled="regSending"
                    class="w-full rounded-xl bg-bark px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-bark-light disabled:opacity-50"
                  >
                    {{ regSending ? 'Odesílám...' : 'Registrovat se' }}
                  </button>
                </form>
              </div>

              <!-- Back link -->
              <NuxtLink
                :to="localePath({ name: 'akce' })"
                class="inline-flex items-center gap-2 text-sm font-medium text-forest transition-colors hover:text-forest-dark"
              >
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Zpět na přehled akcí
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.article-content :deep(h2) {
  @apply mb-6 mt-12 font-display text-3xl font-bold leading-tight text-earth;
}
.article-content :deep(h3) {
  @apply mb-4 mt-10 font-display text-2xl font-bold text-earth;
}
.article-content :deep(p) {
  @apply mb-0;
}
.article-content :deep(a) {
  @apply font-semibold text-forest underline decoration-forest/40 decoration-2 underline-offset-4 transition-all hover:decoration-forest;
}
.article-content :deep(ul) {
  @apply mb-0 list-inside list-disc space-y-2;
}
.article-content :deep(li) {
  @apply pl-2;
}
.article-content :deep(blockquote) {
  @apply my-8 rounded-r-xl border-l-4 border-leaf bg-leaf/10 p-6 text-xl italic text-earth;
}
.article-content :deep(img) {
  @apply my-10 h-auto max-w-full rounded-xl;
}
.article-content :deep(strong) {
  @apply font-bold text-earth;
}
</style>
