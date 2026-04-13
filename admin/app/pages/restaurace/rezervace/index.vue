<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Rezervace');
const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([{ name: pageTitle.value, link: '/restaurace/rezervace', current: true }]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = ref({
  search: null as string | null,
  paginate: 12,
  page: 1,
  orderBy: 'date',
  orderWay: 'desc',
  date_from: '',
  date_to: '',
  status: '',
});
const items = ref([]);

const statusFilterOptions = ref([
  { value: '', name: 'Všechny stavy' },
  { value: 'pending', name: 'Čeká' },
  { value: 'confirmed', name: 'Potvrzeno' },
  { value: 'seated', name: 'Usazení' },
  { value: 'completed', name: 'Dokončeno' },
  { value: 'cancelled', name: 'Zrušeno' },
  { value: 'no_show', name: 'Nedorazili' },
]);

const inlineFilters = computed(() => [
  { key: 'date_from', label: 'Od', type: 'date', modelValue: tableQuery.value.date_from },
  { key: 'date_to', label: 'Do', type: 'date', modelValue: tableQuery.value.date_to },
  {
    key: 'status',
    label: 'Stav',
    type: 'select',
    modelValue: tableQuery.value.status,
    options: statusFilterOptions.value,
  },
]);

function onApplyFilters(payload: { key: string; value?: string }) {
  if (payload.key === '_apply') {
    tableQuery.value.page = 1;
    loadItems();
    return;
  }
  (tableQuery.value as any)[payload.key] = payload.value || '';
}

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();
  const query: Record<string, any> = {
    paginate: tableQuery.value.paginate,
    page: tableQuery.value.page,
    orderBy: tableQuery.value.orderBy,
    orderWay: tableQuery.value.orderWay,
  };
  if (tableQuery.value.search) query.search = tableQuery.value.search;
  if (tableQuery.value.date_from) query.date_from = tableQuery.value.date_from;
  if (tableQuery.value.date_to) query.date_to = tableQuery.value.date_to;
  if (tableQuery.value.status) query.status = tableQuery.value.status;

  await client('/api/admin/food/reservation', {
    method: 'GET',
    query,
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
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
  await client('/api/admin/food/reservation/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).finally(() => {
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
      :actions="[{ type: 'add', text: 'Nová rezervace' }]"
      :inline-filters="inlineFilters"
      slug="reservations"
      @apply-filters="onApplyFilters"
    />

    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 50, hidden: false, sortable: true },
        { key: 'date', name: 'Datum', type: 'text', width: 100, hidden: false, sortable: true },
        { key: 'time_from', name: 'Čas', type: 'text', width: 60, hidden: false, sortable: true },
        {
          key: 'guest_full_name',
          name: 'Host',
          type: 'text',
          width: 150,
          hidden: false,
          sortable: false,
        },
        {
          key: 'guests_count',
          name: 'Hostů',
          type: 'number',
          width: 50,
          hidden: false,
          sortable: true,
        },
        {
          key: 'table_number',
          name: 'Stůl',
          type: 'text',
          width: 60,
          hidden: false,
          sortable: false,
        },
        {
          key: 'guest_phone',
          name: 'Telefon',
          type: 'text',
          width: 100,
          hidden: true,
          sortable: false,
        },
        { key: 'status', name: 'Stav', type: 'mapped', width: 80, hidden: false, sortable: true, map: {
          pending: { label: 'Čeká', class: 'bg-amber-100 text-amber-700' },
          confirmed: { label: 'Potvrzeno', class: 'bg-blue-100 text-blue-700' },
          seated: { label: 'Usazení', class: 'bg-emerald-100 text-emerald-700' },
          completed: { label: 'Dokončeno', class: 'bg-slate-100 text-slate-600' },
          cancelled: { label: 'Zrušeno', class: 'bg-red-100 text-red-700' },
          no_show: { label: 'Nedorazili', class: 'bg-red-50 text-red-500' },
        }},
        { key: 'source', name: 'Zdroj', type: 'mapped', width: 60, hidden: true, sortable: true, map: {
          manual: { label: 'Manuální', class: 'bg-slate-100 text-slate-600' },
          web: { label: 'Web', class: 'bg-indigo-100 text-indigo-700' },
          phone: { label: 'Telefon', class: 'bg-emerald-100 text-emerald-700' },
          email: { label: 'E-mail', class: 'bg-blue-100 text-blue-700' },
        }},
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Rezervace"
      plural="Rezervace"
      :query="tableQuery"
      slug="reservations"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
