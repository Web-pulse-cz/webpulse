<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import { DocumentIcon, DocumentTextIcon, FolderIcon, TrashIcon } from '@heroicons/vue/24/outline';

import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Položky', link: '#polozky', current: false },
  { name: 'Soubory', link: '#soubory', current: false },
]);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová cenová nabídka' : 'Detail cenové nabídky',
);

const breadcrumbs = ref([
  { name: 'Cenové nabídky', link: '/cenove-nabidky', current: false },
  { name: pageTitle.value, link: '/cenove-nabidky/pridat', current: true },
]);

const clients = ref([]);
const projects = ref([]);

const item = ref({
  id: null,
  code: '',
  client_id: null,
  project_id: null,
  title: '',
  introduction: '',
  note: '',
  terms: '',
  status: 'draft',
  currency_id: null,
  tax_rate_id: null,
  total_without_vat: 0,
  total_vat: 0,
  total_with_vat: 0,
  valid_to: '',
  invoice_id: null,
  items: [] as any[],
  files: [] as any[],
  sites: [] as number[],
});

let itemKeyCounter = 0;

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/price-offer/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = {
        ...response,
        sites: Array.isArray(response.sites)
          ? response.sites.map((s: any) => (typeof s === 'object' ? s.id : s))
          : [],
        items: (response.items || []).map((i: any) => ({ ...i, _key: itemKeyCounter++ })),
      };
      pageTitle.value = item.value.code || 'Nabídka #' + item.value.id;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/cenove-nabidky/' + route.params.id,
        current: true,
      };
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst cenovou nabídku.',
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
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      const data = response?.data || response;
      clients.value = Array.isArray(data)
        ? data.map((c: any) => ({ value: c.id, name: c.name }))
        : [];
    })
    .catch(() => {});
}

async function loadProjects() {
  const client = useSanctumClient();
  await client('/api/admin/project', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      const data = response?.data || response;
      projects.value = Array.isArray(data)
        ? data.map((p: any) => ({ value: p.id, name: p.name }))
        : [];
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat'
      ? '/api/admin/price-offer'
      : '/api/admin/price-offer/' + route.params.id,
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
        detail: route.params.id === 'pridat' ? 'Nabídka byla vytvořena.' : 'Nabídka byla upravena.',
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
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit nabídku.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

// ─── Items ─────────────────────────────────────────────────

function addItem() {
  item.value.items.push({
    _key: itemKeyCounter++,
    name: '',
    description: '',
    quantity: 1,
    unit_name: 'ks',
    unit_price_without_vat: 0,
    vat_rate: 21,
  });
}

function removeItem(index: number) {
  item.value.items.splice(index, 1);
}

// ─── Accept / Reject / PDF ─────────────────────────────────

const showAcceptDialog = ref(false);
const acceptDocumentType = ref('invoice');

async function acceptOffer() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/price-offer/' + route.params.id + '/accept', {
    method: 'POST',
    body: JSON.stringify({ document_type: acceptDocumentType.value }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      showAcceptDialog.value = false;
      $toast.show({
        summary: 'Hotovo',
        detail: 'Nabídka byla přijata a faktura vytvořena.',
        severity: 'success',
      });
      loadItem();
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se přijmout nabídku.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function rejectOffer() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/price-offer/' + route.params.id + '/reject', {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Nabídka byla zamítnuta.', severity: 'success' });
      loadItem();
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se zamítnout nabídku.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function downloadFile(fileId: number) {
  const client = useSanctumClient();
  try {
    const res = await client.raw('/api/admin/price-offer/' + item.value.id + '/file/' + fileId, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cenova-nabidka-' + (item.value.code || item.value.id) + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
  }
}

async function downloadPdf() {
  const client = useSanctumClient();
  try {
    const res = await client.raw('/api/admin/price-offer/' + route.params.id + '/pdf', {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cenova-nabidka-' + route.params.id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout PDF.', severity: 'error' });
  }
}

const statusOptions = ref([
  { value: 'draft', name: 'Koncept' },
  { value: 'sent', name: 'Odeslaná' },
  { value: 'accepted', name: 'Přijatá' },
  { value: 'rejected', name: 'Zamítnutá' },
  { value: 'expired', name: 'Vypršelá' },
]);

watchEffect(() => {
  if (!route?.path) return;
  const hash = route.hash || '#info';
  tabs.value.forEach((tab) => {
    tab.current = tab.link === hash;
  });
  if (!tabs.value.some((t) => t.current)) {
    tabs.value[0].current = true;
  }
});

useHead({ title: pageTitle.value });

onMounted(() => {
  loadClients();
  loadProjects();
  if (route.params.id !== 'pridat') {
    loadItem();
  }
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
      slug="price_offers"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <!-- Info tab -->
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
                <BaseFormInput
                  v-model="item.title"
                  label="Název nabídky"
                  type="text"
                  name="title"
                  class="col-span-full"
                />
                <BaseFormSelect
                  v-model="item.client_id"
                  label="Klient"
                  name="client_id"
                  :options="clients"
                  placeholder="-- Bez klienta --"
                />
                <BaseFormSelect
                  v-model="item.project_id"
                  label="Projekt"
                  name="project_id"
                  :options="projects"
                  placeholder="-- Bez projektu --"
                />
                <BaseFormSelect
                  v-model="item.currency_id"
                  label="Měna"
                  name="currency_id"
                  :options="currencyStore.currenciesOptions"
                />
                <BaseFormSelect
                  v-model="item.tax_rate_id"
                  label="Sazba DPH"
                  name="tax_rate_id"
                  :options="taxRateStore.taxRateOptions"
                />
                <BaseFormInput
                  v-model="item.valid_to"
                  label="Platnost do"
                  type="date"
                  name="valid_to"
                />
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600"
                >
                  <DocumentTextIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Texty</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6">
                <BaseFormTextarea
                  v-model="item.introduction"
                  label="Úvod"
                  name="introduction"
                  rows="3"
                />
                <BaseFormTextarea
                  v-model="item.terms"
                  label="Obchodní podmínky"
                  name="terms"
                  rows="3"
                />
                <BaseFormTextarea v-model="item.note" label="Poznámka" name="note" rows="2" />
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

            <LayoutContainer v-if="item.code" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Kód</LayoutTitle
              >
              <p class="mt-2 text-lg font-bold text-indigo-600">{{ item.code }}</p>
            </LayoutContainer>

            <LayoutContainer v-if="item.id && route.params.id !== 'pridat'" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Akce</LayoutTitle
              >
              <div class="mt-4 space-y-2">
                <button
                  v-if="item.status === 'draft' || item.status === 'sent'"
                  type="button"
                  class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-500"
                  @click="showAcceptDialog = true"
                >
                  Přijmout nabídku
                </button>
                <button
                  v-if="item.status === 'draft' || item.status === 'sent'"
                  type="button"
                  class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-500"
                  @click="rejectOffer"
                >
                  Zamítnout
                </button>
                <button
                  type="button"
                  class="w-full rounded-lg bg-slate-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-500"
                  @click="downloadPdf"
                >
                  Stáhnout PDF
                </button>
                <NuxtLink
                  v-if="item.invoice_id"
                  :to="'/faktury/' + item.invoice_id"
                  class="block w-full rounded-lg bg-indigo-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-indigo-500"
                >
                  Zobrazit fakturu
                </NuxtLink>
              </div>
            </LayoutContainer>

            <LayoutContainer v-if="item.total_with_vat" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Celkem</LayoutTitle
              >
              <div class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between">
                  <span class="text-slate-500">Bez DPH</span>
                  <span class="font-medium">{{ item.total_without_vat }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-500">DPH</span>
                  <span class="font-medium">{{ item.total_vat }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-1">
                  <span class="font-bold text-slate-900">S DPH</span>
                  <span class="font-bold text-indigo-600">{{ item.total_with_vat }}</span>
                </div>
              </div>
            </LayoutContainer>
          </div>
        </div>
      </template>

      <!-- Items tab -->
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#polozky')">
        <LayoutContainer>
          <div class="mb-6 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Položky nabídky</LayoutTitle>
            <button
              type="button"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
              @click="addItem"
            >
              Přidat položku
            </button>
          </div>

          <div v-if="item.items?.length === 0" class="py-12 text-center text-sm text-slate-400">
            Zatím nemáte žádné položky.
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(lineItem, index) in item.items"
              :key="lineItem._key ?? lineItem.id ?? index"
              class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <BaseFormInput
                  v-model="lineItem.name"
                  label="Název"
                  :name="'item_name_' + index"
                  class="sm:col-span-3"
                />
                <BaseFormInput
                  v-model="lineItem.description"
                  label="Popis"
                  :name="'item_desc_' + index"
                  class="sm:col-span-3"
                />
                <BaseFormInput
                  v-model="lineItem.quantity"
                  label="Množství"
                  type="number"
                  :name="'item_qty_' + index"
                  :step="0.01"
                  class="sm:col-span-1"
                />
                <BaseFormInput
                  v-model="lineItem.unit_name"
                  label="Jedn."
                  :name="'item_unit_' + index"
                  class="sm:col-span-1"
                />
                <BaseFormInput
                  v-model="lineItem.unit_price_without_vat"
                  label="Cena/ks"
                  type="number"
                  :name="'item_price_' + index"
                  :step="0.01"
                  class="sm:col-span-2"
                />
                <BaseFormInput
                  v-model="lineItem.vat_rate"
                  label="DPH %"
                  type="number"
                  :name="'item_vat_' + index"
                  class="sm:col-span-1"
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

      <!-- Files tab -->
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#soubory')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
            >
              <FolderIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Soubory</LayoutTitle>
          </div>

          <div v-if="!item.files?.length" class="py-12 text-center text-sm text-slate-400">
            Žádné soubory. PDF se vygeneruje automaticky při uložení nabídky.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="file in item.files"
              :key="file.id"
              class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="flex items-center gap-4">
                <div
                  class="flex size-10 items-center justify-center rounded-lg bg-red-50 text-red-600"
                >
                  <DocumentIcon class="size-5" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">{{ file.name }}</p>
                  <p class="text-xs text-slate-400">
                    {{ file.mime_type }}
                    <span v-if="file.size" class="ml-2"
                      >{{ (file.size / 1024).toFixed(0) }} KB</span
                    >
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                @click="downloadFile(file.id)"
              >
                Stáhnout
              </button>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>

    <!-- Accept Dialog -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showAcceptDialog"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showAcceptDialog = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold text-slate-900">Přijmout nabídku</h3>
            <p class="mt-2 text-sm text-slate-500">
              Vyberte typ dokladu, který se vytvoří z této nabídky:
            </p>
            <div class="mt-4 space-y-3">
              <label
                class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition"
                :class="
                  acceptDocumentType === 'invoice'
                    ? 'border-indigo-500 bg-indigo-50'
                    : 'border-slate-200'
                "
              >
                <input
                  v-model="acceptDocumentType"
                  type="radio"
                  value="invoice"
                  class="text-indigo-600"
                />
                <span class="text-sm font-medium">Faktura</span>
              </label>
              <label
                class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition"
                :class="
                  acceptDocumentType === 'proforma'
                    ? 'border-indigo-500 bg-indigo-50'
                    : 'border-slate-200'
                "
              >
                <input
                  v-model="acceptDocumentType"
                  type="radio"
                  value="proforma"
                  class="text-indigo-600"
                />
                <span class="text-sm font-medium">Proforma faktura</span>
              </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
              <button
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                @click="showAcceptDialog = false"
              >
                Zrušit
              </button>
              <button
                type="button"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-500"
                @click="acceptOffer"
              >
                Přijmout
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
