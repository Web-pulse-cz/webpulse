<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Faktury');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/faktury',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const tableQuery = ref({
  search: null as string | null,
  paginate: 12 as number,
  page: 1 as number,
  orderBy: 'issued_on' as string,
  orderWay: 'desc' as string,
});

const items = ref([]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/invoice', {
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
        detail: 'Nepodařilo se načíst faktury. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function downloadInvoice(id: number) {
  const client = useSanctumClient();
  try {
    // First get the invoice to find its file
    const invoice = await client('/api/admin/invoice/' + id, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    const file = invoice.files?.[0];
    if (!file) {
      $toast.show({
        summary: 'Info',
        detail: 'Faktura nemá přiložený soubor.',
        severity: 'warning',
      });
      return;
    }
    const res = await client.raw('/api/admin/invoice/' + id + '/file/' + file.id, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'faktura-' + id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout fakturu.', severity: 'error' });
  }
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/invoice/' + id, {
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
        detail: 'Nepodařilo se smazat fakturu.',
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
      :actions="[{ type: 'add', text: 'Přidat fakturu' }]"
      slug="invoices"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: true },
        { key: 'number', name: 'Číslo', type: 'text', width: 100, hidden: false, sortable: true },
        {
          key: 'subject',
          name: 'Předmět',
          type: 'text',
          width: 200,
          hidden: false,
          sortable: true,
        },
        {
          key: 'client.name',
          name: 'Klient',
          type: 'text',
          width: 150,
          hidden: true,
          sortable: false,
        },
        { key: 'total', name: 'Celkem', type: 'number', width: 100, hidden: true, sortable: true },
        {
          key: 'status',
          name: 'Stav',
          type: 'mapped',
          width: 100,
          hidden: false,
          sortable: true,
          map: {
            open: { label: 'Otevřená', class: 'bg-slate-100 text-slate-600' },
            sent: { label: 'Odeslaná', class: 'bg-blue-100 text-blue-700' },
            overdue: { label: 'Po splatnosti', class: 'bg-red-100 text-red-700' },
            paid: { label: 'Zaplacená', class: 'bg-emerald-100 text-emerald-700' },
            cancelled: { label: 'Stornovaná', class: 'bg-slate-50 text-slate-400' },
          },
        },
        {
          key: 'issued_on',
          name: 'Vystaveno',
          type: 'date',
          width: 100,
          hidden: true,
          sortable: true,
        },
        {
          key: 'due_on',
          name: 'Splatnost',
          type: 'date',
          width: 100,
          hidden: true,
          sortable: true,
        },
      ]"
      :actions="[{ type: 'download' }, { type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Faktura"
      plural="Faktury"
      :query="tableQuery"
      slug="invoices"
      @download="downloadInvoice($event)"
      @delete-item="deleteItem"
      @update-sort="updateSort"
      @update-page="updatePage"
    />
  </div>
</template>
