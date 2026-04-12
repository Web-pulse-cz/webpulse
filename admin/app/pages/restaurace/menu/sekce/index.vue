<script setup lang="ts">
import { ref } from 'vue';
import { definePageMeta } from '#imports';


const { $toast } = useNuxtApp();
const pageTitle = ref('Sekce jídelního lístku');
const loading = ref(false);
const items = ref([]);

const breadcrumbs = ref([
  { name: 'Jídelní lístky', link: '/restaurace/menu', current: false },
  { name: pageTitle.value, link: '/restaurace/menu/sekce', current: true },
]);

async function loadItems() {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/food/menu-section', {
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
  await client('/api/admin/food/menu-section/' + id, {
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
      :actions="[{ type: 'add', text: 'Přidat sekci' }]"
      slug="menus"
    />
    <BaseTable
      :items="items"
      :columns="[
        { key: 'id', name: 'ID', type: 'text', width: 60, hidden: false, sortable: false },
        { key: 'name', name: 'Název', type: 'text', width: 200, hidden: false, sortable: false },
        {
          key: 'description',
          name: 'Popis',
          type: 'text',
          width: 200,
          hidden: true,
          sortable: false,
        },
        {
          key: 'position',
          name: 'Pořadí',
          type: 'number',
          width: 80,
          hidden: true,
          sortable: false,
        },
      ]"
      :actions="[{ type: 'edit' }, { type: 'delete' }]"
      :loading="loading"
      :error="false"
      singular="Sekce"
      plural="Sekce jídelního lístku"
      slug="menus"
      @delete-item="deleteItem"
    />
  </div>
</template>
