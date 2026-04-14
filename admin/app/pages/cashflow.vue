<script setup lang="ts">
import { ref } from 'vue';

import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
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
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst cashflow. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
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
      $toast.show({
        summary: 'Hotovo',
        detail: 'Kategorie byla úspěšně uložena.',
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit kategorii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
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
      $toast.show({
        summary: 'Hotovo',
        detail: 'Záznamy byly úspěšně uloženy.',
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit záznamy. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
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
      $toast.show({
        summary: 'Hotovo',
        detail: 'Měsíční budget byl úspěšně uložena.',
        severity: 'success',
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit měsíční budget. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItems();
    });
}

function previousMonth() {
  const month = tableQuery.value.month - 1;
  const year = tableQuery.value.year;
  tableQuery.value = {
    month: month < 1 ? 12 : month,
    year: month < 1 ? year - 1 : year,
    dayFrom: 1,
    dayTo: 31,
  };
  loadItems();
}

function nextMonth() {
  const month = tableQuery.value.month + 1;
  const year = tableQuery.value.year;
  tableQuery.value = {
    month: month > 12 ? 1 : month,
    year: month > 12 ? year + 1 : year,
    dayFrom: 1,
    dayTo: 31,
  };
  loadItems();
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
  <div class="space-y-6">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      slug="cashflows"
      :actions="[
        {
          type: 'filter-dialog',
          text: 'Filtrovat přehled',
        },
      ]"
      @filter-dialog="filterDialogIsOpen = true"
    />

    <div class="flex items-center justify-between gap-4">
      <BaseButton
        v-if="tableQuery.year >= 2025"
        variant="secondary"
        size="lg"
        @click="previousMonth"
      >
        <ChevronLeftIcon class="mr-2 size-5" />
        Předchozí měsíc
      </BaseButton>

      <div v-else aria-hidden="true" />

      <BaseButton variant="secondary" size="lg" @click="nextMonth">
        Následující měsíc
        <ChevronRightIcon class="ml-2 size-5" />
      </BaseButton>
    </div>

    <LayoutContainer class="!mt-4 overflow-hidden">
      <div class="custom-scrollbar w-full overflow-x-auto">
        <div class="min-w-[1000px] pb-4">
          <CashflowTable
            v-if="!loading && !error"
            :categories="items.categories"
            :income="items.income"
            :year="tableQuery.year"
            :month="tableQuery.month"
            @create-category="categoryDialog.show = true"
            @load-items="loadItems"
            @save-day-records="saveDayRecords"
            @save-budget="saveBudget"
          />
        </div>
      </div>
    </LayoutContainer>

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

<style scoped>
/* Horizontální scrollbar pro finance tabulku */
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc; /* slate-50 */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0; /* slate-200 */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1; /* slate-300 */
}
</style>
