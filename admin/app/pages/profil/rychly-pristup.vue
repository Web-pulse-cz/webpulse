<script setup lang="ts">
import { ref, inject } from 'vue';
import { useToast } from 'primevue/usetoast';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const toast = useToast();

const loading = ref(false);
const error = ref(false);

const pageTitle = ref('Rychlý přístup');

const breadcrumbs = ref([
  {
    name: 'Uživatelský profil',
    link: '/profil',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/profil/rychly-pristup',
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

const quickAccessDialogShow = ref(false);
const quickAccessDialogForm = ref(false);

const items = ref([]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/quick-access', {
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
        detail: 'Nepodařilo se načíst položky rychlého přístupu. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/quick-access/' + id, {
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
        detail: 'Nepodařilo se smazat položku rychlého přístupu.',
        severity: 'error',
        group: 'bc'
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

function openQuickAccessDialog(item) {
  quickAccessDialogShow.value = true;
  quickAccessDialogForm.value = item;
}

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
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 100, hidden: false, sortable: true },
          { key: 'name', name: 'Název', type: 'text', width: 210, hidden: false, sortable: true },
          {
            key: 'link',
            name: 'Odkaz',
            type: 'link',
            width: 150,
            hidden: true,
            sortable: true,
            target: 'target',
          },
          { key: 'target', name: 'Cíl', type: 'enum', width: 150, hidden: true, sortable: true },
        ]"
        :enums="{
          target: {
            _blank: 'Nové okno',
            _self: 'Stejné okno',
          },
        }"
        :actions="[{ type: 'edit-dialog' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Položka rychlého přístupu"
        plural="Položky rychlého přístupu"
        :query="tableQuery"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
        @open-dialog="openQuickAccessDialog"
      />
    </LayoutContainer>
    <QuickAccessDialog v-model:show="quickAccessDialogShow" v-model:form="quickAccessDialogForm" />
  </div>
</template>
