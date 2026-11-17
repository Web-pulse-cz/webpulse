<script setup lang="ts">
import { inject, ref } from 'vue';
import { MapPinIcon } from '@heroicons/vue/24/outline';
import StatsTable from '~/components/DiscGolf/StatsTable.vue';
import TrendChart from '~/components/DiscGolf/TrendChart.vue';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const pageTitle = ref('Statistiky hřišť');
const breadcrumbs = ref([
  { name: pageTitle.value, link: '/discgolf/statistiky-hriste', current: true },
]);

const loading = ref(false);
const error = ref(false);
const courses = ref<any[]>([]);

function courseSeries(course: any) {
  return (course.players ?? [])
    .filter((p: any) => (p.chart ?? []).length > 0)
    .map((p: any) => ({ name: p.player_name, data: p.chart }));
}

async function loadStats() {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();
  await client('/api/admin/discgolf/stats/courses', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((res: any) => {
      courses.value = res.data ?? res ?? [];
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst statistiky hřišť.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({
  title: pageTitle,
});
watch(selectedSiteHash, () => loadStats());
onMounted(() => loadStats());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <div v-if="loading" class="py-16 text-center text-sm italic text-slate-400">
      Načítání statistik…
    </div>

    <div
      v-else-if="!error && !courses.length"
      class="py-16 text-center text-sm italic text-slate-400"
    >
      Zatím žádná data. Dokončete alespoň jednu hru na některém hřišti.
    </div>

    <template v-else-if="!error">
      <LayoutContainer v-for="course in courses" :key="course.course_name" class="space-y-6">
        <div class="flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
          >
            <MapPinIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">{{ course.course_name }}</LayoutTitle>
        </div>

        <StatsTable :players="course.players ?? []" show-recommended-handicap />

        <TrendChart
          v-if="courseSeries(course).length"
          :series="courseSeries(course)"
          :height="320"
        />
      </LayoutContainer>
    </template>
  </div>
</template>
