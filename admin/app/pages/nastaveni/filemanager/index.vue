<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Filemanager');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/nastaveni/filemanager',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tableQuery = ref({
  search: null as string | null,
  paginate: 25 as number,
  page: 1 as number,
  orderBy: 'id' as string,
  orderWay: 'asc' as string,
  entity_type: '' as string,
});

const items = ref([]);

const entityTypeOptions = [
  { value: '', label: 'Všechny typy' },
  { value: 'service', label: 'Služby' },
  { value: 'user', label: 'Uživatelé' },
  { value: 'novelty', label: 'Novinky' },
  { value: 'post', label: 'Blog' },
  { value: 'post_category', label: 'Kategorie blogu' },
  { value: 'logo', label: 'Loga' },
  { value: 'event', label: 'Události' },
  { value: 'career', label: 'Pracovní pozice' },
  { value: 'quiz', label: 'Kvízy' },
  { value: 'apartment', label: 'Apartmány' },
  { value: 'apartment-type', label: 'Typy apartmánů' },
  { value: 'building', label: 'Budovy' },
  { value: 'gallery', label: 'Fotogalerie' },
  { value: 'review', label: 'Reference' },
  { value: 'icon', label: 'Ikony' },
  { value: 'block', label: 'Bloky (obecné)' },
  { value: 'hero', label: 'Blok – Hero' },
];

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/filemanager', {
    method: 'GET',
    query: tableQuery.value,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      if (response?.data && Array.isArray(response.data)) {
        response.data = response.data.map((row: any) => ({
          ...row,
          dimensions: row.width && row.height ? `${row.width} × ${row.height}` : '—',
        }));
      }
      items.value = response;
      tableQuery.value.page = response?.currentPage ?? tableQuery.value.page;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst filemanager. Zkuste to prosím později.',
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

  await client('/api/admin/filemanager/' + id, {
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
        detail: 'Nepodařilo se smazat záznam filemanageru.',
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
watch(
  () => tableQuery.value.entity_type,
  () => loadItems(),
);

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
      :actions="[{ type: 'add', text: 'Přidat formát' }]"
      slug="filemanagers"
    />

    <div class="mb-4 max-w-xs">
      <BaseFormSelect
        v-model="tableQuery.entity_type"
        :options="entityTypeOptions"
        label="Filtr podle typu"
        name="entity_type_filter"
      />
    </div>

    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        {
          key: 'entity_type',
          name: 'Typ entity',
          type: 'text',
          width: 120,
          hidden: false,
          sortable: true,
        },
        {
          key: 'format',
          name: 'Formát',
          type: 'text',
          width: 100,
          hidden: false,
          sortable: true,
        },
        {
          key: 'dimensions',
          name: 'Rozměry',
          type: 'text',
          width: 110,
          hidden: false,
          sortable: false,
        },
        {
          key: 'mode',
          name: 'Mód',
          type: 'text',
          width: 80,
          hidden: false,
          sortable: true,
        },
        {
          key: 'crop_position',
          name: 'Pozice ořezu',
          type: 'text',
          width: 110,
          hidden: false,
          sortable: false,
        },
        {
          key: 'path',
          name: 'Adresář',
          type: 'text',
          width: 120,
          hidden: true,
          sortable: false,
        },
        {
          key: 'position',
          name: 'Pořadí',
          type: 'text',
          width: 70,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Formát"
      plural="Formáty"
      :query="tableQuery"
      slug="filemanagers"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
