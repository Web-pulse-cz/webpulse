<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import {
  UserIcon,
  MapPinIcon,
  TruckIcon,
  BanknotesIcon,
  ChatBubbleLeftIcon,
  DocumentTextIcon,
  FolderIcon,
} from '@heroicons/vue/24/outline';

import { useCountryStore } from '~/../stores/countryStore';

const { $toast } = useNuxtApp();
const { formatCurrency, formatDate } = useFormat();
const countryStore = useCountryStore();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Adresy', link: '#adresy', current: false },
  { name: 'Bankovní údaje', link: '#banka', current: false },
  { name: 'Faktury', link: '#faktury', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový klient' : 'Detail klienta');

const breadcrumbs = ref([
  {
    name: 'Klienti',
    link: '/klienti',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/klienti/pridat',
    current: true,
  },
]);

const item = ref({
  id: null,
  type: 'customer',
  name: '',
  full_name: '',
  email: '',
  email_copy: '',
  phone_prefix: '+420',
  phone: '',
  ico: '',
  dic: '',
  web: '',
  street: '',
  city: '',
  zip: '',
  country_id: null,
  has_delivery_address: false,
  delivery_name: '',
  delivery_street: '',
  delivery_city: '',
  delivery_zip: '',
  delivery_country_id: null,
  bank_account_number: '',
  bank_account_iban: '',
  bank_account_swift: '',
  variable_symbol: '',
  note: '',
  sites: [] as number[],
});

const clientInvoices = ref([]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/client/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = Array.isArray(response.sites)
        ? response.sites.map((s: any) => (typeof s === 'object' ? s.id : s))
        : [];
      pageTitle.value = item.value.name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/klienti/' + route.params.id,
        current: true,
      };
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst klienta.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadClientInvoices() {
  const client = useSanctumClient();

  await client('/api/admin/invoice', {
    method: 'GET',
    query: { client_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      clientInvoices.value = response?.data || response;
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat' ? '/api/admin/client' : '/api/admin/client/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Klient byl úspěšně vytvořen.'
            : 'Klient byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/klienti/' + response.id);
      } else if (redirect) {
        router.push('/klienti');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit klienta. Zkontrolujte vyplněná pole a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

const typeOptions = ref([
  { value: 'customer', name: 'Odběratel' },
  { value: 'supplier', name: 'Dodavatel' },
  { value: 'both', name: 'Obojí' },
]);

// ─── Price Offers (Soubory tab) ──────────────────────────────
const clientPriceOffers = ref([]);

async function loadClientPriceOffers() {
  if (route.params.id === 'pridat') return;
  const client = useSanctumClient();
  await client('/api/admin/price-offer', {
    method: 'GET',
    query: { client_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      clientPriceOffers.value = Array.isArray(d) ? d : [];
      if (clientPriceOffers.value.length > 0 && !tabs.value.find((t) => t.link === '#soubory')) {
        tabs.value.push({ name: 'Soubory', link: '#soubory', current: false });
      }
    })
    .catch(() => {});
}

async function downloadClientPriceOfferFile(offer: any) {
  const client = useSanctumClient();
  try {
    const file = offer.files?.[0];
    if (!file) {
      $toast.show({ summary: 'Info', detail: 'Žádný přiložený soubor.', severity: 'warning' });
      return;
    }
    const res = await client.raw('/api/admin/price-offer/' + offer.id + '/file/' + file.id, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'cenova-nabidka-' + offer.id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
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

watch(selectedSiteHash, () => loadItem());

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
    loadClientInvoices();
    loadClientPriceOffers();
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
      slug="clients"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form ref="formRef" @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <UserIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.name"
                  label="Název / Jméno"
                  type="text"
                  name="name"
                  rules="required|min:2"
                  class="col-span-full"
                />

                <BaseFormInput
                  v-model="item.full_name"
                  label="Celý název"
                  type="text"
                  name="full_name"
                />

                <BaseFormSelect
                  v-model="item.type"
                  label="Typ"
                  name="type"
                  :options="typeOptions"
                />

                <BaseFormInput v-model="item.email" label="E-mail" type="email" name="email" />

                <BaseFormInput
                  v-model="item.email_copy"
                  label="Kopie e-mailu"
                  type="email"
                  name="email_copy"
                />

                <div class="col-span-full flex gap-3 sm:col-span-1">
                  <BaseFormInput
                    v-model="item.phone_prefix"
                    label="Předčíslí"
                    name="phone_prefix"
                    class="w-24"
                  />
                  <BaseFormInput v-model="item.phone" label="Telefon" name="phone" class="flex-1" />
                </div>

                <BaseFormInput v-model="item.web" label="Web" type="text" name="web" />

                <div class="col-span-full grid grid-cols-2 gap-6 border-t border-slate-100 pt-4">
                  <BaseFormInput v-model="item.ico" label="IČO" name="ico" />
                  <BaseFormInput v-model="item.dic" label="DIČ" name="dic" />
                </div>
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600"
                >
                  <ChatBubbleLeftIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Poznámka</LayoutTitle>
              </div>
              <BaseFormTextarea v-model="item.note" label="" name="note" rows="4" />
            </LayoutContainer>
          </div>

          <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutContainer v-if="item.synced_at" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Fakturoid</LayoutTitle
              >
              <div class="mt-2 text-xs text-slate-500">
                <p>ID: {{ item.fakturoid_id || '—' }}</p>
                <p class="mt-1">
                  Synced: {{ item.synced_at ? new Date(item.synced_at).toLocaleString() : '—' }}
                </p>
              </div>
            </LayoutContainer>
            <LayoutActionsDetailBlock
              v-model:sites="item.sites"
              :allow-image="false"
              :allow-is-active="false"
              :allow-translations="false"
            />
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#adresy')">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <MapPinIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Fakturační adresa</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.street"
                label="Ulice a č. p."
                name="street"
                class="col-span-full"
              />
              <BaseFormInput v-model="item.zip" label="PSČ" name="zip" />
              <BaseFormInput v-model="item.city" label="Město" name="city" />
              <BaseFormSelect
                v-model="item.country_id"
                label="Země"
                name="country_id"
                class="col-span-full"
                :options="countryStore.countriesOptions"
              />

              <div class="col-span-full mt-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <BaseFormCheckbox
                  v-model="item.has_delivery_address"
                  label="Má odlišnou doručovací adresu"
                  name="has_delivery_address"
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
            <LayoutContainer v-if="item.has_delivery_address">
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                >
                  <TruckIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Doručovací adresa</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.delivery_name"
                  label="Jméno / Firma"
                  name="delivery_name"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.delivery_street"
                  label="Ulice a č. p."
                  name="delivery_street"
                  class="col-span-full"
                />
                <BaseFormInput v-model="item.delivery_zip" label="PSČ" name="delivery_zip" />
                <BaseFormInput v-model="item.delivery_city" label="Město" name="delivery_city" />
                <BaseFormSelect
                  v-model="item.delivery_country_id"
                  label="Země"
                  name="delivery_country_id"
                  class="col-span-full"
                  :options="countryStore.countriesOptions"
                />
              </div>
            </LayoutContainer>
          </Transition>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#banka')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
            >
              <BanknotesIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Bankovní údaje</LayoutTitle>
          </div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <BaseFormInput
              v-model="item.bank_account_number"
              label="Číslo účtu"
              name="bank_account_number"
            />
            <BaseFormInput v-model="item.bank_account_iban" label="IBAN" name="bank_account_iban" />
            <BaseFormInput
              v-model="item.bank_account_swift"
              label="SWIFT/BIC"
              name="bank_account_swift"
            />
            <BaseFormInput
              v-model="item.variable_symbol"
              label="Variabilní symbol"
              name="variable_symbol"
            />
          </div>
        </LayoutContainer>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#faktury')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
            >
              <DocumentTextIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Faktury klienta</LayoutTitle>
          </div>
          <div v-if="clientInvoices.length === 0" class="py-8 text-center text-sm text-slate-400">
            Tento klient zatím nemá žádné faktury.
          </div>
          <div v-else class="space-y-3">
            <NuxtLink
              v-for="inv in clientInvoices"
              :key="inv.id"
              :to="'/faktury/' + inv.id"
              class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:ring-1 hover:ring-indigo-200"
            >
              <div class="flex items-center gap-4">
                <div
                  class="flex size-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <BanknotesIcon class="size-5" />
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-900">{{ inv.number || '—' }}</span>
                    <span class="text-sm text-slate-500">{{ inv.subject }}</span>
                    <span
                      class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                      :class="{
                        'bg-green-100 text-green-700': inv.status === 'paid',
                        'bg-yellow-100 text-yellow-700': inv.status === 'sent',
                        'bg-red-100 text-red-700': inv.status === 'overdue',
                        'bg-slate-100 text-slate-700': inv.status === 'open',
                        'bg-gray-100 text-gray-500': inv.status === 'cancelled',
                      }"
                    >
                      {{
                        {
                          paid: 'Zaplacená',
                          sent: 'Odeslaná',
                          overdue: 'Po splatnosti',
                          open: 'Otevřená',
                          cancelled: 'Stornovaná',
                        }[inv.status] || inv.status
                      }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-400">
                    {{ formatCurrency(inv.total) }}
                  </p>
                </div>
              </div>
            </NuxtLink>
          </div>
        </LayoutContainer>
      </template>

      <!-- Soubory tab (price offers linked to this client) -->
      <template v-if="tabs.find((t) => t.current && t.link === '#soubory')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
            >
              <FolderIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Soubory</LayoutTitle>
          </div>

          <div v-if="!clientPriceOffers.length" class="py-12 text-center text-sm text-slate-400">
            Žádné soubory.
          </div>
          <div v-else>
            <h3 class="mb-3 text-sm font-semibold text-slate-500">Cenové nabídky</h3>
            <div class="space-y-3">
              <div
                v-for="offer in clientPriceOffers"
                :key="offer.id"
                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
              >
                <NuxtLink
                  :to="'/cenove-nabidky/' + offer.id"
                  class="flex flex-1 items-center gap-4"
                >
                  <div
                    class="flex size-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                  >
                    <DocumentTextIcon class="size-5" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-medium text-slate-900"
                        >{{ offer.code }} — {{ offer.title }}</span
                      >
                      <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                        :class="{
                          'bg-slate-100 text-slate-600': offer.status === 'draft',
                          'bg-blue-100 text-blue-700': offer.status === 'sent',
                          'bg-emerald-100 text-emerald-700': offer.status === 'accepted',
                          'bg-red-100 text-red-700': offer.status === 'rejected',
                          'bg-amber-100 text-amber-700': offer.status === 'expired',
                        }"
                      >
                        {{
                          {
                            draft: 'Koncept',
                            sent: 'Odeslaná',
                            accepted: 'Přijatá',
                            rejected: 'Zamítnutá',
                            expired: 'Vypršelá',
                          }[offer.status] || offer.status
                        }}
                      </span>
                    </div>
                    <p class="text-xs text-slate-400">
                      {{ formatCurrency(offer.total_with_vat) }} · Platnost do
                      {{ offer.valid_to || '—' }}
                    </p>
                  </div>
                </NuxtLink>
                <button
                  v-if="offer.files?.length"
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                  @click="downloadClientPriceOfferFile(offer)"
                >
                  Stáhnout PDF
                </button>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
