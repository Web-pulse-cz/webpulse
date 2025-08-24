<script setup lang="ts">
import { ref, inject } from 'vue';
import { useActivityStore } from '~/../stores/activityStore';

const activityStore = useActivityStore();

const route = useRoute();
const router = useRouter();

const toast = useToast();
const pageTitle = ref('Statistiky');

const loading = ref(false);
const error = ref(false);

const filterDialogIsOpen = ref(false);

const tabs = ref([
  { name: 'Růst byznysu', link: '#byznys', current: true },
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
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst aktivity. Zkuste to prosím později.',
        color: 'red',
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
    router.push(route.path + '#byznys');
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
      @filter-dialog="filterDialogIsOpen = true"
    />
    <div>
      <div class="mt-5 block">
        <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow-sm" aria-label="Tabs">
          <NuxtLink
            v-for="(tab, index) in tabs"
            :key="index"
            :to="tab.link"
            class="group relative min-w-0 flex-1 overflow-hidden bg-white px-2 py-2.5 text-center text-xs font-medium text-grayCustom hover:bg-gray-50 hover:text-grayDark focus:z-10 lg:px-4 lg:py-4 lg:text-sm"
          >
            <span>{{ tab.name }}</span>
            <span
              aria-hidden="true"
              :class="
                tab.current
                  ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                  : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
              "
            />
          </NuxtLink>
        </nav>
      </div>
    </div>
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
      @submit="loadItems"
    />
  </div>
</template>
