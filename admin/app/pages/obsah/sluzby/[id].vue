<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';
import { useLanguageStore } from '~~/stores/languageStore';

const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová služby' : 'Detail služby');

const breadcrumbs = ref([
  {
    name: 'Služby',
    link: '/obsah/sluzby',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/sluzby/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  type: 'service' as string,
  price_type: 'hourly' as string,
  price: 0 as number,
  tax_rate_id: 5 as number,
  currency_id: 1 as number,
  image: '' as string,
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'description' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    type: string;
    price_type: string;
    price: number;
    tax_rate_id: number;
    currency_id: number;
    image: string;
    active: boolean;
    translations: object;
  }>('/api/admin/service/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = response.sites.map((site) => site.id);
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/sluzby/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst službu. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/sluzby');
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
    type: string;
    price_type: string;
    price: number;
    tax_rate_id: number;
    currency_id: number;
    image: string;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/service' : '/api/admin/service/' + route.params.id,
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
            ? 'Služba byla úspěšně vytvořena.'
            : 'Služba byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/sluzby/' + response.id);
      } else if (redirect) {
        router.push('/obsah/sluzby');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit službu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadItem();
});

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
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="services"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <BanknotesIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Nastavení ceny a typu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-6">
              <BaseFormSelect
                v-model="item.type"
                label="Typ položky"
                name="type"
                class="col-span-full sm:col-span-3 lg:col-span-2"
                :options="[
                  { value: 'service', name: 'Služba' },
                  { value: 'product', name: 'Produkt' },
                ]"
              />
              <BaseFormSelect
                v-model="item.price_type"
                label="Způsob účtování"
                name="price_type"
                class="col-span-full sm:col-span-3 lg:col-span-2"
                :options="[
                  { value: 'total', name: 'Fixní cena za kus/akci' },
                  { value: 'hourly', name: 'Hodinová sazba' },
                ]"
              />

              <div class="hidden lg:col-span-2 lg:block"></div>
              <BaseFormInput
                v-model="item.price"
                label="Základní cena (bez DPH)"
                type="number"
                name="price"
                rules="required|min:1"
                class="col-span-full sm:col-span-2 lg:col-span-2"
              />
              <BaseFormSelect
                v-model="item.currency_id"
                label="Měna"
                name="currency_id"
                class="col-span-full sm:col-span-2 lg:col-span-2"
                :options="currencyStore.currenciesOptions"
              />
              <BaseFormSelect
                v-model="item.tax_rate_id"
                label="Sazba DPH"
                name="tax_rate_id"
                class="col-span-full sm:col-span-2 lg:col-span-2"
                :options="taxRateStore.taxRateOptions"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <IdentificationIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0"
                  >Popis a marketing ({{ selectedLocale.toUpperCase() }})</LayoutTitle
                >
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                :key="`name-${selectedLocale}`"
                v-model="item.translations[selectedLocale].name"
                label="Název služby / produktu"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full"
                placeholder="Zadejte název, který uvidí zákazník..."
              />

              <div
                class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200 lg:grid-cols-2"
              >
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                  :key="`meta_title-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_title"
                  label="SEO Titulek (Meta Title)"
                  type="text"
                  name="meta_title"
                  class="col-span-full"
                />
                <BaseFormTextarea
                  v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                  :key="`meta_description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_description"
                  label="SEO Popis (Meta Description)"
                  name="meta_description"
                  rows="2"
                  class="col-span-full"
                />
              </div>

              <div class="col-span-full space-y-10 pt-4">
                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.perex !== undefined"
                  :key="`perex-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].perex"
                  label="Krátká upoutávka (Perex)"
                  name="perex"
                />

                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.description !== undefined"
                  :key="`description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].description"
                  label="Podrobný popis služby"
                  name="description"
                />
              </div>
            </div>
          </LayoutContainer>
        </div>

        <div class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:sites="item.sites"
            v-model:image="item.image"
            v-model:active="item.active"
            :allow-is-active="true"
            image-type="service"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-emerald-50 p-6 ring-1 ring-inset ring-emerald-100">
            <div class="mb-3 flex items-center gap-2">
              <InformationCircleIcon class="size-4 text-emerald-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-emerald-900">
                Dobrá rada
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-emerald-800/80">
              Ujistěte se, že <strong>Perex</strong> je dostatečně úderný. Je to první věc, kterou
              zákazník uvidí v ceníku nebo v náhledu služby.
            </p>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
