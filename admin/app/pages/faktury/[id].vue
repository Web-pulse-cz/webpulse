<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { DocumentIcon, ChatBubbleLeftIcon, ListBulletIcon, CreditCardIcon, TrashIcon } from '@heroicons/vue/24/outline';

import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Položky', link: '#polozky', current: false },
  { name: 'Platební údaje', link: '#platba', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová faktura' : 'Detail faktury');

const breadcrumbs = ref([
  {
    name: 'Faktury',
    link: '/faktury',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/faktury/pridat',
    current: true,
  },
]);

const clients = ref([]);
const projects = ref([]);

const item = ref({
  id: null,
  client_id: null,
  project_id: null,
  document_type: 'invoice',
  number: '',
  subject: '',
  note: '',
  footer_note: '',
  status: 'open',
  currency_id: null,
  language_id: null,
  payment_method: 'bank',
  variable_symbol: '',
  constant_symbol: '',
  specific_symbol: '',
  bank_account: '',
  iban: '',
  swift_bic: '',
  issued_on: '',
  taxable_fulfillment_due: '',
  due_on: '',
  paid_on: '',
  items: [] as any[],
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/invoice/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = item.value.number || 'Faktura #' + item.value.id;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/faktury/' + route.params.id,
        current: true,
      };
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fakturu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadClients() {
  const client = useSanctumClient();
  await client('/api/admin/client', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((response) => {
      const data = response?.data || response;
      clients.value = data.map((c: any) => ({ value: c.id, name: c.name }));
    })
    .catch(() => {});
}

async function loadProjects() {
  const client = useSanctumClient();
  await client('/api/admin/project', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((response) => {
      const data = response?.data || response;
      projects.value = data.map((p: any) => ({ value: p.id, name: p.name }));
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat' ? '/api/admin/invoice' : '/api/admin/invoice/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Faktura byla úspěšně vytvořena.'
            : 'Faktura byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/faktury/' + response.id);
      } else if (redirect) {
        router.push('/faktury');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit fakturu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addItem() {
  item.value.items.push({
    name: '',
    quantity: 1,
    unit_name: 'ks',
    unit_price: 0,
    vat_rate: 21,
  });
}

function removeItem(index: number) {
  item.value.items.splice(index, 1);
}

const documentTypeOptions = ref([
  { value: 'invoice', name: 'Faktura' },
  { value: 'proforma', name: 'Proforma' },
  { value: 'partial_proforma', name: 'Částečná proforma' },
  { value: 'final_invoice', name: 'Vyúčtovací faktura' },
]);

const statusOptions = ref([
  { value: 'open', name: 'Otevřená' },
  { value: 'sent', name: 'Odeslaná' },
  { value: 'overdue', name: 'Po splatnosti' },
  { value: 'paid', name: 'Zaplacená' },
  { value: 'cancelled', name: 'Stornovaná' },
]);

const paymentMethodOptions = ref([
  { value: 'bank', name: 'Bankovní převod' },
  { value: 'cash', name: 'Hotovost' },
  { value: 'card', name: 'Kartou' },
  { value: 'paypal', name: 'PayPal' },
]);

watchEffect(() => {
  const routeTabHash = route.hash;
  if (routeTabHash && routeTabHash !== '') {
    tabs.value.forEach((tab) => {
      tab.current = tab.link === routeTabHash;
    });
  } else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
});

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  loadClients();
  loadProjects();
  if (route.params.id !== 'pridat') {
    loadItem();
  }
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
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="invoices"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <DocumentIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormSelect
                  v-model="item.document_type"
                  label="Typ dokladu"
                  name="document_type"
                  :options="documentTypeOptions"
                />
                <BaseFormInput
                  v-model="item.number"
                  label="Číslo faktury"
                  type="text"
                  name="number"
                />
                <BaseFormInput
                  v-model="item.subject"
                  label="Předmět"
                  type="text"
                  name="subject"
                  class="col-span-full"
                />
                <BaseFormSelect
                  v-model="item.client_id"
                  label="Klient"
                  name="client_id"
                  :options="clients"
                />
                <BaseFormSelect
                  v-model="item.project_id"
                  label="Projekt"
                  name="project_id"
                  :options="projects"
                />
                <BaseFormSelect
                  v-model="item.currency_id"
                  label="Měna"
                  name="currency_id"
                  :options="currencyStore.currenciesOptions"
                />
                <BaseFormInput
                  v-model="item.issued_on"
                  label="Datum vystavení"
                  type="date"
                  name="issued_on"
                />
                <BaseFormInput
                  v-model="item.taxable_fulfillment_due"
                  label="Datum zdanitelného plnění"
                  type="date"
                  name="taxable_fulfillment_due"
                />
                <BaseFormInput
                  v-model="item.due_on"
                  label="Datum splatnosti"
                  type="date"
                  name="due_on"
                />
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div class="flex size-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                  <ChatBubbleLeftIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Poznámky</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormTextarea v-model="item.note" label="Poznámka" name="note" rows="3" />
                <BaseFormTextarea
                  v-model="item.footer_note"
                  label="Patička"
                  name="footer_note"
                  rows="3"
                />
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Stav</LayoutTitle
              >
              <div class="mt-4">
                <BaseFormSelect
                  v-model="item.status"
                  label=""
                  name="status"
                  :options="statusOptions"
                />
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#polozky')">
        <LayoutContainer>
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <ListBulletIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Položky faktury</LayoutTitle>
            </div>
            <button
              type="button"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
              @click="addItem"
            >
              Přidat položku
            </button>
          </div>

          <div v-if="item.items?.length === 0" class="py-12 text-center text-sm text-slate-400">
            Zatím nemáte žádné položky. Klikněte na "Přidat položku".
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(lineItem, index) in item.items"
              :key="index"
              class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <BaseFormInput
                  v-model="lineItem.name"
                  label="Název"
                  name="item_name"
                  class="sm:col-span-4"
                />
                <BaseFormInput
                  v-model="lineItem.quantity"
                  label="Množství"
                  type="number"
                  name="item_qty"
                  :step="0.01"
                  class="sm:col-span-2"
                />
                <BaseFormInput
                  v-model="lineItem.unit_name"
                  label="Jednotka"
                  name="item_unit"
                  class="sm:col-span-1"
                />
                <BaseFormInput
                  v-model="lineItem.unit_price"
                  label="Cena/ks"
                  type="number"
                  name="item_price"
                  :step="0.01"
                  class="sm:col-span-2"
                />
                <BaseFormInput
                  v-model="lineItem.vat_rate"
                  label="DPH %"
                  type="number"
                  name="item_vat"
                  class="sm:col-span-2"
                />
                <div class="flex items-end sm:col-span-1">
                  <button
                    type="button"
                    class="rounded-lg p-2 text-red-500 transition hover:bg-red-50"
                    @click="removeItem(index)"
                  >
                    <TrashIcon class="size-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#platba')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
              <CreditCardIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Platební údaje</LayoutTitle>
          </div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <BaseFormSelect
              v-model="item.payment_method"
              label="Způsob platby"
              name="payment_method"
              :options="paymentMethodOptions"
            />
            <BaseFormInput
              v-model="item.variable_symbol"
              label="Variabilní symbol"
              name="variable_symbol"
            />
            <BaseFormInput
              v-model="item.constant_symbol"
              label="Konstantní symbol"
              name="constant_symbol"
            />
            <BaseFormInput v-model="item.bank_account" label="Číslo účtu" name="bank_account" />
            <BaseFormInput v-model="item.iban" label="IBAN" name="iban" />
            <BaseFormInput v-model="item.swift_bic" label="SWIFT/BIC" name="swift_bic" />
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
