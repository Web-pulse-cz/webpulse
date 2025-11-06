<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Životopisy');

const user = useSanctumUser();

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/zivotopisy',
    current: false,
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

  await client<{ id: number }>('/api/admin/biography', {
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
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst životopisy. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/biography/' + id, {
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
        detail: 'Nepodařilo se smazat položku životopisu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function downloadFile(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  try {
    const res = await client.raw(`/api/admin/biography/download/${id}`, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob', // klíčové pro binární data s ofetch
    });

    if (!res.ok) {
      throw new Error(`Chyba při stahování souboru (${res.status})`);
    }

    const blob: Blob = res._data as Blob;

    // název souboru z Content-Disposition
    const dispo = res.headers.get('content-disposition') || '';
    let filename = 'zivotopis_' + user.value.lastname;

    // filename*="UTF-8''nazev.pdf"
    let match = dispo.match(/filename\*=(?:UTF-8'')?([^;]+)/i);
    if (match && match[1]) {
      filename = decodeURIComponent(match[1].replace(/["']/g, '').trim());
    } else {
      // fallback: filename="nazev.pdf"
      match = dispo.match(/filename=([^;]+)/i);
      if (match && match[1]) {
        filename = match[1].replace(/["']/g, '').trim();
      }
    }

    // doplnění přípony, pokud chybí
    if (!/\.[a-z0-9]{2,8}$/i.test(filename)) {
      const ext = blob.type?.split('/')[1];
      filename += ext ? `.${ext}` : '.bin';
    }

    // stažení
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Download selhal:', error);
  } finally {
    loading.value = false;
  }
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
      :actions="[{ type: 'add', text: 'Přidat životopis' }]"
      slug="biographies"
    />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
          { key: 'name', name: 'Název', type: 'text', width: 80, hidden: false, sortable: true },
          {
            key: 'job_title',
            name: 'Pozice',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'updated_at',
            name: 'Poslední úprava',
            type: 'date',
            width: 80,
            hidden: true,
            sortable: true,
          },
        ]"
        :actions="[{ type: 'download' }, { type: 'edit' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Životopis"
        :plural="pageTitle"
        :query="tableQuery"
        slug="biographies"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
        @download="downloadFile($event)"
      />
    </LayoutContainer>
  </div>
</template>
