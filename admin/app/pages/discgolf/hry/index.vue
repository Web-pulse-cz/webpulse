<script setup lang="ts">
import { inject, ref } from 'vue';
import _ from 'lodash';
import { PlusIcon, ListBulletIcon, TableCellsIcon, TrophyIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Hry');

const loading = ref(false);
const error = ref(false);

const matrixLoading = ref(false);
const matrixError = ref(false);
const matrixRows = ref<any[]>([]);
const matrixLoaded = ref(false);

const cupLoading = ref(false);
const cupError = ref(false);
const cupRows = ref<any[]>([]);
const cupLoaded = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/discgolf/hry', current: true }]);
const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const activeTab = ref<'games' | 'matrix' | 'cup'>('games');

const tableQuery = ref({
  search: null as string | null,
  paginate: 25 as number,
  page: 1 as number,
  orderBy: 'played_at' as string,
  orderWay: 'desc' as string,
});

const items = ref([]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/discgolf/game', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      items.value = response;
      tableQuery.value.page = response.page;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst hry.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadMatrix() {
  matrixLoading.value = true;
  matrixError.value = false;
  const client = useSanctumClient();

  await client('/api/admin/discgolf/game/matrix', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      matrixRows.value = response.data || [];
      matrixLoaded.value = true;
    })
    .catch(() => {
      matrixError.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst záznamy her.',
        severity: 'error',
      });
    })
    .finally(() => {
      matrixLoading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/discgolf/game/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat hru.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function loadCup() {
  cupLoading.value = true;
  cupError.value = false;
  const client = useSanctumClient();

  await client('/api/admin/discgolf/game/cup', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      cupRows.value = response.data || [];
      cupLoaded.value = true;
    })
    .catch(() => {
      cupError.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst pořadí poháru.',
        severity: 'error',
      });
    })
    .finally(() => {
      cupLoading.value = false;
    });
}

function switchTab(tab: 'games' | 'matrix' | 'cup') {
  activeTab.value = tab;
  if (tab === 'matrix' && !matrixLoaded.value) loadMatrix();
  if (tab === 'cup' && !cupLoaded.value) loadCup();
}

function updateSort(column: string) {
  if (tableQuery.value.orderBy === column) {
    tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
  } else {
    tableQuery.value.orderBy = column;
    tableQuery.value.orderWay = 'asc';
  }
  loadItems();
}
function updatePage(page: number) {
  tableQuery.value.page = page;
  loadItems();
}
function updatePerPage(perPage: number) {
  tableQuery.value.paginate = perPage;
  tableQuery.value.page = 1;
  loadItems();
}

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => {
  tableQuery.value.search = searchString.value;
  debouncedLoadItems();
});
watch(selectedSiteHash, () => {
  loadItems();
  matrixLoaded.value = false;
  cupLoaded.value = false;
  if (activeTab.value === 'matrix') loadMatrix();
  if (activeTab.value === 'cup') loadCup();
});

useHead({ title: pageTitle.value });
onMounted(() => loadItems());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-4">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex gap-2 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200">
        <button
          type="button"
          class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
          :class="
            activeTab === 'games'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-slate-500 hover:text-slate-900'
          "
          @click="switchTab('games')"
        >
          <ListBulletIcon class="size-4" />
          Hry
        </button>
        <button
          type="button"
          class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
          :class="
            activeTab === 'matrix'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-slate-500 hover:text-slate-900'
          "
          @click="switchTab('matrix')"
        >
          <TableCellsIcon class="size-4" />
          Záznamy
        </button>
        <button
          type="button"
          class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors"
          :class="
            activeTab === 'cup'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-slate-500 hover:text-slate-900'
          "
          @click="switchTab('cup')"
        >
          <TrophyIcon class="size-4" />
          Gellerův pohár
        </button>
      </div>

      <NuxtLink
        to="/discgolf/hry/vytvorit"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
      >
        <PlusIcon class="size-5" />
        Vytvořit hru
      </NuxtLink>
    </div>

    <div v-show="activeTab === 'games'">
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: true, sortable: true },
          {
            key: 'played_at',
            name: 'Datum',
            type: 'date',
            width: 120,
            hidden: false,
            sortable: true,
          },
          {
            key: 'course_name',
            name: 'Hřiště',
            type: 'text',
            width: 200,
            hidden: false,
            sortable: false,
          },
          {
            key: 'layout_name',
            name: 'Layout',
            type: 'text',
            width: 160,
            hidden: false,
            sortable: false,
          },
          {
            key: 'par',
            name: 'Par',
            type: 'number',
            width: 80,
            hidden: false,
            sortable: false,
          },
          {
            key: 'status',
            name: 'Stav',
            type: 'mapped',
            width: 140,
            hidden: false,
            sortable: false,
            map: {
              draft: { label: 'Koncept', class: 'bg-slate-100 text-slate-600' },
              in_progress: { label: 'Probíhá', class: 'bg-amber-100 text-amber-700' },
              completed: { label: 'Dokončeno', class: 'bg-emerald-100 text-emerald-700' },
            },
          },
          {
            key: 'players_count',
            name: 'Hráčů',
            type: 'number',
            width: 90,
            hidden: false,
            sortable: false,
          },
          {
            key: 'winner_name',
            name: 'Vítěz',
            type: 'text',
            width: 160,
            hidden: false,
            sortable: false,
          },
          {
            key: 'count_to_cup',
            name: 'Do poháru',
            type: 'status',
            width: 100,
            hidden: false,
            sortable: false,
          },
        ]"
        :actions="[{ type: 'edit', path: '/discgolf/hry' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Hra"
        plural="Hry"
        :query="tableQuery"
        slug="games"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
        @update-per-page="updatePerPage"
      />
    </div>

    <div v-show="activeTab === 'matrix'">
      <DiscGolfGamesMatrix :rows="matrixRows" :loading="matrixLoading" :error="matrixError" />
    </div>

    <div v-show="activeTab === 'cup'">
      <DiscGolfCupStandings :rows="cupRows" :loading="cupLoading" :error="cupError" />
    </div>
  </div>
</template>
