<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Stoly');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/restaurace/stoly', current: true }]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = ref({
  search: null as string | null,
  paginate: 12,
  page: 1,
  orderBy: 'position',
  orderWay: 'asc',
});
const items = ref([]);

const statusLabels: Record<string, string> = {
  available: 'Volný',
  occupied: 'Obsazený',
  reserved: 'Rezervovaný',
  maintenance: 'Údržba',
};

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/food/table', {
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
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst stoly.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/food/table/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).finally(() => {
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
  tableQuery.value.search = searchString.value;
  debouncedLoadItems();
});
watch(selectedSiteHash, () => loadItems());

useHead({ title: pageTitle.value });
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
      :actions="[{ type: 'add', text: 'Přidat stůl' }]"
      slug="restaurant_tables"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'number', name: 'Číslo', type: 'text', width: 80, hidden: false, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 150, hidden: true, sortable: true },
        { key: 'seats', name: 'Míst', type: 'number', width: 60, hidden: false, sortable: true },
        {
          key: 'location',
          name: 'Umístění',
          type: 'text',
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
            available: { label: 'Volný', class: 'bg-emerald-100 text-emerald-700' },
            occupied: { label: 'Obsazený', class: 'bg-red-100 text-red-700' },
            reserved: { label: 'Rezervovaný', class: 'bg-amber-100 text-amber-700' },
            maintenance: { label: 'Údržba', class: 'bg-slate-100 text-slate-600' },
          },
        },
        {
          key: 'upcoming_count',
          name: 'Rezervací',
          type: 'number',
          width: 80,
          hidden: false,
          sortable: false,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Stůl"
      plural="Stoly"
      :query="tableQuery"
      slug="restaurant_tables"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
