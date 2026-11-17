<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Smlouvy');
const loading = ref(false);
const error = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const breadcrumbs = ref([{ name: pageTitle.value, link: '/smlouvy', current: true }]);

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25,
    page: 1,
    orderBy: 'created_at',
    orderWay: 'desc',
  },
  { searchString },
);

const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/contract', {
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

async function downloadContract(id: number) {
  const client = useSanctumClient();
  try {
    const contract = await client('/api/admin/contract/' + id, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    const file = contract.files?.[0];
    if (!file) {
      $toast.show({
        summary: 'Info',
        detail: 'Smlouva nemá přiložený soubor.',
        severity: 'warning',
      });
      return;
    }
    const res = await client.raw('/api/admin/contract/' + id + '/file/' + file.id, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'smlouva-' + id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout smlouvu.', severity: 'error' });
  }
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/contract/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
      loadItems();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat smlouvu.', severity: 'error' });
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
      :actions="[{ type: 'add', text: 'Přidat smlouvu' }]"
      slug="employee_contracts"
    />
    <BaseTable
      add-link="/smlouvy/pridat"
      add-label="Přidat smlouvu"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'title', name: 'Název', type: 'text', width: 200, hidden: false, sortable: true },
        {
          key: 'type',
          name: 'Typ',
          type: 'mapped',
          width: 120,
          hidden: false,
          sortable: true,
          map: {
            hpp: { label: 'HPP', class: 'bg-blue-100 text-blue-700' },
            dpp: { label: 'DPP', class: 'bg-sky-100 text-sky-700' },
            dpc: { label: 'DPČ', class: 'bg-cyan-100 text-cyan-700' },
            osvc: { label: 'OSVČ', class: 'bg-violet-100 text-violet-700' },
            internship: { label: 'Stáž', class: 'bg-amber-100 text-amber-700' },
            nda: { label: 'NDA', class: 'bg-rose-100 text-rose-700' },
            other: { label: 'Jiný', class: 'bg-slate-100 text-slate-600' },
          },
        },
        {
          key: 'status',
          name: 'Stav',
          type: 'mapped',
          width: 100,
          hidden: false,
          sortable: true,
          map: {
            draft: { label: 'Koncept', class: 'bg-slate-100 text-slate-600' },
            active: { label: 'Aktivní', class: 'bg-emerald-100 text-emerald-700' },
            terminated: { label: 'Ukončená', class: 'bg-red-100 text-red-700' },
            expired: { label: 'Vypršelá', class: 'bg-amber-100 text-amber-700' },
          },
        },
        {
          key: 'employee_name',
          name: 'Zaměstnanec',
          type: 'text',
          width: 150,
          hidden: false,
          sortable: false,
        },
        {
          key: 'project_name',
          name: 'Projekt',
          type: 'text',
          width: 150,
          hidden: true,
          sortable: false,
        },
        { key: 'date_from', name: 'Od', type: 'date', width: 100, hidden: true, sortable: true },
        { key: 'date_to', name: 'Do', type: 'date', width: 100, hidden: true, sortable: true },
        {
          key: 'created_at',
          name: 'Vytvořeno',
          type: 'date',
          width: 100,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'download' }, { type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Smlouva"
      plural="Smlouvy"
      :query="tableQuery"
      slug="employee_contracts"
      :filtered="!!tableQuery.search"
      @download="downloadContract($event)"
      @delete-item="deleteItem"
      @retry="loadItems"
      @clear-filters="searchString = ''"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
    />
  </div>
</template>
