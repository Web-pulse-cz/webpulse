<script setup lang="ts">
import { ref, inject } from 'vue';
import { useToast } from 'primevue/usetoast';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const toast = useToast();
const pageTitle = ref('Kontakty');

const sources = ref([]);
const phases = ref([]);

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/kontakty',
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
  filters: {} as Record<string, number[]>,
});

const showQuickEditDialog = ref(false);
const quickEditDialogItem = ref(null);

const items = ref([]);

async function loadItems() {
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadPhases() {
  const client = useSanctumClient();
  loading.value = true;

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
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fáze. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadSources() {
  const client = useSanctumClient();
  loading.value = true;

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
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zdroje. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/contact/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {})
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku kontaktu.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function saveItem(item) {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/' + item.id, {
    method: 'POST',
    body: JSON.stringify(item),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      toast.add({
        summary: 'Hotovo',
        detail: 'Kontakt byl úspěšně uložen.',
        severity: 'succcess',
        group: 'bc',
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit kontakt. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
        group: 'bc'
      });
    })
    .finally(() => {
      loading.value = false;
      showQuickEditDialog.value = false;
      loadItems();
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

function updateFilters() {
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
  // Load filters from session storage
  if (sessionStorage.getItem('filtersQuery')) {
    console.log('sessionStorage.getItem("filtersQuery")', sessionStorage.getItem('filtersQuery'));
    tableQuery.value = JSON.parse(sessionStorage.getItem('filtersQuery'));
  }
  searchString.value = '';
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
    <LayoutContainer>
      <BaseTable
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
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
        @open-dialog="showEditDialog"
      />
    </LayoutContainer>
    <ContactQuickEditDialog
      v-model:show="showQuickEditDialog"
      v-model:item="quickEditDialogItem"
      @save-item="saveItem"
    />
  </div>
</template>
