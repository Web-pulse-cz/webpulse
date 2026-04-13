<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Zaměstnanci');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/zamestnanci', current: true }]);

const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const searchString = ref(inject('searchString', ''));
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
  await client('/api/admin/employee', {
    method: 'GET',
    query: tableQuery.value,
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      items.value = r;
      tableQuery.value.page = r.page;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zaměstnance.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/employee/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then(() => {
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

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => loadItems());

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
      :actions="[{ type: 'add', text: 'Přidat zaměstnance' }]"
      slug="employees"
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
        { key: 'position', name: 'Pozice', type: 'text', width: 120, hidden: true, sortable: true },
        { key: 'status', name: 'Stav', type: 'mapped', width: 80, hidden: false, sortable: true, map: {
          active: { label: 'Aktivní', class: 'bg-emerald-100 text-emerald-700' },
          on_leave: { label: 'Na dovolené', class: 'bg-amber-100 text-amber-700' },
          terminated: { label: 'Ukončený', class: 'bg-red-100 text-red-700' },
          suspended: { label: 'Pozastavený', class: 'bg-slate-100 text-slate-600' },
        }},
        {
          key: 'date_hired',
          name: 'Nástup',
          type: 'date',
          width: 100,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Zaměstnanec"
      plural="Zaměstnanci"
      :query="tableQuery"
      slug="employees"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
