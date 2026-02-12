<script setup lang="ts">
import { ref } from 'vue';

import { useCashflowCategoryStore } from '~/../stores/cashflowCategoryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';

const cashflowCategoryStore = useCashflowCategoryStore();
const currencyStore = useCurrencyStore();

const { $toast } = useNuxtApp();
const pageTitle = ref('Přehled');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([]);

const dashboard = ref([]);
const changelog = ref([]);

const cashflowActionDialog = ref({
  show: false as boolean,
  day: 0 as number,
  categoryId: null as number | null,
});

async function loadDashboard() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/dashboard', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      dashboard.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst přehled. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadChangelog() {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/changelog', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    query: {
      orderBy: 'id',
      orderWay: 'desc',
    },
  })
    .then((response) => {
      changelog.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst changelog. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function openCashflowDialog() {
  cashflowActionDialog.value.show = true;
  cashflowActionDialog.value.categoryId = 50;
  cashflowActionDialog.value.day = new Date().getDate();
}

async function saveDayRecords(data: {
  categoryId: number | null;
  currencyId: number;
  day: number;
  type: string;
  dayRecords: Array<{ id: number | null; amount: number; description: string }>;
}) {
  const client = useSanctumClient();
  // loading.value = true;
  error.value = false;

  const month = new Date().getMonth() + 1;
  const year = new Date().getFullYear();

  const formattedDate = new Date(Date.UTC(year, month - 1, data.day)).toISOString();

  const categoryId = data.categoryId ? data.categoryId : null;
  const currencyId = data.currencyId ? data.currencyId : null;
  const type = data.type ? data.type : 'expense';
  const records = data.dayRecords.map((record) => ({
    id: record.id,
    amount: record.amount,
    description: record.description,
  }));

  await client<{
    id: number;
  }>(categoryId ? '/api/admin/cashflow/' + categoryId : '/api/admin/cashflow', {
    method: 'POST',
    body: JSON.stringify({
      categoryId,
      currencyId,
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
        detail: 'Záznamy byly úspěšně uložen.',
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
    });
}

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  loadDashboard();
  loadChangelog();
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
      :actions="[{ type: 'add-cashflow', text: 'Přidat útratu' }]"
      @open-cashflow-dialog="openCashflowDialog"
    />
    <div
      class="grid grid-cols-1 items-start justify-between gap-x-8 gap-y-2 lg:grid-cols-2 lg:gap-y-4"
    >
      <div class="col-span-full lg:col-span-1">
        <LayoutContainer>
          <LayoutTitle>Dnes máš zavolat těmto kontaktům</LayoutTitle>
          <BaseTable
            :items="dashboard.contactsToCall"
            :columns="[
              {
                key: 'firstname',
                name: 'Jméno',
                type: 'text',
                width: 80,
                hidden: false,
                sortable: false,
              },
              {
                key: 'lastname',
                name: 'Příjmení',
                type: 'text',
                width: 80,
                hidden: false,
                sortable: false,
              },
              {
                key: 'phone',
                name: 'Telefon',
                type: 'text',
                width: 80,
                hidden: true,
                sortable: false,
              },
            ]"
            :actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
            :loading="loading"
            :error="error"
            singular="Poseldní přidaný kontakt"
            plural="Poslední přidané kontakty"
          />
        </LayoutContainer>
        <LayoutContainer>
          <LayoutTitle>Nadcházející schůzky</LayoutTitle>
          <BaseTable
            :items="dashboard.comingEvents"
            :columns="[
              {
                key: 'firstname',
                name: 'Jméno',
                type: 'text',
                width: 80,
                hidden: false,
                sortable: false,
              },
              {
                key: 'lastname',
                name: 'Příjmení',
                type: 'text',
                width: 80,
                hidden: false,
                sortable: false,
              },
              {
                key: 'next_meeting',
                name: 'Datum a čas',
                type: 'datetime',
                width: 80,
                hidden: true,
                sortable: false,
              },
            ]"
            :actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
            :loading="loading"
            :error="error"
            singular="Nadcházející schůzka"
            plural="Nadházející schůzky"
          />
        </LayoutContainer>
      </div>
      <div>
        <LayoutContainer
          class="col-span-full max-h-[512px] space-y-4 overflow-y-auto lg:col-span-1"
        >
          <LayoutTitle>Changelog</LayoutTitle>
          <ChangelogCard
            v-for="(changelogItem, index) in changelog"
            :key="index"
            :changelog="changelogItem"
          />
        </LayoutContainer>
      </div>
    </div>
    <CashflowDialogExtendedAction
      v-model:show="cashflowActionDialog.show"
      :categories="cashflowCategoryStore.categoriesOptions"
      :currencies="currencyStore.currenciesOptions"
      :day="cashflowActionDialog.day"
      :type="cashflowActionDialog.type"
      @save-day-records="saveDayRecords($event)"
    />
  </div>
</template>
