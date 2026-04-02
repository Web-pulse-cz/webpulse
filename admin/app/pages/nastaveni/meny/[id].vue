<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová měna' : 'Detail měny');

const breadcrumbs = ref([
  {
    name: 'Měny',
    link: '/nastaveni/měny',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/meny/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  code: '' as string,
  rate: 0 as number,
  decimals: 0 as number,
  active: true as boolean,
  bank_account_number: '' as string,
  bank_account_name: '' as string,
  bank_account_iban: '' as string,
  bank_account_swift: '' as string,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'symbol_before' as string, label: 'Symbol před' as string },
  { field: 'symbol_after' as string, label: 'Symbol za' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    code: string;
    rate: number;
    decimals: number;
    active: boolean;
    bank_account_number: string;
    bank_account_name: string;
    bank_account_iban: string;
    bank_account_swift: string;
    translations: object;
  }>('/api/admin/currency/' + route.params.id, {
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
        link: '/nastaveni/meny/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst měnu. Zkuste to prosím později.',
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

  await client<{
    id: number | null;
    name: string;
    code: string;
    rate: number;
    decimals: number;
    active: boolean;
    bank_account_number: string;
    bank_account_name: string;
    bank_account_iban: string;
    bank_account_swift: string;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/currency' : '/api/admin/currency/' + route.params.id,
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
            ? 'Měna byla úspěšně vytvořena.'
            : 'Měna byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/meny/' + response.id);
      } else if (redirect) {
        router.push('/nastaveni/meny');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit měnu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function fillEmptyTranslations() {
  // Set default translations for all languages
  languageStore.languages.forEach((language) => {
    if (item.value.translations[language.code] === undefined) {
      item.value.translations[language.code] = {};
      translatableAttributes.value.forEach((attribute) => {
        if (item.value.translations[language.code][attribute.field] === undefined) {
          item.value.translations[language.code][attribute.field] = '';
        }
      });
    }
  });
}

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
  fillEmptyTranslations();
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
      slug="currencies"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <BanknotesIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Nastavení měny a formátu</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyk:</span
                >
                <span
                  class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-tight text-white"
                >
                  {{ selectedLocale }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                v-model="item.translations[selectedLocale].name"
                label="Název měny"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full lg:col-span-2"
                placeholder="Např. Euro"
              />
              <BaseFormInput
                v-model="item.code"
                label="Kód (ISO)"
                type="text"
                name="code"
                rules="required|min:2"
                placeholder="EUR"
              />
              <BaseFormInput
                v-model="item.rate"
                label="Kurz (převod k hlavní)"
                type="number"
                name="rate"
                rules="required"
                :min="0"
                :step="0.000001"
              />

              <div
                class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60 sm:grid-cols-3"
              >
                <BaseFormInput
                  v-model="item.decimals"
                  label="Počet desetinných míst"
                  type="number"
                  name="decimals"
                  rules="required"
                  :min="0"
                />
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.symbol_before !== undefined"
                  v-model="item.translations[selectedLocale].symbol_before"
                  label="Symbol PŘED částkou"
                  type="text"
                  name="symbol_before"
                  placeholder="€"
                />
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.symbol_after !== undefined"
                  v-model="item.translations[selectedLocale].symbol_after"
                  label="Symbol ZA částkou"
                  type="text"
                  name="symbol_after"
                  placeholder="Kč"
                />
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
              >
                <CreditCardIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Bankovní spojení pro tuto měnu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.bank_account_number"
                label="Číslo účtu"
                type="text"
                name="bank_account_number"
                placeholder="123456789/0100"
              />
              <BaseFormInput
                v-model="item.bank_account_name"
                label="Název účtu (Majitel)"
                type="text"
                name="bank_account_name"
                placeholder="Barbershop s.r.o."
              />
              <BaseFormInput
                v-model="item.bank_account_iban"
                label="IBAN"
                type="text"
                name="bank_account_iban"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.bank_account_swift"
                label="SWIFT / BIC"
                type="text"
                name="bank_account_swift"
                class="col-span-1"
              />
            </div>
            <p class="mt-6 text-xs italic text-slate-400">
              Tyto údaje se automaticky propíší na faktury vystavené v této měně.
            </p>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400"
              >Lokalizace a stav</LayoutTitle
            >

            <div class="mt-6 space-y-6">
              <BaseFormSelect
                v-model="selectedLocale"
                label="Upravit v jazyce"
                name="locale"
                class="w-full"
                :options="languageStore.languageOptions"
              />

              <div
                class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition-all hover:bg-white hover:shadow-sm"
              >
                <BaseFormCheckbox
                  v-model="item.active"
                  name="active"
                  label="Měna je aktivní"
                  class="flex-row-reverse justify-between font-bold text-slate-700"
                  :checked="item.active"
                  :reverse="true"
                />
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-emerald-50 p-6 ring-1 ring-inset ring-emerald-100">
            <div class="flex items-start gap-4">
              <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-emerald-600" />
              <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-emerald-900">
                  Automatizace
                </h4>
                <p class="mt-2 text-sm leading-relaxed text-emerald-800/80">
                  Pokud měnu deaktivujete, nebude ji možné vybrat v nastavení projektů ani při
                  tvorbě nových faktur.
                </p>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
