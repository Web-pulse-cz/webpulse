<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

import { useCurrencyStore } from '~/../stores/currencyStore';
import { useCountryStore } from '~/../stores/countryStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

interface Project {
  id: number | null;
  name: string;
  description: string;
  note: string;
  image: string;
  hourly_rate: number;
  expected_price: number;
  expected_price_vat: number;
  expected_hours: number;
  total_price: number;
  total_price_vat: number;
  total_hours: number;
  start_date: string;
  formatted_start_date: string;
  end_date: string;
  formatted_end_date: string;
  invoice_firstname: string;
  invoice_lastname: string;
  invoice_email: string;
  invoice_phone_prefix: string;
  invoice_phone: string;
  invoice_street: string;
  invoice_city: string;
  invoice_zip: string;
  invoice_country_id: number | null;
  is_delivery_address_same: boolean;
  invoice_ico: string;
  invoice_dic: string;
  delivery_firstname: string;
  delivery_lastname: string;
  delivery_email: string;
  delivery_phone_prefix: string;
  delivery_phone: string;
  delivery_street: string;
  delivery_city: string;
  delivery_zip: string;
  delivery_country_id: number | null;
  currency_id: number | null;
  client_id: number | null;
  tax_rate_id: number | null;
  status_id: number | null;
}

const statuses = ref([]);

const { $toast } = useNuxtApp();

const countryStore = useCountryStore();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Kontaktní údaje', link: '#adresy', current: false },
  { name: 'Soubory', link: '#soubory', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový projekt' : 'Detail projektu');

const breadcrumbs = ref([
  {
    name: 'Projekty',
    link: '/projekty',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/projekty/pridat',
    current: true,
  },
]);

const item = ref({} as Project);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>('/api/admin/project/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/projekty/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst projekt. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>(
    route.params.id === 'pridat' ? '/api/admin/project' : '/api/admin/project/' + route.params.id,
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
            ? 'Projekt byl úspěšně vytvořen.'
            : 'Projekt byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/projekty/' + response.id);
      } else if (redirect) {
        router.push('/projekty');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit projekt. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadStatuses() {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>('/api/admin/project/status', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      statuses.value = response.map((status) => ({
        value: status.id,
        name: status.name,
      }));
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stavy projektů. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function calculatePriceWithVAT(rate: number = 0, type: string = 'expected_price') {
  if (type === 'expected_price') {
    item.value.expected_price_vat = item.value.expected_price * (1 + rate / 100);
  } else if (type === 'total_price') {
    item.value.total_price_vat = item.value.total_price * (1 + rate / 100);
  }
}

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

watch(
  () => item.value.expected_hours,
  () => {
    if (item.value.hourly_rate === 0) {
      item.value.expected_price = 0;
    } else {
      item.value.expected_price = item.value.expected_hours * item.value.hourly_rate;
    }
  },
);
watch(
  () => item.value.expected_price,
  () => {
    const currentRate = taxRateStore.taxRates.find((rate) => rate.id === item.value.tax_rate_id);
    calculatePriceWithVAT(currentRate?.rate, 'expected_price');
  },
);
watch(
  () => item.value.total_price,
  () => {
    const currentRate = taxRateStore.taxRates.find((rate) => rate.id === item.value.tax_rate_id);
    calculatePriceWithVAT(currentRate?.rate, 'total_price');
  },
);

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  loadStatuses();
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
      slug="projects"
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

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-4">
                <BaseFormInput
                  v-model="item.name"
                  label="Název projektu"
                  type="text"
                  name="firstname"
                  rules="required|min:3"
                  class="col-span-full"
                />

                <BaseFormTextarea
                  v-model="item.description"
                  label="Podrobný popis"
                  name="description"
                  rules="required|min:3"
                  class="col-span-full lg:col-span-3"
                  rows="4"
                />

                <BaseFormTextarea
                  v-model="item.note"
                  label="Interní poznámka"
                  name="note"
                  class="col-span-full lg:col-span-1"
                  rows="4"
                />

                <div
                  class="col-span-full grid grid-cols-1 gap-6 border-t border-slate-100 pt-4 sm:grid-cols-2"
                >
                  <BaseFormInput
                    v-model="item.formatted_start_date"
                    label="Datum zahájení"
                    type="date"
                    name="formatted_start_date"
                  />
                  <BaseFormInput
                    v-model="item.formatted_end_date"
                    label="Předpokládané ukončení"
                    type="date"
                    name="formatted_end_date"
                  />
                </div>
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <BanknotesIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Finanční rozpočet</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200">
                  <BaseFormSelect
                    v-model="item.tax_rate_id"
                    label="Sazba DPH"
                    name="tax_rate_id"
                    rules="required"
                    :options="taxRateStore.taxRateOptions"
                  />
                  <BaseFormSelect
                    v-model="item.currency_id"
                    label="Měna projektu"
                    name="currency_id"
                    rules="required"
                    :options="currencyStore.currenciesOptions"
                  />
                  <BaseFormInput
                    v-model="item.hourly_rate"
                    label="Hodinová sazba (bez DPH)"
                    type="number"
                    name="hourly_rate"
                  />
                </div>

                <div class="space-y-6 lg:col-span-2">
                  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <BaseFormInput
                      v-model="item.expected_hours"
                      label="Očekávané hodiny"
                      type="number"
                      name="expected_hours"
                      :step="0.01"
                    />
                    <BaseFormInput
                      v-model="item.total_hours"
                      label="Odpracované hodiny celkem"
                      type="text"
                      name="total_hours"
                      disabled
                      class="opacity-60"
                    />
                  </div>

                  <div
                    class="grid grid-cols-1 gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2"
                  >
                    <div class="space-y-4">
                      <BaseFormInput
                        v-model="item.expected_price"
                        label="Plánovaná cena"
                        type="number"
                        name="..."
                      />
                      <BaseFormInput
                        v-model="item.expected_price_vat"
                        label="Plánovaná cena s DPH"
                        type="text"
                        disabled
                      />
                    </div>
                    <div class="space-y-4">
                      <BaseFormInput
                        v-model="item.total_price"
                        label="Reálná cena (aktuální)"
                        type="number"
                        name="..."
                      />
                      <BaseFormInput
                        v-model="item.total_price_vat"
                        label="Reálná cena s DPH"
                        type="text"
                        disabled
                      />
                    </div>
                  </div>
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Stav projektu</LayoutTitle
              >
              <div class="mt-4">
                <BaseFormSelect
                  v-model="item.status_id"
                  label=""
                  name="status_id"
                  rules="required"
                  :options="statuses"
                />
              </div>
            </LayoutContainer>

            <LayoutContainer v-if="item.events?.length" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Historie událostí</LayoutTitle
              >
              <div class="mt-6 flow-root">
                <ul role="list" class="-mb-8">
                  <li v-for="(event, index) in item.events" :key="index">
                    <div class="relative pb-8">
                      <span
                        v-if="index !== item.events.length - 1"
                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-100"
                        aria-hidden="true"
                      />
                      <div class="relative flex space-x-3">
                        <div>
                          <span
                            class="flex size-8 items-center justify-center rounded-full bg-slate-50 ring-4 ring-white"
                          >
                            <div class="size-1.5 rounded-full bg-slate-400" />
                          </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5">
                          <p class="text-[10px] font-bold uppercase text-slate-400">
                            {{ new Date(event.created_at).toLocaleString() }}
                          </p>
                          <p class="mt-1 text-xs text-slate-600">
                            {{ event.description }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#adresy')">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <LayoutContainer>
            <LayoutTitle>Fakturační údaje</LayoutTitle>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.invoice_firstname"
                label="Jméno"
                name="..."
                rules="required"
              />
              <BaseFormInput
                v-model="item.invoice_lastname"
                label="Příjmení"
                name="..."
                rules="required"
              />
              <BaseFormInput v-model="item.invoice_ico" label="IČO" name="..." rules="required" />
              <BaseFormInput v-model="item.invoice_dic" label="DIČ" name="..." />
              <BaseFormInput
                v-model="item.invoice_email"
                label="E-mail pro fakturaci"
                name="..."
                class="col-span-full"
              />

              <div class="col-span-full flex gap-3">
                <BaseFormInput
                  v-model="item.invoice_phone_prefix"
                  label="Předčíslí"
                  name="..."
                  class="w-24"
                />
                <BaseFormInput
                  v-model="item.invoice_phone"
                  label="Telefon"
                  name="..."
                  class="flex-1"
                />
              </div>

              <BaseFormInput
                v-model="item.invoice_street"
                label="Ulice a č. p."
                name="..."
                class="col-span-full"
              />
              <BaseFormInput v-model="item.invoice_city" label="PSČ" name="..." />
              <BaseFormInput v-model="item.invoice_zip" label="Město" name="..." />

              <BaseFormSelect
                v-model="item.invoice_country_id"
                label="Země"
                name="..."
                class="col-span-full"
                :options="countryStore.countriesOptions"
              />

              <div class="col-span-full mt-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <BaseFormCheckbox
                  v-model="item.is_delivery_address_same"
                  label="Dodací adresa je stejná jako fakturační"
                  name="is_delivery_address_same"
                  class="flex-row-reverse justify-between"
                />
              </div>
            </div>
          </LayoutContainer>

          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
          >
            <LayoutContainer v-if="!item.is_delivery_address_same">
              <LayoutTitle>Dodací údaje</LayoutTitle>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput v-model="item.delivery_firstname" label="Jméno" name="..." />
                <BaseFormInput v-model="item.delivery_lastname" label="Příjmení" name="..." />
                <BaseFormInput
                  v-model="item.delivery_email"
                  label="Kontaktní e-mail"
                  name="..."
                  class="col-span-full"
                />

                <div class="col-span-full flex gap-3">
                  <BaseFormInput
                    v-model="item.delivery_phone_prefix"
                    label="Předčíslí"
                    name="..."
                    class="w-24"
                  />
                  <BaseFormInput
                    v-model="item.delivery_phone"
                    label="Telefon"
                    name="..."
                    class="flex-1"
                  />
                </div>

                <BaseFormInput
                  v-model="item.delivery_street"
                  label="Ulice a č. p."
                  name="..."
                  class="col-span-full"
                />
                <BaseFormInput v-model="item.delivery_zip" label="PSČ" name="..." />
                <BaseFormInput v-model="item.delivery_city" label="Město" name="..." />

                <BaseFormSelect
                  v-model="item.delivery_country_id"
                  label="Země"
                  name="..."
                  class="col-span-full"
                  :options="countryStore.countriesOptions"
                />
              </div>
            </LayoutContainer>
          </Transition>
        </div>
      </template>
    </Form>
  </div>
</template>
