<script setup lang="ts">
import { ref } from 'vue';
import { definePageMeta } from '#imports';

const toast = useToast();
const tableQuery = ref({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  dayFrom: 1,
  dayTo: 31,
});

const pageTitle = ref(`Cashflow ─ ${tableQuery.value.month}/${tableQuery.value.year}`);

const loading = ref(false);
const error = ref(false);

const filterDialogIsOpen = ref(false);

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/cashflow',
    current: true,
  },
]);

const categoryDialog = ref({
  show: false,
  category: {
    id: 0,
    name: '',
  },
});

const items = ref({
  categories: [],
  income: [],
});

async function loadItems() {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();

  await client<{
    id: number;
  }>('/api/admin/cashflow/category', {
    method: 'GET',
    query: tableQuery.value,
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
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst cashflow. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteItem(id: number) {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/casfhlow/category/' + id, {
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
        description: 'Nepodařilo se smazat aktivitu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function saveCategory(item) {
  const client = useSanctumClient();
  loading.value = true;
  error.value = false;

  await client<{
    id: number;
  }>(item.id === 0 ? '/api/admin/cashflow/category' : '/api/admin/cashflow/category/' + item.id, {
    method: 'POST',
    body: JSON.stringify(item),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description: 'Kategorie byla úspěšně uložena.',
        color: 'green',
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit kategorii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function saveDayRecords(categoryId: number | null, day: number, type: string, records: []) {
  const client = useSanctumClient();
  // loading.value = true;
  error.value = false;

  const month = tableQuery.value.month;
  const year = tableQuery.value.year;

  const formattedDate = new Date(Date.UTC(year, month - 1, day)).toISOString();

  await client<{
    id: number;
  }>(categoryId ? '/api/admin/cashflow/' + categoryId : '/api/admin/cashflow', {
    method: 'POST',
    body: JSON.stringify({
      categoryId,
      formattedDate,
      type,
      records,
    }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description: 'Záznamy byly úspěšně uložen.',
        color: 'green',
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit záznamy. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

async function saveBudget(categoryId: number, budget: number) {
  const client = useSanctumClient();
  // loading.value = true;
  error.value = false;

  const month = tableQuery.value.month;
  const year = tableQuery.value.year;

  await client<{
    id: number;
  }>('/api/admin/cashflow/budget/' + categoryId, {
    method: 'POST',
    body: JSON.stringify({
      budget,
      month,
      year,
    }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description: 'Měsíční budget byl úspěšně uložena.',
        color: 'green',
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit měsíční budget. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

watch(
  tableQuery,
  () => {
    pageTitle.value = `Cashflow ─ ${tableQuery.value.month}/${tableQuery.value.year}`;
  },
  { deep: true },
);

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
      slug="cashflows"
      :actions="[
        {
          type: 'filter-dialog',
          text: 'Filtrovat',
        },
      ]"
      @filter-dialog="filterDialogIsOpen = true"
    />
    <div class="w-full overflow-x-scroll lg:overflow-hidden">
      <CashflowTable
        v-if="!loading && !error"
        :categories="items.categories"
        :income="items.income"
        :year="tableQuery.year"
        :month="tableQuery.month"
        class="min-w-full"
        @create-category="categoryDialog.show = true"
        @load-items="loadItems"
        @save-day-records="saveDayRecords"
        @save-budget="saveBudget"
      />
    </div>
    <CashflowCategoryDialog
      v-model:show="categoryDialog.show"
      v-model:category="categoryDialog.category"
      @submit="saveCategory"
    />
    <CashflowDialogFilter
      v-model:show="filterDialogIsOpen"
      v-model:year="tableQuery.year"
      v-model:month="tableQuery.month"
      v-model:day-from="tableQuery.dayFrom"
      v-model:day-to="tableQuery.dayTo"
      @submit="loadItems"
    />
  </div>
</template>
