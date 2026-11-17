<script setup lang="ts">
import { inject, onMounted, ref, watch } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Audia');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  { name: 'Osobní růst', link: '/osobni-rust/audia', current: false },
  { name: pageTitle.value, link: '/osobni-rust/audia', current: true },
]);

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25 as number,
    page: 1 as number,
    orderBy: 'created_at' as string,
    orderWay: 'desc' as string,
  },
  { searchString },
);

const items = ref<any>([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client<any>('/api/admin/personal-growth/audio', {
    method: 'GET',
    query: tableQuery.value,
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      response.data = response.data.map((audio: any) => ({
        ...audio,
        keywords_text: (audio.keywords ?? []).join(', ') || '-',
      }));
      items.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst audia. Zkuste to prosím později.',
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

  await client('/api/admin/personal-growth/audio/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat položku.', severity: 'error' });
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

useHead({ title: pageTitle });

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
      :actions="[{ type: 'add', text: 'Přidat audio' }]"
      slug="audios"
    />
    <BaseTable
      add-link="/osobni-rust/audia/pridat"
      add-label="Přidat audio"
      :items="items"
      :columns="[
        { key: 'name', name: 'Název', type: 'text', hidden: false, sortable: true },
        { key: 'speaker', name: 'Speaker', type: 'text', hidden: false, sortable: true },
        {
          key: 'keywords_text',
          name: 'Klíčová slova',
          type: 'text',
          hidden: false,
          sortable: false,
        },
        { key: 'created_at', name: 'Přidáno', type: 'date', hidden: false, sortable: true },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Audio"
      plural="Audia"
      :query="tableQuery"
      slug="audios"
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
