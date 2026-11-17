<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Šablony zpráv');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: 'Šablony zpráv',
    link: '/sablony-zprav',
    current: false,
  },
]);

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25 as number,
    page: 1 as number,
    orderBy: 'id' as string,
    orderWay: 'desc' as string,
  },
  { searchString },
);

const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/message/blueprint', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
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
        detail: 'Nepodařilo se načíst šablony zpráv. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/message/blueprint/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku šablony.',
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

useHead({
  title: pageTitle,
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
      :actions="[{ type: 'add', text: 'Přidat šablonu' }]"
      slug="message_blueprints"
    />
    <BaseTable
      add-link="/sablony-zprav/pridat"
      add-label="Přidat šablonu"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 80, hidden: false, sortable: true },
        { key: 'type', name: 'Typ', type: 'text', width: 80, hidden: true, sortable: true },
        {
          key: 'updated_at',
          name: 'Poslední úprava',
          type: 'date',
          width: 80,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'copy', key: 'message' }, { type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Šablona zprávy"
      plural="Šablony zpráv"
      :query="tableQuery"
      slug="message_blueprints"
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
