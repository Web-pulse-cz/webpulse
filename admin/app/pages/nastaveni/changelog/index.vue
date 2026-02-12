<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';

const { $toast } = useNuxtApp();
const pageTitle = ref('Changelog');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/nastaveni/changelog',
    current: true,
  },
]);

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

  await client<{ id: number }>('/api/admin/changelog', {
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
        detail: 'Nepodařilo se načíst changelogy. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/changelog/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {})
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat pložku changelogy.',
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

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => {
  tableQuery.value.search = searchString.value;
  debouncedLoadItems();
});

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
      slug="changelogs"
      :actions="[{ type: 'add', text: 'Přidat changelog' }]"
    />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
          {
            key: 'version',
            name: 'Verze',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'title',
            name: 'Nadpis',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'type',
            name: 'Typ',
            type: 'enum',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'priority',
            name: 'Priorita',
            type: 'enum',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'updated_at',
            name: 'Poslední úprava',
            type: 'date',
            width: 80,
            hidden: true,
            sortable: true,
          },
        ]"
        :enums="{
          type: {
            feature: 'Nová funkce',
            bugfix: 'Oprava chyby',
            design: 'Vylepšení designu',
            other: 'Ostatní',
          },
          priority: { low: 'Nízká', medium: 'Normální', high: 'Vysoká' },
        }"
        :actions="[{ type: 'edit' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Chnagelog"
        plural="Changelogy"
        :query="tableQuery"
        slug="changelogs"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
