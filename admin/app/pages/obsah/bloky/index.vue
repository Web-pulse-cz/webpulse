<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Bloky');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/obsah/bloky',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tableQuery = ref({
  search: null as string | null,
  paginate: 20 as number,
  page: 1 as number,
  orderBy: 'position' as string,
  orderWay: 'asc' as string,
});

const items = ref<any>({});
const schemas = ref<any>({ types: [] });

async function loadSchemas() {
  const client = useSanctumClient();
  await client('/api/admin/block/schemas', {
    method: 'GET',
    headers: { Accept: 'application/json' },
  }).then((response: any) => {
    schemas.value = response;
  });
}

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/block', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      response.data = (response.data ?? []).map((b: any) => ({
        ...b,
        type_label:
          schemas.value.types.find((t: any) => t.key === b.type)?.label ?? b.type,
        title: pickFirstTranslationField(b, ['title', 'name']) || '—',
      }));
      items.value = response;
      tableQuery.value.page = response.currentPage ?? tableQuery.value.page;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst bloky. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function pickFirstTranslationField(block: any, fields: string[]): string | null {
  const translations = block.translations || {};
  for (const locale of Object.keys(translations)) {
    const data = translations[locale]?.data || {};
    for (const f of fields) {
      if (data[f]) return data[f];
    }
  }
  return null;
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/block/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat blok.',
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

watch(selectedSiteHash, () => loadItems());

useHead({
  title: pageTitle.value,
});

onMounted(async () => {
  await loadSchemas();
  await loadItems();
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
      :actions="[{ type: 'add', text: 'Přidat blok' }]"
      slug="blocks"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
        { key: 'type_label', name: 'Typ', type: 'text', width: 160, hidden: false, sortable: false },
        { key: 'title', name: 'Titulek', type: 'text', width: 280, hidden: false, sortable: false },
        { key: 'position', name: 'Pořadí', type: 'text', width: 100, hidden: false, sortable: true },
        { key: 'is_active', name: 'Aktivní', type: 'status', width: 100, hidden: false, sortable: false },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Blok"
      plural="Bloky"
      :query="tableQuery"
      slug="blocks"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
