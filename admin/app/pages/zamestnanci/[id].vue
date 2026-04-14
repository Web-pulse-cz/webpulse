<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import {
  UserIcon,
  MapPinIcon,
  PhoneIcon,
  BriefcaseIcon,
  BanknotesIcon,
  HeartIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline';
import { useCountryStore } from '~/../stores/countryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const countryStore = useCountryStore();
const currencyStore = useCurrencyStore();
const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Osobní údaje', link: '#osobni', current: false },
  { name: 'Pracovní údaje', link: '#prace', current: false },
  { name: 'Banka a pojištění', link: '#banka', current: false },
  { name: 'Soubory', link: '#soubory', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový zaměstnanec' : 'Detail zaměstnance');
const breadcrumbs = ref([
  { name: 'Zaměstnanci', link: '/zamestnanci', current: false },
  { name: pageTitle.value, link: '/zamestnanci/pridat', current: true },
]);

const divisions = ref([]);
const sites = ref([]);

const item = ref({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
  phone_prefix: '+420',
  phone: '',
  date_of_birth: '',
  gender: null,
  personal_id_number: '',
  id_card_number: '',
  nationality: '',
  street: '',
  city: '',
  zip: '',
  country_id: null,
  position: '',
  employee_number: '',
  status: 'active',
  date_hired: '',
  date_terminated: '',
  hourly_rate: 0,
  monthly_salary: 0,
  currency_id: null,
  bank_account_number: '',
  bank_account_iban: '',
  bank_account_swift: '',
  health_insurance_company: '',
  health_insurance_number: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relation: '',
  photo: '',
  note: '',
  divisions: [] as number[],
  sites: [] as number[],
});

const genderOptions = ref([
  { value: 'male', name: 'Muž' },
  { value: 'female', name: 'Žena' },
  { value: 'other', name: 'Jiné' },
]);
const statusOptions = ref([
  { value: 'active', name: 'Aktivní' },
  { value: 'on_leave', name: 'Na dovolené' },
  { value: 'terminated', name: 'Ukončený' },
  { value: 'suspended', name: 'Pozastavený' },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/employee/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = {
        ...r,
        divisions: r.divisions?.map((d: any) => d.id) || [],
        sites: r.sites || [],
      };
      employeeFiles.value = r.files || [];
      pageTitle.value = r.first_name + ' ' + r.last_name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/zamestnanci/' + route.params.id,
        current: true,
      };
    })
    .catch(() => {
      error.value = true;
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadDivisions() {
  const client = useSanctumClient();
  await client('/api/admin/employee/division', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      divisions.value = r;
    })
    .catch(() => {});
}

async function loadSites() {
  const client = useSanctumClient();
  await client('/api/admin/site', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      sites.value = r;
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat' ? '/api/admin/employee' : '/api/admin/employee/' + route.params.id,
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
    .then((r) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Zaměstnanec vytvořen.' : 'Zaměstnanec upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') router.push('/zamestnanci/' + r.id);
      else if (redirect) router.push('/zamestnanci');
      else loadItem();
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit zaměstnance.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

// ─── Files & Contracts (Soubory tab) ──────────────────────────────
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const employeeContracts = ref([]);
const employeeFiles = ref([] as any[]);

async function loadEmployeeContracts() {
  if (route.params.id === 'pridat') return;
  const client = useSanctumClient();
  await client('/api/admin/contract', {
    method: 'GET',
    query: { employee_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      employeeContracts.value = Array.isArray(d) ? d : [];
    })
    .catch(() => {});
}

function onFileUploaded(files: any[]) {
  employeeFiles.value = files;
}

function onFileDeleted(fileId: number) {
  employeeFiles.value = employeeFiles.value.filter((f: any) => f.id !== fileId);
}

async function downloadContractFile(contract: any) {
  const client = useSanctumClient();
  try {
    const file = contract.files?.[0];
    if (!file) {
      $toast.show({
        summary: 'Info',
        detail: 'Smlouva nemá přiložený soubor.',
        severity: 'warning',
      });
      return;
    }
    const res = await client.raw('/api/admin/contract/' + contract.id + '/file/' + file.id, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'smlouva-' + contract.id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
  }
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

watch(selectedSiteHash, () => loadItem());

useHead({ title: pageTitle.value });
onMounted(() => {
  loadDivisions();
  loadSites();
  if (route.params.id !== 'pridat') {
    loadItem();
    loadEmployeeContracts();
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
      slug="employees"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
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
                <BaseFormInput v-model="item.email" label="E-mail" type="email" name="email" />
                <div class="col-span-full flex gap-3 sm:col-span-1">
                  <BaseFormInput
                    v-model="item.phone_prefix"
                    label="Předčíslí"
                    name="phone_prefix"
                    class="w-20 shrink-0"
                  />
                  <BaseFormInput
                    v-model="item.phone"
                    label="Telefon"
                    name="phone"
                    class="min-w-0 flex-1"
                  />
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
                <BaseFormInput
                  v-model="item.personal_id_number"
                  label="Rodné číslo"
                  name="personal_id_number"
                />
                <BaseFormInput
                  v-model="item.id_card_number"
                  label="Číslo OP"
                  name="id_card_number"
                />
                <BaseFormInput v-model="item.nationality" label="Národnost" name="nationality" />
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
                  class="flex size-8 items-center justify-center rounded-lg bg-red-50 text-red-600"
                >
                  <PhoneIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Nouzový kontakt</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <BaseFormInput v-model="item.emergency_contact_name" label="Jméno" name="ec_name" />
                <BaseFormInput
                  v-model="item.emergency_contact_phone"
                  label="Telefon"
                  name="ec_phone"
                />
                <BaseFormInput
                  v-model="item.emergency_contact_relation"
                  label="Vztah"
                  name="ec_relation"
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
            <LayoutContainer v-if="divisions.length > 0" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Divize</LayoutTitle
              >
              <div class="mt-3 space-y-1">
                <label
                  v-for="div in divisions"
                  :key="div.id"
                  class="flex items-center gap-2 rounded-lg p-1.5 text-xs hover:bg-slate-50"
                >
                  <input
                    type="checkbox"
                    :checked="item.divisions?.includes(div.id)"
                    class="rounded text-indigo-600"
                    @change="
                      item.divisions?.includes(div.id)
                        ? (item.divisions = item.divisions.filter((d) => d !== div.id))
                        : item.divisions.push(div.id)
                    "
                  />
                  <span class="size-2 rounded-full" :style="{ backgroundColor: div.color }"></span>
                  {{ div.name }}
                </label>
              </div>
            </LayoutContainer>
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Poznámka</LayoutTitle
              >
              <BaseFormTextarea v-model="item.note" label="" name="note" rows="3" class="mt-2" />
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

      <!-- Pracovní údaje -->
      <template v-if="tabs.find((t) => t.current && t.link === '#prace')">
        <LayoutContainer>
          <div class="mb-6 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
            >
              <BriefcaseIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Pracovní informace</LayoutTitle>
          </div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <BaseFormInput v-model="item.position" label="Pozice" name="position" />
            <BaseFormInput
              v-model="item.employee_number"
              label="Osobní číslo"
              name="employee_number"
            />
            <BaseFormInput
              v-model="item.date_hired"
              label="Datum nástupu"
              type="date"
              name="date_hired"
            />
            <BaseFormInput
              v-model="item.date_terminated"
              label="Datum ukončení"
              type="date"
              name="date_terminated"
            />
            <BaseFormInput
              v-model="item.hourly_rate"
              label="Hodinová sazba"
              type="number"
              name="hourly_rate"
              :step="0.01"
            />
            <BaseFormInput
              v-model="item.monthly_salary"
              label="Měsíční mzda"
              type="number"
              name="monthly_salary"
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

      <!-- Banka a pojištění -->
      <template v-if="tabs.find((t) => t.current && t.link === '#banka')">
        <div class="space-y-8">
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
                name="bank_number"
              />
              <BaseFormInput v-model="item.bank_account_iban" label="IBAN" name="bank_iban" />
              <BaseFormInput
                v-model="item.bank_account_swift"
                label="SWIFT/BIC"
                name="bank_swift"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-pink-50 text-pink-600"
              >
                <HeartIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Zdravotní pojištění</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.health_insurance_company"
                label="Pojišťovna"
                name="insurance_company"
              />
              <BaseFormInput
                v-model="item.health_insurance_number"
                label="Číslo pojištěnce"
                name="insurance_number"
              />
            </div>
          </LayoutContainer>
        </div>
      </template>
      <!-- Soubory tab -->
      <template v-if="tabs.find((t) => t.current && t.link === '#soubory')">
        <LayoutContainer>
          <BaseFileSection
            entity-type="employee"
            :entity-id="route.params.id !== 'pridat' ? route.params.id : null"
            :files="employeeFiles"
            :allow-upload="true"
            @file-uploaded="onFileUploaded"
            @file-deleted="onFileDeleted"
          >
            <template #actions>
              <NuxtLink
                to="/smlouvy/pridat"
                class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-500"
              >
                Nová smlouva
              </NuxtLink>
            </template>
            <template #extra>
              <div v-if="employeeContracts.length" class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-500">Smlouvy</h3>
                <div class="space-y-3">
                  <div
                    v-for="contract in employeeContracts"
                    :key="contract.id"
                    class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                  >
                    <NuxtLink
                      :to="'/smlouvy/' + contract.id"
                      class="flex flex-1 items-center gap-4"
                    >
                      <div
                        class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                      >
                        <DocumentTextIcon class="size-5" />
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <span class="text-sm font-medium text-slate-900">{{
                            contract.title
                          }}</span>
                          <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                            :class="{
                              'bg-emerald-100 text-emerald-700': contract.status === 'active',
                              'bg-slate-100 text-slate-600': contract.status === 'draft',
                              'bg-red-100 text-red-700': contract.status === 'terminated',
                              'bg-amber-100 text-amber-700': contract.status === 'expired',
                            }"
                          >
                            {{
                              {
                                draft: 'Koncept',
                                active: 'Aktivní',
                                terminated: 'Ukončená',
                                expired: 'Vypršelá',
                              }[contract.status] || contract.status
                            }}
                          </span>
                          <span
                            class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-600"
                          >
                            {{
                              {
                                hpp: 'HPP',
                                dpp: 'DPP',
                                dpc: 'DPČ',
                                osvc: 'OSVČ',
                                internship: 'Stáž',
                                nda: 'NDA',
                                other: 'Jiný',
                              }[contract.type] || contract.type
                            }}
                          </span>
                        </div>
                        <p class="text-xs text-slate-400">
                          {{ contract.date_from || '—' }} &mdash;
                          {{ contract.date_to || 'Doba neurčitá' }}
                        </p>
                      </div>
                    </NuxtLink>
                    <button
                      v-if="contract.files?.length"
                      type="button"
                      class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                      @click="downloadContractFile(contract)"
                    >
                      Stáhnout
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </BaseFileSection>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
