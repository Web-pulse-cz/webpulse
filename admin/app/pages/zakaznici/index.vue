<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Zákazníci');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/zakaznici', current: true }]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25,
    page: 1,
    orderBy: 'last_name',
    orderWay: 'asc',
  },
  { searchString },
);

const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/customer', {
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
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zákazníky.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/customer/' + id, {
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
      :actions="[{ type: 'add', text: 'Přidat zákazníka' }]"
      slug="customers"
    />
    <BaseTable
      add-link="/zakaznici/pridat"
      add-label="Přidat zákazníka"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        {
          key: 'last_name',
          name: 'Příjmení',
          type: 'text',
          width: 120,
          hidden: false,
          sortable: true,
        },
        {
          key: 'first_name',
          name: 'Jméno',
          type: 'text',
          width: 120,
          hidden: false,
          sortable: true,
        },
        { key: 'email', name: 'E-mail', type: 'text', width: 150, hidden: true, sortable: true },
        {
          key: 'total_spent',
          name: 'Útrata',
          type: 'number',
          width: 100,
          hidden: true,
          sortable: true,
        },
        {
          key: 'rating',
          name: 'Hodnocení',
          type: 'stars',
          width: 100,
          hidden: false,
          sortable: true,
        },
        {
          key: 'group_name',
          name: 'Skupina',
          type: 'text',
          width: 100,
          hidden: true,
          sortable: false,
        },
        {
          key: 'status',
          name: 'Stav',
          type: 'mapped',
          width: 80,
          hidden: false,
          sortable: true,
          map: {
            active: { label: 'Aktivní', class: 'bg-emerald-100 text-emerald-700' },
            inactive: { label: 'Neaktivní', class: 'bg-slate-100 text-slate-600' },
            blocked: { label: 'Blokovaný', class: 'bg-red-100 text-red-700' },
          },
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Zákazník"
      plural="Zákazníci"
      :query="tableQuery"
      slug="customers"
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
