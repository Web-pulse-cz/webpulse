<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Kvízy');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/obsah/kvizy',
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

  await client<{ id: number }>('/api/admin/quiz', {
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
        detail: 'Nepodařilo se načíst kvízy. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/quiz/' + id, {
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
        detail: 'Nepodařilo se smazat položku kvízu.',
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
  if ((tableQuery.value.search ?? '') === (searchString.value ?? '')) return;
  tableQuery.value.search = searchString.value;
  tableQuery.value.page = 1;
  debouncedLoadItems();
});

useHead({
  title: pageTitle,
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
      :actions="[{ type: 'add', text: 'Přidat kvíz' }]"
      slug="quizzes"
    />
    <BaseTable
      add-link="/obsah/kvizy/pridat"
      add-label="Přidat kvíz"
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
          key: 'status',
          name: 'Stav',
          type: 'enum',
          width: 80,
          hidden: true,
          sortable: true,
        },
        {
          key: 'accuracy',
          name: 'Úspěšnost',
          type: 'percent',
          width: 80,
          hidden: true,
          sortable: true,
        },
        {
          key: 'attempts',
          name: 'Počet pokusů',
          type: 'number',
          width: 80,
          hidden: true,
          sortable: true,
        },
      ]"
      :enums="{
        status: {
          draft: 'Koncept',
          public: 'Veřejný',
          private: 'Soukromý',
          archived: 'Archivovaný',
        },
      }"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Kvíz"
      plural="Kvízy"
      :query="tableQuery"
      slug="quizzes"
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
