<script setup lang="ts">
import { inject, ref } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Roční období');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  { name: pageTitle.value, link: '/ubytovani/rocni-obdobi', current: true },
]);
const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tableQuery = ref({
  search: null as string | null,
  paginate: 12 as number,
  page: 1 as number,
  orderBy: 'position' as string,
  orderWay: 'asc' as string,
});

const items = ref([]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/season', {
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
        detail: 'Nepodařilo se načíst roční období.',
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
  await client('/api/admin/season/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat období.', severity: 'error' });
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

useHead({ title: pageTitle.value });
onMounted(() => loadItems());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'add', text: 'Přidat období' }]"
      slug="seasons"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 80, hidden: false, sortable: false },
        {
          key: 'is_recurring',
          name: 'Opakující se',
          type: 'status',
          width: 80,
          hidden: false,
          sortable: false,
        },
        {
          key: 'position',
          name: 'Pořadí',
          type: 'number',
          width: 80,
          hidden: false,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Období"
      plural="Období"
      :query="tableQuery"
      slug="seasons"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
