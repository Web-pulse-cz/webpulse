<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Kategorie');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: 'Reference',
    link: '/obsah/reference',
    current: true,
  },
  {
    name: pageTitle.value,
    link: '/obsah/reference/kategorie',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
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

  await client<{ id: number }>('/api/admin/review/category', {
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
        detail: 'Nepodařilo se načíst kategorie. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/review/category/' + id, {
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
        detail: 'Nepodařilo se smazat položku kategorie.',
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
      :actions="[{ type: 'add', text: 'Přidat kategorii' }]"
      slug="reviews"
    />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          {
            key: 'id',
            name: 'ID',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'name',
            name: 'Název',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: false,
          },
          {
            key: 'position',
            name: 'Pořadí ve výpisu',
            type: 'number',
            width: 80,
            hidden: true,
            sortable: true,
          },
          {
            key: 'reviews_count',
            name: 'Počet referencí',
            type: 'number',
            width: 80,
            hidden: true,
            sortable: false,
          },
        ]"
        :actions="[{ type: 'edit' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Kategorie"
        plural="Kategorie"
        :query="tableQuery"
        slug="reviews"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
