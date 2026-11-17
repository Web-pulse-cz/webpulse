<script setup lang="ts">
import { inject, ref } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Hráči');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/discgolf/hraci', current: true }]);
const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25 as number,
    page: 1 as number,
    orderBy: 'position' as string,
    orderWay: 'asc' as string,
  },
  { searchString },
);

const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/discgolf/player', {
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
        detail: 'Nepodařilo se načíst hráče.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/discgolf/player/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat hráče.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
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
  if ((tableQuery.value.search ?? '') === (searchString.value ?? '')) return;
  tableQuery.value.search = searchString.value;
  tableQuery.value.page = 1;
  debouncedLoadItems();
});
watch(selectedSiteHash, () => loadItems());

useHead({
  title: pageTitle,
});
onMounted(() => loadItems());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'add', text: 'Přidat hráče' }]"
      slug="players"
    />
    <BaseTable
      add-link="/discgolf/hraci/pridat"
      add-label="Přidat hráče"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 80, hidden: true, sortable: true },
        { key: 'name', name: 'Jméno', type: 'text', width: 240, hidden: false, sortable: false },
        {
          key: 'games_count',
          name: 'Odehraných her',
          type: 'number',
          width: 140,
          hidden: false,
          sortable: false,
        },
        {
          key: 'include_in_stats',
          name: 'Ve statistikách',
          type: 'mapped',
          width: 140,
          hidden: false,
          sortable: false,
          map: {
            true: { label: 'Ano', class: 'bg-emerald-100 text-emerald-700' },
            false: { label: 'Ne', class: 'bg-slate-100 text-slate-500' },
          },
        },
        {
          key: 'position',
          name: 'Pořadí',
          type: 'number',
          width: 80,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Hráč"
      plural="Hráči"
      :query="tableQuery"
      slug="players"
      :filtered="!!tableQuery.search"
      @delete-item="deleteItem"
      @retry="loadItems"
      @clear-filters="searchString = ''"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
