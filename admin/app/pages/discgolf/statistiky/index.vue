<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import { ChartBarIcon, PresentationChartLineIcon } from '@heroicons/vue/24/outline';
import StatsTable from '~/components/DiscGolf/StatsTable.vue';
import TrendChart from '~/components/DiscGolf/TrendChart.vue';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const pageTitle = ref('Statistiky hráčů');
const breadcrumbs = ref([{ name: pageTitle.value, link: '/discgolf/statistiky', current: true }]);

const loading = ref(false);
const error = ref(false);
const players = ref<any[]>([]);

const chartSeries = computed(() =>
  players.value
    .filter((p) => (p.chart ?? []).length > 0)
    .map((p) => ({ name: p.player_name, data: p.chart })),
);

async function loadStats() {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();
  await client('/api/admin/discgolf/stats/players', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((res: any) => {
      players.value = res.data ?? res ?? [];
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst statistiky hráčů.',
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

    <template v-else-if="!error">
      <LayoutContainer>
        <div class="mb-6 flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
          >
            <ChartBarIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Přehled hráčů</LayoutTitle>
        </div>
        <StatsTable :players="players" />
      </LayoutContainer>

      <LayoutContainer v-if="chartSeries.length">
        <div class="mb-6 flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
          >
            <PresentationChartLineIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Vývoj výkonu v čase (+/- par)</LayoutTitle>
        </div>
        <TrendChart :series="chartSeries" :height="380" />
      </LayoutContainer>
    </template>
  </div>
</template>
