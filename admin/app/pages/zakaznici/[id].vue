<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  UserIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  BanknotesIcon,
  TicketIcon,
  StarIcon,
} from '@heroicons/vue/24/outline';
import { useCountryStore } from '~/../stores/countryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const countryStore = useCountryStore();
const currencyStore = useCurrencyStore();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();
const loading = ref(false);

const tabs = ref([
  { name: 'Osobní údaje', link: '#osobni', current: false },
  { name: 'Finance', link: '#finance', current: false },
  { name: 'Vouchery', link: '#vouchery', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový zákazník' : 'Detail zákazníka');
const breadcrumbs = ref([
  { name: 'Zákazníci', link: '/zakaznici', current: false },
  { name: pageTitle.value, link: '/zakaznici/pridat', current: true },
]);

const groups = ref([]);

const item = ref({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
  phone_prefix: '+420',
  phone: '',
  date_of_birth: '',
  gender: null,
  street: '',
  city: '',
  zip: '',
  country_id: null,
  company_name: '',
  ico: '',
  dic: '',
  total_spent: 0,
  credit_balance: 0,
  currency_id: null,
  rating: null,
  customer_group_id: null,
  status: 'active',
  note: '',
  sites: [] as number[],
  vouchers: [] as any[],
});

const statusOptions = ref([
  { value: 'active', name: 'Aktivní' },
  { value: 'inactive', name: 'Neaktivní' },
  { value: 'blocked', name: 'Blokovaný' },
]);
const genderOptions = ref([
  { value: 'male', name: 'Muž' },
  { value: 'female', name: 'Žena' },
  { value: 'other', name: 'Jiné' },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/customer/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = { ...r, sites: r.sites || [] };
      pageTitle.value = r.first_name + ' ' + r.last_name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/zakaznici/' + route.params.id,
        current: true,
      };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadGroups() {
  const client = useSanctumClient();
  await client('/api/admin/customer/group', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      groups.value = d.map((g: any) => ({ value: g.id, name: g.name }));
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat' ? '/api/admin/customer' : '/api/admin/customer/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Zákazník vytvořen.' : 'Zákazník upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') router.push('/zakaznici/' + r.id);
      else if (redirect) router.push('/zakaznici');
      else loadItem();
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit zákazníka.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watchEffect(() => {
  const h = route.hash;
  if (h)
    tabs.value.forEach((t) => {
      t.current = t.link === h;
    });
  else {
    tabs.value[0].current = true;
    router.push(route.path + '#osobni');
  }
});

watch(selectedSiteHash, () => {
  loadItem();
});
useHead({ title: pageTitle.value });
onMounted(() => {
  loadGroups();
  if (route.params.id !== 'pridat') loadItem();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="customers"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form ref="formRef" @submit="saveItem">
      <!-- Osobní údaje -->
      <template v-if="tabs.find((t) => t.current && t.link === '#osobni')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <UserIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Osobní údaje</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.first_name"
                  label="Jméno"
                  name="first_name"
                  rules="required"
                />
                <BaseFormInput
                  v-model="item.last_name"
                  label="Příjmení"
                  name="last_name"
                  rules="required"
                />
                <BaseFormInput
                  v-model="item.email"
                  label="E-mail"
                  type="email"
                  name="email"
                  rules="required|email"
                />
                <div class="flex gap-3">
                  <BaseFormInput
                    v-model="item.phone_prefix"
                    label="Předčíslí"
                    name="phone_prefix"
                    class="w-24"
                  />
                  <BaseFormInput v-model="item.phone" label="Telefon" name="phone" class="flex-1" />
                </div>
                <BaseFormInput
                  v-model="item.date_of_birth"
                  label="Datum narození"
                  type="date"
                  name="date_of_birth"
                />
                <BaseFormSelect
                  v-model="item.gender"
                  label="Pohlaví"
                  name="gender"
                  :options="genderOptions"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <MapPinIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Adresa</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.street"
                  label="Ulice"
                  name="street"
                  class="col-span-full"
                />
                <BaseFormInput v-model="item.zip" label="PSČ" name="zip" />
                <BaseFormInput v-model="item.city" label="Město" name="city" />
                <BaseFormSelect
                  v-model="item.country_id"
                  label="Země"
                  name="country_id"
                  :options="countryStore.countriesOptions"
                  class="col-span-full"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600"
                >
                  <BuildingOfficeIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Firemní údaje</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <BaseFormInput v-model="item.company_name" label="Firma" name="company_name" />
                <BaseFormInput v-model="item.ico" label="IČO" name="ico" />
                <BaseFormInput v-model="item.dic" label="DIČ" name="dic" />
              </div>
            </LayoutContainer>
          </div>
          <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:sites="item.sites"
              :allow-image="false"
              :allow-is-active="false"
              :allow-translations="false"
            />
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
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Skupina</LayoutTitle
              >
              <div class="mt-4">
                <BaseFormSelect
                  v-model="item.customer_group_id"
                  label=""
                  name="group"
                  :options="groups"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Hodnocení</LayoutTitle
              >
              <div class="mt-3 flex gap-1">
                <button
                  v-for="star in 5"
                  :key="star"
                  type="button"
                  @click="item.rating = item.rating === star ? null : star"
                >
                  <StarIcon
                    :class="[
                      'size-7 transition',
                      star <= (item.rating || 0)
                        ? 'fill-amber-400 text-amber-400'
                        : 'text-slate-300',
                    ]"
                  />
                </button>
              </div>
            </LayoutContainer>
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Poznámka</LayoutTitle
              >
              <BaseFormTextarea v-model="item.note" label="" name="note" rows="3" class="mt-2" />
            </LayoutContainer>
          </div>
        </div>
      </template>

      <!-- Finance -->
      <template v-if="tabs.find((t) => t.current && t.link === '#finance')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
            >
              <BanknotesIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Finanční přehled</LayoutTitle>
          </div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <BaseFormInput
              v-model="item.total_spent"
              label="Celková útrata"
              type="number"
              name="total_spent"
              :step="0.01"
            />
            <BaseFormInput
              v-model="item.credit_balance"
              label="Kreditní zůstatek"
              type="number"
              name="credit_balance"
              :step="0.01"
            />
            <BaseFormSelect
              v-model="item.currency_id"
              label="Měna"
              name="currency_id"
              :options="currencyStore.currenciesOptions"
            />
          </div>
        </LayoutContainer>
      </template>

      <!-- Vouchery -->
      <template v-if="tabs.find((t) => t.current && t.link === '#vouchery')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600"
            >
              <TicketIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Přiřazené vouchery</LayoutTitle>
          </div>
          <div v-if="!item.vouchers?.length" class="py-12 text-center text-sm text-slate-400">
            Zákazník nemá přiřazené žádné vouchery.
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="v in item.vouchers"
              :key="v.id"
              class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="flex items-center gap-4">
                <div
                  class="flex size-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600"
                >
                  <TicketIcon class="size-5" />
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-bold text-indigo-600">{{ v.code }}</span>
                    <span class="text-sm font-medium text-slate-900">{{ v.name }}</span>
                  </div>
                  <p class="text-xs text-slate-400">Použito: {{ v.times_used }}x</p>
                </div>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
