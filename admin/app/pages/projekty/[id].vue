<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useToast } from 'primevue/usetoast';
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

const toast = useToast();

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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst projekt. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
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
      toast.add({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Projekt byl úspěšně vytvořen.'
            : 'Projekt byl úspěšně upraven.',
        severity: 'succcess',
        group: 'bc',
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
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit projekt. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
        group: 'bc',
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stavy projektů. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="projects"
      @save="saveItem"
    />
    <div>
      <div class="mt-5 block">
        <nav class="isolate flex divide-x divide-gray-200 shadow-sm" aria-label="Tabs">
          <NuxtLink
            v-for="(tab, index) in tabs"
            :key="index"
            :to="tab.link"
            class="group relative min-w-0 flex-1 overflow-hidden bg-white px-2 py-2.5 text-center text-xs font-medium text-grayCustom hover:bg-gray-50 hover:text-grayDark focus:z-10 lg:px-4 lg:py-4 lg:text-sm"
          >
            <span>{{ tab.name }}</span>
            <span
              aria-hidden="true"
              :class="
                tab.current
                  ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                  : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
              "
            />
          </NuxtLink>
        </nav>
      </div>
    </div>
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-baseline gap-x-8 gap-y-2 lg:grid-cols-12 lg:gap-y-4">
          <div class="col-span-9 grid grid-cols-1 lg:grid-cols-12">
            <LayoutContainer class="col-span-full w-full">
              <LayoutTitle>Základní údaje</LayoutTitle>
              <div class="grid grid-cols-4 gap-x-8 gap-y-4">
                <BaseFormInput
                  v-model="item.name"
                  label="Název"
                  type="text"
                  name="firstname"
                  rules="required|min:3"
                  class="col-span-full"
                />
                <BaseFormTextarea
                  v-model="item.description"
                  label="Popis projektu"
                  name="description"
                  rules="required|min:3"
                  class="col-span-3"
                  :max="1000"
                />
                <BaseFormTextarea
                  v-model="item.note"
                  label="Interní poznámka"
                  name="note"
                  class="col-span-1"
                />
                <BaseFormInput
                  v-model="item.formatted_start_date"
                  label="Datum začátku"
                  type="date"
                  name="formatted_start_date"
                  class="col-span-1"
                />
                <BaseFormInput
                  v-model="item.formatted_end_date"
                  label="Datum ukončení"
                  type="date"
                  name="formatted_end_date"
                  class="col-span-1"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer class="col-span-full w-full">
              <LayoutTitle>Cenotvorba</LayoutTitle>
              <div class="grid grid-cols-6 gap-x-8 gap-y-4">
                <BaseFormSelect
                  v-model="item.tax_rate_id"
                  label="Sazba DPH"
                  name="tax_rate_id"
                  rules="required"
                  class="col-span-2"
                  :options="taxRateStore.taxRateOptions"
                />
                <BaseFormSelect
                  v-model="item.currency_id"
                  label="Měna"
                  name="currency_id"
                  rules="required"
                  class="col-span-2"
                  :options="currencyStore.currenciesOptions"
                />
                <BaseFormInput
                  v-model="item.hourly_rate"
                  :max="100000"
                  label="Hodinová sazba (bez DPH)"
                  type="number"
                  name="hourly_rate"
                  class="col-span-2"
                />
                <div class="col-span-full mb-2 mt-4 border-b border-grayLight" />
                <BaseFormInput
                  v-model="item.expected_hours"
                  label="Očekávané hodiny"
                  :max="10000000"
                  type="number"
                  name="expected_hours"
                  :step="0.01"
                  class="col-span-3"
                />
                <BaseFormInput
                  v-model="item.total_hours"
                  label="Celkové hodiny"
                  :max="10000000"
                  type="text"
                  name="total_hours"
                  class="col-span-3"
                />
                <BaseFormInput
                  v-model="item.expected_price"
                  :max="10000000"
                  label="Očekávaná cena"
                  type="number"
                  name="expected_price"
                  class="col-span-3"
                />
                <BaseFormInput
                  v-model="item.expected_price_vat"
                  label="Očekávaná cena vč. DPH"
                  type="text"
                  name="expected_price_vat"
                  disabled
                  class="col-span-3"
                />
                <BaseFormInput
                  v-model="item.total_price"
                  label="Celková cena"
                  :max="10000000"
                  type="number"
                  name="total_price"
                  class="col-span-3"
                />
                <BaseFormInput
                  v-model="item.total_price_vat"
                  label="Celková cena vč. DPH"
                  type="text"
                  name="total_price_vat"
                  disabled
                  class="col-span-3"
                />
              </div>
            </LayoutContainer>
          </div>
          <div class="col-span-3 grid grid-cols-1">
            <LayoutContainer class="col-span-1 w-full">
              <LayoutTitle>Stav projektu</LayoutTitle>
              <BaseFormSelect
                v-model="item.status_id"
                label="Stav projektu"
                name="status_id"
                rules="required"
                class="col-span-2"
                :options="statuses"
              />
            </LayoutContainer>
            <LayoutContainer v-if="item.events && item.events.length" class="col-span-1 w-full">
              <LayoutTitle>Historie</LayoutTitle>
              <div class="grid grid-cols-1">
                <div v-for="(event, index) in item.events" :key="index" class="col-span-full">
                  <p class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6">
                    {{ new Date(event.created_at).toLocaleString() }}
                  </p>
                  <p class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6">
                    {{ event.description }}
                  </p>
                </div>
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#adresy')">
        <div class="grid grid-cols-1 gap-x-8 gap-y-2 lg:grid-cols-2 lg:gap-y-4">
          <LayoutContainer class="col-span-1 w-full">
            <LayoutTitle>Fakturační údaje</LayoutTitle>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.invoice_firstname"
                label="Jméno"
                type="text"
                name="invoice_firstname"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_lastname"
                label="Příjmení"
                type="text"
                name="invoice_lastname"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_ico"
                label="IČO"
                type="text"
                name="invoice_ico"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_dic"
                label="DIČ"
                type="text"
                name="invoice_dic"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_email"
                label="E-mail"
                type="text"
                rules="email"
                name="invoice_email"
                class="col-span-full"
              />
              <BaseFormInput
                v-model="item.invoice_phone_prefix"
                label="Předčíslí"
                type="text"
                name="invoice_phone_prefix"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_phone"
                label="Telefonní číslo"
                type="text"
                name="invoice_phone"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_street"
                label="Ulice a č. p."
                type="text"
                name="invoice_street"
                rules="required|min:3"
                class="col-span-full"
              />
              <BaseFormInput
                v-model="item.invoice_city"
                label="PSČ"
                type="text"
                name="invoice_city"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.invoice_zip"
                label="Město"
                type="text"
                name="invoice_zip"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormSelect
                v-model="item.invoice_country_id"
                label="Země"
                name="invoice_country_id"
                rules="required"
                class="col-span-1"
                :options="countryStore.countriesOptions"
              />
              <BaseFormCheckbox
                v-model="item.is_delivery_address_same"
                :checked="item.is_delivery_address_same"
                label="Dodací adresa je stejná jako fakturační"
                name="is_delivery_address_same"
                label-color="grayCustom"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer v-if="!item.is_delivery_address_same" class="col-span-1 w-full">
            <LayoutTitle>Dodací údaje</LayoutTitle>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.delivery_firstname"
                label="Jméno"
                type="text"
                name="delivery_firstname"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.delivery_lastname"
                label="Příjmení"
                type="text"
                name="delivery_lastname"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.delivery_email"
                label="E-mail"
                type="text"
                name="delivery_email"
                rules="required|min:3"
                class="col-span-full"
              />
              <BaseFormInput
                v-model="item.delivery_phone_prefix"
                label="Předčíslí"
                type="text"
                name="delivery_phone_prefix"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.delivery_phone"
                label="Telefonní číslo"
                type="text"
                name="delivery_phone"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.delivery_street"
                label="Ulice a č. p."
                type="text"
                name="delivery_street"
                rules="required|min:3"
                class="col-span-full"
              />
              <BaseFormInput
                v-model="item.delivery_zip"
                label="PSČ"
                type="text"
                name="delivery_city"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.delivery_city"
                label="Město"
                type="text"
                name="delivery_city"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormSelect
                v-model="item.delivery_country_id"
                label="Země"
                name="delivery_country_id"
                rules="required"
                class="col-span-1"
                :options="countryStore.countriesOptions"
              />
            </div>
          </LayoutContainer>
        </div>
      </template>
    </Form>
  </div>
</template>
