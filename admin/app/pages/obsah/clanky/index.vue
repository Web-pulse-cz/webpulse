<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Blogové články');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/obsah/clanky',
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

  await client<{ id: number }>('/api/admin/post', {
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
        detail: 'Nepodařilo se načíst články. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/post/' + id, {
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
        detail: 'Nepodařilo se smazat položku článku.',
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
      client('/api/admin/post/' + id, {
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
      :actions="[{ type: 'add', text: 'Přidat článek' }]"
      slug="posts"
    />
    <BaseTable
      add-link="/obsah/clanky/pridat"
      add-label="Přidat článek"
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
          key: 'published_from',
          name: 'Publikováno od',
          type: 'datetime',
          width: 80,
          hidden: true,
          sortable: true,
        },
        {
          key: 'published_to',
          name: 'Publikováno do',
          type: 'datetime',
          width: 80,
          hidden: true,
          sortable: true,
        },
        {
          key: 'active',
          name: 'Aktivní',
          type: 'status',
          width: 80,
          hidden: true,
          sortable: false,
        },
      ]"
      :enums="{
        status: {
          draft: 'Koncept',
          published: 'Publikováno',
          archived: 'Archivováno',
        },
      }"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Článek"
      plural="Články"
      :query="tableQuery"
      slug="posts"
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
