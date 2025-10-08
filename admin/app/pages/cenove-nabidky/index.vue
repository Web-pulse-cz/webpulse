<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { useToast } from 'primevue/usetoast';
import { definePageMeta } from '#imports';

const toast = useToast();
const pageTitle = ref('Cenové nabídky');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/projekty',
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

  await client<{ id: number }>('/api/admin/price-offer', {
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst cenové nabídky. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/price-offer/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku cenové nabídky.',
        severity: 'error',
        group: 'bc',
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
      :actions="[{ type: 'add', text: 'Přidat nabídku' }]"
      slug="price_offers"
    />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
          { key: 'name', name: 'Název', type: 'text', width: 80, hidden: false, sortable: true },
          {
            key: 'expected_price_vat',
            name: 'Očekávaná cena (vč. DPH)',
            type: 'number',
            width: 80,
            hidden: true,
            sortable: true,
          },
          {
            key: 'total_price_vat',
            name: 'Konečená cena (vč. DPH)',
            type: 'number',
            width: 80,
            hidden: true,
            sortable: true,
          },
          {
            key: 'status_name',
            name: 'Stav projektu',
            type: 'badge',
            width: 80,
            hidden: false,
            sortable: false,
            colorKey: 'status_color',
          },
        ]"
        :actions="[{ type: 'edit', hash: '#info' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Cenová nabídka"
        plural="Cenové nabídky"
        :query="tableQuery"
        slug="price_offers"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
