<script setup lang="ts">
import { ref, inject } from 'vue';

import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Žádosti');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: 'Pracovní pozice',
    link: '/obsah/pracovni-pozice',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/pracovni-pozice/zadosti',
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

  await client<{ id: number }>('/api/admin/career/application', {
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
        detail: 'Nepodařilo se načíst žádosti. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/career/application/' + id, {
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
        detail: 'Nepodařilo se smazat položku žádosti.',
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
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="careers" />
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
          {
            key: 'email',
            name: 'E-mail',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'phone',
            name: 'Telefon',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'availability',
            name: 'Nástup možný od',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'salary_expectation',
            name: 'Očekáváná mzda',
            type: 'text',
            width: 80,
            hidden: false,
            sortable: true,
          },
          {
            key: 'status',
            name: 'Stav přihlášky',
            type: 'enum',
            width: 80,
            hidden: false,
            sortable: true,
          },
        ]"
        :enums="{
          availability: {
            immediate: 'Ihned',
            '1-week': 'Za týden',
            '2-weeks': 'Za 2 týdny',
            '1-month': 'Za měsíc',
            '2-month': 'Za dva a více měsíců',
            negotiable: 'Dohodou',
          },
          status: {
            pending: 'Čeká',
            reviewed: 'Zobrazeno',
            accepted: 'Přijato',
            rejected: 'Zamítnuto',
          },
        }"
        :actions="[{ type: 'edit' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Pracovní pozici"
        plural="Pracovní pozice"
        :query="tableQuery"
        slug="careers"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
