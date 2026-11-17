<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Kontakty');

const sources = ref([]);
const phases = ref([]);

const loading = ref(false);
const lookupsLoading = ref(false);
const quickSaving = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/kontakty',
    current: true,
  },
]);

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25 as number,
    page: 1 as number,
    orderBy: 'id' as string,
    orderWay: 'desc' as string,
    filters: {} as Record<string, number[]>,
  },
  { searchString },
);

const showQuickEditDialog = ref(false);
const quickEditDialogItem = ref(null);

const items = ref([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client<{
    id: number;
  }>('/api/admin/contact', {
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
        detail: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadPhases() {
  const client = useSanctumClient();
  lookupsLoading.value = true;

  await client<{ id: number }>('/api/admin/contact/phase', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      phases.value = response;
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fáze. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      lookupsLoading.value = false;
    });
}

async function loadSources() {
  const client = useSanctumClient();
  lookupsLoading.value = true;

  await client<{ id: number }>('/api/admin/contact/source', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      sources.value = response;
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zdroje. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      lookupsLoading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/contact/' + id, {
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
        detail: 'Nepodařilo se smazat položku kontaktu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function saveItem(item) {
  const client = useSanctumClient();
  quickSaving.value = true;

  await client<{ id: number }>('/api/admin/contact/' + item.id, {
    method: 'POST',
    body: JSON.stringify(item),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail: 'Kontakt byl úspěšně uložen.',
        severity: 'success',
      });
      showQuickEditDialog.value = false;
      loadItems();
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit kontakt. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      quickSaving.value = false;
    });
}

function showEditDialog(item) {
  quickEditDialogItem.value = item;
  showQuickEditDialog.value = true;
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

function updateFilters(query: Record<string, any>) {
  tableQuery.value.filters = query.filters ?? {};
  tableQuery.value.page = 1;
  loadItems();
}

const isFiltered = computed(
  () =>
    !!tableQuery.value.search ||
    Object.values(tableQuery.value.filters ?? {}).some((value) =>
      Array.isArray(value) ? value.length > 0 : !!value,
    ),
);

function clearFilters() {
  tableQuery.value.filters = {};
  tableQuery.value.search = null;
  tableQuery.value.page = 1;
  searchString.value = '';
  loadItems();
}

async function bulkDeleteItems(ids: number[]) {
  const client = useSanctumClient();
  loading.value = true;

  const results = await Promise.allSettled(
    ids.map((id) =>
      client('/api/admin/contact/' + id, {
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

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => {
  if ((tableQuery.value.search ?? '') === (searchString.value ?? '')) return;
  tableQuery.value.search = searchString.value;
  tableQuery.value.page = 1;
  debouncedLoadItems();
});

useHead({
  title: pageTitle,
});

onMounted(() => {
  // Filtry/stránkování se obnovují z URL (useListQuery); zbytkové hledání z jiné stránky vynulovat
  if (!tableQuery.value.search) searchString.value = '';
  loadItems();
  loadPhases();
  loadSources();
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
      :actions="[{ type: 'add', text: 'Přidat kontakt' }]"
      :filters="[
        { title: 'Podle fáze', data: phases, multiple: true, type: 'badge', slug: 'phase' },
        { title: 'Podle zdroje', data: sources, multiple: true, type: 'badge', slug: 'source' },
      ]"
      :filters-query="tableQuery"
      slug="contacts"
      @update-filters="updateFilters"
    />
    <BaseTable
      add-link="/kontakty/pridat"
      add-label="Přidat kontakt"
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
        {
          key: 'firstname',
          name: 'Jméno',
          type: 'text',
          width: 80,
          hidden: false,
          sortable: true,
        },
        {
          key: 'lastname',
          name: 'Příjmení',
          type: 'text',
          width: 80,
          hidden: false,
          sortable: true,
        },
        { key: 'phone', name: 'Telefon', type: 'text', width: 80, hidden: true, sortable: true },
        { key: 'email', name: 'E-mail', type: 'text', width: 80, hidden: true, sortable: true },
        {
          key: 'phase',
          name: 'Fáze',
          type: 'badge',
          width: 80,
          hidden: true,
          sortable: false,
          colorKey: 'phase_color',
        },
        {
          key: 'source',
          name: 'Zdroj',
          type: 'badge',
          width: 80,
          hidden: true,
          sortable: false,
          colorKey: 'source_color',
        },
      ]"
      :actions="[{ type: 'edit', hash: '#proces' }, { type: 'edit-dialog' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Kontakt"
      plural="Kontakty"
      :query="tableQuery"
      slug="contacts"
      selectable
      :filtered="isFiltered"
      @delete-item="deleteItem"
      @retry="loadItems"
      @clear-filters="clearFilters"
      @bulk-delete="bulkDeleteItems"
      @update-sort="updateSort"
      @update-page="updatePage"
      @update-per-page="updatePerPage"
      @open-dialog="showEditDialog"
    />
    <ContactQuickEditDialog
      v-model:show="showQuickEditDialog"
      v-model:item="quickEditDialogItem"
      @save-item="saveItem"
    />
  </div>
</template>
