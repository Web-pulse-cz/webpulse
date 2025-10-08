<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('E-maily');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: 'Nastavení',
    link: '#',
    current: false,
  },
  {
    name: 'Logy',
    link: '#',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/logy/emaily',
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

  await client<{ id: number }>('/api/admin/log/email', {
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
        detail: 'Nepodařilo se načíst e-maily. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/log/email/' + id, {
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
        detail: 'Nepodařilo se smazat položku e-mailu.',
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
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="emails" />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          {
            key: 'id',
            name: 'ID',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'to',
            name: 'Příjemce',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'subject',
            name: 'Předmět',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'priority',
            name: 'Priorita',
            type: 'number',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'attempts',
            name: 'Počet pokusů',
            type: 'number',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'sent',
            name: 'Odesláno',
            type: 'status',
            width: 80,
            hidden: false,
            sortable: true,
          },
        ]"
        :actions="[{ type: 'edit' }]"
        :loading="loading"
        :error="error"
        singular="E-mail"
        plural="E-maily"
        :query="tableQuery"
        slug="emails"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
