<script setup lang="ts">
import { ref } from 'vue';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Šablony směn');
const loading = ref(false);
const items = ref([]);

const breadcrumbs = ref([
  { name: 'Směny', link: '/smeny', current: false },
  { name: pageTitle.value, link: '/smeny/sablony', current: true },
]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/shift/template', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      items.value = { data: r };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/shift/template/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItems();
  });
}

useHead({ title: pageTitle.value });
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
      :actions="[{ type: 'add', text: 'Přidat šablonu' }]"
      slug="shifts"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: false },
        { key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: false },
        { key: 'start_time', name: 'Od', type: 'text', width: 80, hidden: false, sortable: false },
        { key: 'end_time', name: 'Do', type: 'text', width: 80, hidden: false, sortable: false },
        {
          key: 'break_minutes',
          name: 'Přestávka (min)',
          type: 'number',
          width: 100,
          hidden: true,
          sortable: false,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="false"
      singular="Šablona"
      plural="Šablony směn"
      slug="shifts"
      @delete-item="deleteItem"
    />
  </div>
</template>
