<script setup lang="ts">
import { ref, inject } from 'vue';
import _ from 'lodash';
import { useUserGroupStore } from '~/stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const toast = useToast();
const pageTitle = ref('Uživatelské skupiny');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: 'Uživatelé',
    link: '/administratori',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/administratori/skupiny',
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

  await client<{ id: number }>('/api/admin/user/group', {
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
        title: 'Chyba',
        description: 'Nepodařilo se načíst uživatelské skupiny. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/user/group/' + id, {
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
        title: 'Chyba',
        description: 'Nepodařilo se smazat položku uživatelskou skupinu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
      userGroupStore.fetchUserGroups();
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
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      slug="user_groups"
      :actions="[{ type: 'add', text: 'Přidat uživatelskou skupinu' }]"
    />
    <LayoutContainer>
      <BaseTable
        :items="items"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 80, hidden: false, sortable: true },
          { key: 'name', name: 'Jméno', type: 'text', width: 80, hidden: false, sortable: true },
          {
            key: 'users_count',
            name: 'Počet uživatelů',
            type: 'number',
            width: 80,
            hidden: true,
            sortable: false,
          },
        ]"
        :actions="[{ type: 'edit' }, { type: 'delete' }]"
        :loading="loading"
        :error="error"
        singular="Uživatelská skupiny"
        plural="Uživatelské skupiny"
        :query="tableQuery"
        slug="user_groups"
        @delete-item="deleteItem"
        @update-sort="updateSort"
        @update-page="updatePage"
      />
    </LayoutContainer>
  </div>
</template>
