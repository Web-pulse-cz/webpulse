<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

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

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: true },
  { name: 'Cenotvorba', link: '#cenotvorba', current: false },
]);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová cenová nabídka' : 'Detail cenové nabídky',
);

const breadcrumbs = ref([
  {
    name: 'Cenové nabídky',
    link: '/cenove-nabidky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/cenove-nabidky/pridat',
    current: true,
  },
]);

const item = ref({} as Project);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>('/api/admin/price-offer/' + route.params.id, {
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
        link: '/cenove-nabidky/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst cenovou nabídku. Zkuste to prosím později.',
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
    route.params.id === 'pridat'
      ? '/api/admin/price-offer'
      : '/api/admin/price-offer/' + route.params.id,
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
            ? 'Cenová nabídka byla úspěšně vytvořena.'
            : 'Cenová nabídka byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/cenove-nabidky/' + response.id);
      } else if (redirect) {
        router.push('/cenove-nabidky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit cenovou nabídku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
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

useHead({
  title: pageTitle.value,
});

onMounted(() => {
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
      :modify-bottom="false"
      slug="price_offers"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" />
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
          </div>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#cenotvorba')">
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
          </div>
          <div class="col-span-3 grid grid-cols-1 lg:grid-cols-12">
            <LayoutContainer class="col-span-full w-full">
              <LayoutTitle>Souhrn</LayoutTitle>
              <div class="grid grid-cols-3">
                <p class="col-span-2 text-xs font-semibold text-grayDark lg:text-sm/6">
                  Celková cena:
                </p>
                <p class="col-span-1 text-xs font-medium text-grayDark lg:text-sm/6">xxx ,-</p>
                <p class="col-span-2 text-xs font-semibold text-grayDark lg:text-sm/6">DPH:</p>
                <p class="col-span-1 text-xs font-medium text-grayDark lg:text-sm/6">xxx ,-</p>
                <p class="col-span-2 text-xs font-semibold text-grayDark lg:text-sm/6">
                  Celková cena vč. DPH:
                </p>
                <p class="col-span-1 text-xs font-medium text-grayDark lg:text-sm/6">xxx ,-</p>
                <p class="col-span-2 text-xs font-semibold text-grayDark lg:text-sm/6">
                  Celkový počet hodin:
                </p>
                <p class="col-span-1 text-xs font-medium text-grayDark lg:text-sm/6">xxx</p>
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>
    </Form>
  </div>
</template>
