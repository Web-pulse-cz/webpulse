<script setup lang="ts">
import { ref, computed, inject, watch } from 'vue';

import { useCashflowCategoryStore } from '~/../stores/cashflowCategoryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';
import {
  availableWidgets,
  defaultConfig,
  mergeConfig,
  type WidgetConfig,
} from '~/components/Dashboard/widgets';
import { usePermissions } from '~/composables/usePermissions';

const cashflowCategoryStore = useCashflowCategoryStore();
const currencyStore = useCurrencyStore();

const { $toast } = useNuxtApp();
const pageTitle = ref('Přehled');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([]);

const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const widgetConfig = ref<WidgetConfig[]>(defaultConfig());
const settingsDialogShow = ref(false);

const { canView, moduleBelongsToSite } = usePermissions();

function widgetVisible(slug?: string): boolean {
  if (!slug) return true;
  return moduleBelongsToSite(slug) && canView(slug);
}

const activeWidgets = computed(() =>
  widgetConfig.value
    .filter((w) => w.enabled)
    .map((w) => ({
      config: w,
      definition: availableWidgets.find((d) => d.key === w.widget_key)!,
    }))
    .filter((w) => !!w.definition)
    .filter((w) => widgetVisible(w.definition.permissionSlug)),
);

const cashflowActionDialog = ref({
  show: false as boolean,
  day: 0 as number,
  categoryId: null as number | null,
});

async function loadConfig() {
  if (!selectedSiteHash.value) {
    widgetConfig.value = defaultConfig();
    return;
  }
  const client = useSanctumClient();
  try {
    const response: any = await client('/api/admin/dashboard/widget', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    const saved = (response?.data ?? []) as WidgetConfig[];
    widgetConfig.value = mergeConfig(saved);
  } catch (_) {
    widgetConfig.value = defaultConfig();
  }
}

async function saveConfig(newConfig: WidgetConfig[]) {
  const client = useSanctumClient();
  try {
    const response: any = await client('/api/admin/dashboard/widget', {
      method: 'POST',
      body: JSON.stringify({ widgets: newConfig }),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    const saved = (response?.data ?? []) as WidgetConfig[];
    widgetConfig.value = mergeConfig(saved);
    $toast.show({
      summary: 'Hotovo',
      detail: 'Nastavení dashboardu bylo uloženo.',
      severity: 'success',
    });
  } catch (_) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se uložit nastavení dashboardu.',
      severity: 'error',
    });
  }
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

  await client(categoryId ? '/api/admin/cashflow/' + categoryId : '/api/admin/cashflow', {
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
  loadConfig();
});
watch(selectedSiteHash, () => loadConfig());
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[
        { type: 'dashboard-settings', text: 'Nastavit widgety' },
        { type: 'add-cashflow', text: 'Zaznamenat výdaj' },
      ]"
      @open-cashflow-dialog="openCashflowDialog"
      @open-dashboard-settings="settingsDialogShow = true"
    />

    <div v-if="activeWidgets.length" class="grid grid-cols-1 items-start gap-6 sm:grid-cols-2">
      <div
        v-for="item in activeWidgets"
        :key="item.config.widget_key"
        :class="item.config.size === 'full' ? 'sm:col-span-2' : 'sm:col-span-1'"
      >
        <component
          :is="item.definition.component"
          :widget-key="item.definition.key"
          :title="item.definition.title"
          :icon="item.definition.icon"
          :permission-slug="item.definition.permissionSlug"
          v-bind="item.definition.props || {}"
        />
      </div>
    </div>

    <div v-else class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
      <p class="text-sm text-slate-500">
        Na dashboardu nejsou žádné widgety. Klikněte na
        <button
          class="font-semibold text-indigo-600 hover:underline"
          @click="settingsDialogShow = true"
        >
          Nastavit widgety
        </button>
        a vyberte, co chcete zobrazit.
      </p>
    </div>

    <DashboardSettingsDialog
      v-model:show="settingsDialogShow"
      :config="widgetConfig"
      @save="saveConfig"
    />

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
