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
  <div class="space-y-6 pb-20">
    <LayoutHeader
        :title="pageTitle"
        :breadcrumbs="breadcrumbs"
        :actions="[{ type: 'add-cashflow', text: 'Zaznamenat výdaj' }]"
        @open-cashflow-dialog="openCashflowDialog"
    />

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">

      <div class="col-span-1 space-y-8 lg:col-span-8">

        <LayoutContainer>
          <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
              <div class="relative flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                <PhoneIcon class="size-5" />
                <div class="absolute -top-1 -right-1 flex h-2.5 w-2.5 animate-pulse rounded-full bg-amber-400 ring-2 ring-white" />
              </div>
              <LayoutTitle class="!mb-0">Dnes kontaktovat (Hovory)</LayoutTitle>
            </div>
            <div class="flex items-center gap-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
              Celkem:
              <span class="font-black text-indigo-600 text-sm/none">{{ dashboard.contactsToCall?.length || 0 }}</span>
            </div>
          </div>

          <BaseTable
              :items="dashboard.contactsToCall"
              :columns="[
              { key: 'firstname', name: 'Jméno', type: 'text', sortable: false },
              { key: 'lastname', name: 'Příjmení', type: 'text', sortable: false },
              { key: 'phone', name: 'Telefonní číslo', type: 'text', sortable: false },
            ]"
              :actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
              :loading="loading"
              :error="error"
              singular="Hovor na dnes"
              plural="Hovory na dnes"
          />
        </LayoutContainer>

        <LayoutContainer>
          <div class="mb-8 flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
              <CalendarDaysIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Nadcházející schůzky</LayoutTitle>
          </div>

          <BaseTable
              :items="dashboard.comingEvents"
              :columns="[
              { key: 'firstname', name: 'Jméno klienta', type: 'text', sortable: false },
              { key: 'lastname', name: 'Příjmení', type: 'text', sortable: false },
              { key: 'next_meeting', name: 'Termín schůzky', type: 'datetime', sortable: false },
            ]"
              :actions="[{ type: 'edit', path: '/kontakty', hash: '#proces' }]"
              :loading="loading"
              :error="error"
              singular="Naplánovaná schůzka"
              plural="Naplánované schůzky"
          />
        </LayoutContainer>
      </div>

      <aside class="col-span-1 lg:col-span-4 lg:sticky lg:top-8">
        <LayoutContainer class="!py-6">
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <RocketLaunchIcon class="size-4 text-slate-400" />
              <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400 !mb-0">Changelog</LayoutTitle>
            </div>
            <span class="rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-black uppercase text-white tracking-widest">
              v1.0.6
            </span>
          </div>

          <div class="custom-scrollbar space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto pr-2 pb-2">
            <ChangelogCard
                v-for="(changelogItem, index) in changelog"
                :key="index"
                :changelog="changelogItem"
            />
          </div>
        </LayoutContainer>

        <div class="mt-6 rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100/50">
          <p class="text-sm leading-relaxed text-indigo-800/80">
            <strong>Tip:</strong> Zaznamenávejte schůzky a hovory ihned po jejich skončení. Udržíte tak histori kontaktu v CRM aktuální a nezapomenete na důležité detaily.
          </p>
        </div>
      </aside>
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

<style scoped>
/* Jemný scrollbar pro Changelog sloupec, aby nerušil design */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; /* slate-300 */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; /* slate-400 */
}
</style>