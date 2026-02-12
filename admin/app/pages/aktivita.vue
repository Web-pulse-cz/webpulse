<script setup lang="ts">
import { ref } from 'vue';

import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Aktivita');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/aktivita',
    current: true,
  },
]);

const currentMonth = ref(new Date().getMonth() + 1);
const currentYear = ref(new Date().getFullYear());

const showQuickEditDialog = ref(false);
const quickEditDialogItem = ref(null);

const items = ref([]);

async function loadItems(month: number, year: number) {
  currentMonth.value = month;
  currentYear.value = year;

  loading.value = true;
  const client = useSanctumClient();

  await client<{
    id: number;
  }>('/api/admin/user/activity?month=' + month + '&year=' + year, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      items.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst aktivity. Zkuste to prosím později.',
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

  await client<{ id: number }>('/api/admin/user/activity/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {})
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat aktivitu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems(currentMonth.value, currentYear.value);
    });
}

async function saveItem(item) {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>(
    item.id === 0 ? '/api/admin/user/activity' : '/api/admin/user/activity/' + item.id,
    {
      method: 'POST',
      body: JSON.stringify(item),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail: 'Aktivita byla úspěšně uložena.',
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit aktivitu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      showQuickEditDialog.value = false;
      loadItems(currentMonth.value, currentYear.value);
    });
}

function showEditDialog() {
  quickEditDialogItem.value = {
    id: 0,
    activity_id: 1,
    completed: false,
    formatted_date: new Date().toISOString().split('T')[0],
  };
  showQuickEditDialog.value = true;
}

function addEditDialogItem(date: string) {
  const dateObj = new Date(date);
  dateObj.setDate(dateObj.getDate() + 1);
  quickEditDialogItem.value = {
    id: 0,
    activity_id: 1,
    completed: false,
    formatted_date: new Date(dateObj).toISOString().split('T')[0],
  };
  showQuickEditDialog.value = true;
}

function showUpdateEditDialog(item) {
  quickEditDialogItem.value = item;
  showQuickEditDialog.value = true;
}

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  loadItems(currentMonth.value, currentYear.value);
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
      :actions="[
        { type: 'print', text: 'Tisknout' },
        { type: 'add-dialog', text: 'Přidat aktivitu' },
      ]"
      slug="users_has_activities"
      @add-dialog="showEditDialog"
    />
    <LayoutContainer>
      <UserActivityCalendar
        :activities="items"
        @update-item="showUpdateEditDialog"
        @load-items="loadItems"
        @add-item="addEditDialogItem"
      />
    </LayoutContainer>
    <UserActivityDialog
      v-model:show="showQuickEditDialog"
      v-model:item="quickEditDialogItem"
      @save-item="saveItem"
    />
  </div>
</template>
