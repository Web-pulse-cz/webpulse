<script setup lang="ts">
import { inject, onMounted, ref, watch } from 'vue';
import _ from 'lodash';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Semináře');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  { name: 'Osobní růst', link: '/osobni-rust/seminare', current: false },
  { name: pageTitle.value, link: '/osobni-rust/seminare', current: true },
]);

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    paginate: 25 as number,
    page: 1 as number,
    orderBy: 'date' as string,
    orderWay: 'desc' as string,
  },
  { searchString },
);

const items = ref<any>([]);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client<any>('/api/admin/personal-growth/seminar', {
    method: 'GET',
    query: tableQuery.value,
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      response.data = response.data.map((seminar: any) => ({
        ...seminar,
        tasks_text: seminar.tasks_total ? `${seminar.tasks_done} / ${seminar.tasks_total}` : '-',
      }));
      items.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst semináře. Zkuste to prosím později.',
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

  await client('/api/admin/personal-growth/seminar/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat položku.', severity: 'error' });
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

useHead({ title: pageTitle });

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
      :actions="[{ type: 'add', text: 'Přidat seminář' }]"
      slug="seminars"
    />
    <BaseTable
      add-link="/osobni-rust/seminare/pridat"
      add-label="Přidat seminář"
      :items="items"
      :columns="[
        { key: 'date', name: 'Datum', type: 'date', hidden: false, sortable: true },
        { key: 'type_label', name: 'Typ', type: 'text', hidden: false, sortable: false },
        { key: 'place', name: 'Místo', type: 'text', hidden: false, sortable: true },
        { key: 'organizer', name: 'Organizátor', type: 'text', hidden: false, sortable: true },
        { key: 'tasks_text', name: 'Úkoly hotovo', type: 'text', hidden: false, sortable: false },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="error"
      singular="Seminář"
      plural="Semináře"
      :query="tableQuery"
      slug="seminars"
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
