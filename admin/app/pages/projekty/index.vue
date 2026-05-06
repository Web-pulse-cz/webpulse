<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Projekty');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/projekty',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = ref({
  search: null as string | null,
  paginate: 12 as number,
  page: 1 as number,
  orderBy: 'id' as string,
  orderWay: 'desc' as string,
});

const items = ref([]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/project', {
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
        detail: 'Nepodařilo se načíst projekty. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/project/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat projekt.',
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
  tableQuery.value.search = searchString.value;
  debouncedLoadItems();
});
watch(selectedSiteHash, () => loadItems());

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
      :actions="[{ type: 'add', text: 'Přidat projekt' }]"
      slug="projects"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
        {
          key: 'client.name',
          name: 'Klient',
          type: 'text',
          width: 150,
          hidden: true,
          sortable: false,
        },
        {
          key: 'status.name',
          name: 'Stav',
          type: 'badge',
          width: 100,
          hidden: false,
          sortable: false,
          colorKey: 'status.color',
        },
        {
          key: 'total_tracked_seconds',
          name: 'Čas',
          type: 'seconds',
          width: 80,
          hidden: true,
          sortable: true,
        },
        {
          key: 'total_revenue_with_vat',
          name: 'Celkem s DPH',
          type: 'number',
          width: 120,
          hidden: true,
          sortable: true,
        },
        { key: 'profit', name: 'Zisk', type: 'number', width: 100, hidden: true, sortable: true },
      ]"
      :actions="[{ type: 'edit', hash: '#prehled' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Projekt"
      plural="Projekty"
      :query="tableQuery"
      slug="projects"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
