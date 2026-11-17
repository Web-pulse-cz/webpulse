<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Kalorická kalkulačka');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/restaurace/kalkulacka', current: true }]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25,
    page: 1,
    orderBy: 'updated_at',
    orderWay: 'desc',
  },
  { searchString },
);
const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/food/calorie-measurement', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      items.value = r;
      tableQuery.value.page = r.page;
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst měření.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/food/calorie-measurement/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku.',
        severity: 'error',
      });
    })
    .finally(() => {
      loadItems();
    });
}

function updateSort(column: string) {
  if (tableQuery.value.orderBy === column)
    tableQuery.value.orderWay = tableQuery.value.orderWay === 'asc' ? 'desc' : 'asc';
  else {
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
onMounted(() => {
  loadItems();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'add', text: 'Nová kalkulačka' }]"
      slug="calorie_measurements"
    />
    <BaseTable
      add-link="/restaurace/kalkulacka/pridat"
      add-label="Nová kalkulačka"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: true, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 260, hidden: false, sortable: true },
        {
          key: 'items_count',
          name: 'Počet potravin',
          type: 'number',
          width: 120,
          hidden: false,
          sortable: false,
        },
        {
          key: 'portions',
          name: 'Porcí',
          type: 'number',
          width: 80,
          hidden: false,
          sortable: true,
        },
        {
          key: 'updated_at',
          name: 'Upraveno',
          type: 'date',
          width: 140,
          hidden: false,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Měření"
      plural="Měření"
      :query="tableQuery"
      slug="calorie_measurements"
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
