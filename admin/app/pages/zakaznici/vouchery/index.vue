<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Slevové vouchery');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  { name: 'Zákazníci', link: '/zakaznici', current: false },
  { name: pageTitle.value, link: '/zakaznici/vouchery', current: true },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25,
    page: 1,
    orderBy: 'id',
    orderWay: 'desc',
  },
  { searchString },
);
const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/voucher', {
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
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/voucher/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku.',
        severity: 'error',
      });
    })
    .finally(() => {
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
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'add', text: 'Přidat voucher' }]"
      slug="vouchers"
    />
    <BaseTable
      add-link="/zakaznici/vouchery/pridat"
      add-label="Přidat voucher"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'code', name: 'Kód', type: 'text', width: 120, hidden: false, sortable: true },
        { key: 'name', name: 'Název', type: 'text', width: 150, hidden: false, sortable: true },
        {
          key: 'discount_value',
          name: 'Sleva',
          type: 'number',
          width: 80,
          hidden: false,
          sortable: true,
        },
        {
          key: 'discount_type',
          name: 'Typ',
          type: 'mapped',
          width: 80,
          hidden: true,
          sortable: false,
          map: {
            fixed: { label: 'Pevná částka', class: 'bg-emerald-100 text-emerald-700' },
            percentage: { label: 'Procenta', class: 'bg-indigo-100 text-indigo-700' },
          },
        },
        {
          key: 'used_count',
          name: 'Použito',
          type: 'number',
          width: 80,
          hidden: false,
          sortable: true,
        },
        {
          key: 'max_uses',
          name: 'Max. použití',
          type: 'number',
          width: 80,
          hidden: true,
          sortable: false,
        },
        {
          key: 'valid_to',
          name: 'Platnost do',
          type: 'text',
          width: 100,
          hidden: true,
          sortable: true,
        },
        {
          key: 'is_active',
          name: 'Aktivní',
          type: 'status',
          width: 60,
          hidden: false,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Voucher"
      plural="Slevové vouchery"
      :query="tableQuery"
      slug="vouchers"
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
