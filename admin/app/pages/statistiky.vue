<script setup lang="ts">
import { ref } from 'vue';

import { useActivityStore } from '~/../stores/activityStore';
import ChartContacts from '~/components/Statistics/ChartContacts.vue';

const activityStore = useActivityStore();

const route = useRoute();
const router = useRouter();

const { $toast } = useNuxtApp();
const pageTitle = ref('Statistiky');

const loading = ref(false);
const error = ref(false);

const filterDialogIsOpen = ref(false);

const tabs = ref([
  { name: 'Kontakty', link: '#kontakty', current: true },
  { name: 'Růst byznysu', link: '#byznys', current: false },
  { name: 'Osobní růst', link: '#osobni', current: false },
  { name: 'Cashflow', link: '#cashflow', current: false },
]);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/statistiky',
    current: true,
  },
]);

const tableQuery = ref({
  filter: 'month' as string,
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  // current date - 3 months to input
  from: new Date(new Date().setMonth(new Date().getMonth() - 3)).toISOString().split('T')[0],
  to: null as string | null,
});

const items = ref(null);

async function loadItems() {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/statistics', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      items.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst aktivity. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watchEffect(() => {
  const routeTabHash = route.hash;
  if (routeTabHash && routeTabHash !== '') {
    tabs.value.forEach((tab) => {
      tab.current = tab.link === routeTabHash;
    });
  } else {
    tabs.value[0].current = true;
    router.push(route.path + '#kontakty');
  }
});

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  loadItems();
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[
        {
          type: 'filter-dialog',
          text: 'Filtrovat',
        },
      ]"
      :modify-bottom="false"
      @filter-dialog="filterDialogIsOpen = true"
    />
    <LayoutTabs :tabs="tabs" />
    <template v-if="tabs.find((tab) => tab.current && tab.link === '#kontakty')">
      <!--      <StatisticsContacts /> -->
      <LayoutContainer v-if="items && !error && !loading">
        <ChartContacts :items="items.contacts" />
      </LayoutContainer>
    </template>
    <template v-if="tabs.find((tab) => tab.current && tab.link === '#byznys')">
      <!--      <StatisticsStatsBusinessGrowth /> -->
      <LayoutContainer v-if="items && !error && !loading">
        <StatisticsChartBusinessGrowth :items="items" :activities="activityStore.activities" />
        <StatisticsChartBusinessGrowthHeatmap
          :items="items"
          :activities="activityStore.activities"
        />
      </LayoutContainer>
    </template>
    <template v-if="tabs.find((tab) => tab.current && tab.link === '#osobni')">
      <LayoutContainer v-if="items && !error && !loading">
        <StatisticsChartPersonalGrowth :items="items" :activities="activityStore.activities" />
      </LayoutContainer>
    </template>
    <template v-if="tabs.find((tab) => tab.current && tab.link === '#cashflow')">
      <LayoutContainer v-if="items && !error && !loading">
        <StatisticsChartCashflow :items="items" :activities="activityStore.activities" />
      </LayoutContainer>
    </template>
    <StatisticsDialogFilter
      v-model:show="filterDialogIsOpen"
      v-model:filter="tableQuery.filter"
      v-model:year="tableQuery.year"
      v-model:month="tableQuery.month"
      v-model:from="tableQuery.from"
      v-model:to="tableQuery.to"
      @submit="loadItems"
    />
  </div>
</template>
