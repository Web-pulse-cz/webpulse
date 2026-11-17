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
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
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

async function bulkDeleteItems(ids: number[]) {
  const client = useSanctumClient();
  loading.value = true;

  const results = await Promise.allSettled(
    ids.map((id) =>
      client('/api/admin/project/' + id, {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      }),
    ),
  );

  const failed = results.filter((result) => result.status === 'rejected').length;
  const deleted = results.length - failed;
  if (deleted) {
    $toast.show({ summary: 'Smazáno', detail: `Smazáno položek: ${deleted}`, severity: 'success' });
  }
  if (failed) {
    $toast.show({
      summary: 'Chyba',
      detail: `Nepodařilo se smazat ${failed} z ${results.length} položek.`,
      severity: 'error',
    });
  }

  loading.value = false;
  loadItems();
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
      add-link="/projekty/pridat"
      add-label="Přidat projekt"
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
      :filtered="!!tableQuery.search"
      selectable
      @delete-item="deleteItem"
      @retry="loadItems"
      @clear-filters="searchString = ''"
      @bulk-delete="bulkDeleteItems"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
