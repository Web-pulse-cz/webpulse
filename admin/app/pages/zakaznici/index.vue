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
const tableQuery = ref({
  search: null as string | null,
  paginate: 12,
  page: 1,
  orderBy: 'last_name',
  orderWay: 'asc',
});

const items = ref([]);

async function loadItems() {
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
      :actions="[{ type: 'add', text: 'Přidat zákazníka' }]"
      slug="customers"
    />
    <BaseTable
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
        { key: 'status', name: 'Stav', type: 'text', width: 80, hidden: false, sortable: true },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Zákazník"
      plural="Zákazníci"
      :query="tableQuery"
      slug="customers"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
