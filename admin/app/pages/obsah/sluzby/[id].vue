<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';
import { useLanguageStore } from '~~/stores/languageStore';

const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const { $toast } = useNuxtApp();

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
    },
  })
    .then((response) => {
      item.value = response;
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

function updateItemImage(files) {
  item.value.image = files[0];
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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="services"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.price"
              label="Cena"
              type="number"
              name="price"
              rules="required|min:1"
              class="col-span-1"
            />
            <BaseFormSelect
              v-model="item.type"
              label="Typ služby"
              name="type"
              class="col-span-1"
              :options="[
                { value: 'service', name: 'Služba' },
                { value: 'product', name: 'Produkt' },
              ]"
            />
            <BaseFormSelect
              v-model="item.price_type"
              label="Typ ceny"
              name="price_type"
              class="col-span-1"
              :options="[
                { value: 'total', name: 'Celkem za službu' },
                { value: 'hourly', name: 'Cena za hodinu' },
              ]"
            />
            <br />
            <BaseFormSelect
              v-model="item.tax_rate_id"
              label="DPH"
              name="tax_rate_id"
              class="col-span-1"
              :options="taxRateStore.taxRateOptions"
            />
            <BaseFormSelect
              v-model="item.currency_id"
              label="Měna"
              name="currency_id"
              class="col-span-1"
              :options="currencyStore.currenciesOptions"
            />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].name !== undefined
              "
              :key="`name-${selectedLocale}`"
              v-model="item.translations[selectedLocale].name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].meta_title !== undefined
              "
              :key="`meta_title-${selectedLocale}`"
              v-model="item.translations[selectedLocale].meta_title"
              label="Meta název"
              type="text"
              name="meta_title"
              class="col-span-1"
            />
            <br />
            <BaseFormTextarea
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].meta_description !== undefined
              "
              :key="`meta_description-${selectedLocale}`"
              v-model="item.translations[selectedLocale].meta_description"
              label="Meta popis"
              name="meta_description"
              class="col-span-full"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].perex !== undefined
              "
              :key="`perex-${selectedLocale}`"
              v-model="item.translations[selectedLocale].perex"
              label="Perex"
              name="perex"
              class="col-span-2"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].description !== undefined
              "
              :key="`description-${selectedLocale}`"
              v-model="item.translations[selectedLocale].description"
              label="Popis"
              name="description"
              class="col-span-2"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full space-y-6">
          <div class="col-span-1">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
          <div class="col-span-1">
            <BaseFormCheckbox
              v-model="item.active"
              name="active"
              label="Aktivní"
              class="col-span-1 mt-4 flex-row-reverse justify-between"
              :checked="item.active"
              label-color="grayCustom"
              :reverse="true"
            />
          </div>
          <div class="col-span-1">
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="false"
              type="service"
              format="small"
              label="Náhledový obrázek"
              @update-files="updateItemImage"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
