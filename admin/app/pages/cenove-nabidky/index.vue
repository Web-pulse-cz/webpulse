<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';

import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Cenové nabídky');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/cenove-nabidky',
    current: true,
  },
]);

const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const searchString = ref(inject('searchString', ''));
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

  await client<{ id: number }>('/api/admin/price-offer', {
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
        detail: 'Nepodařilo se načíst cenové nabídky.',
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

  await client<{ id: number }>('/api/admin/price-offer/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat cenovou nabídku.',
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

useHead({
  title: pageTitle.value,
});

watch(selectedSiteHash, () => loadItems());

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
      :actions="[{ type: 'add', text: 'Přidat nabídku' }]"
      slug="price_offers"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'code', name: 'Kód', type: 'text', width: 120, hidden: false, sortable: true },
        { key: 'title', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
        {
          key: 'client.name',
          name: 'Klient',
          type: 'text',
          width: 150,
          hidden: true,
          sortable: false,
        },
        {
          key: 'total_with_vat',
          name: 'Celkem s DPH',
          type: 'number',
          width: 120,
          hidden: true,
          sortable: true,
        },
        {
          key: 'status',
          name: 'Stav',
          type: 'mapped',
          width: 100,
          hidden: false,
          sortable: true,
          map: {
            draft: { label: 'Koncept', class: 'bg-slate-100 text-slate-600' },
            sent: { label: 'Odeslaná', class: 'bg-blue-100 text-blue-700' },
            accepted: { label: 'Přijatá', class: 'bg-emerald-100 text-emerald-700' },
            rejected: { label: 'Zamítnutá', class: 'bg-red-100 text-red-700' },
            expired: { label: 'Vypršelá', class: 'bg-amber-100 text-amber-700' },
          },
        },
        {
          key: 'valid_to',
          name: 'Platnost do',
          type: 'text',
          width: 100,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Cenová nabídka"
      plural="Cenové nabídky"
      :query="tableQuery"
      slug="price_offers"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
