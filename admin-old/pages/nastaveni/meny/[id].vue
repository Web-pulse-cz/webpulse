<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

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
    name: 'Nová měna',
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
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/nastaveni/meny/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst měnu. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem() {
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
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Měna byla úspěšně vytvořena.'
            : 'Měna byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/nastaveni/meny');
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit měnu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
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
  <div>
    <LayoutHeader
      :title="route.params.id === 'pridat' ? 'Nová měna' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="currencies"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4 border-b pb-6">
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].name !== undefined
              "
              v-model="item.translations[selectedLocale].name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.code"
              label="Kód"
              type="text"
              name="code"
              rules="required|min:2"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.rate"
              label="Převod"
              type="number"
              name="rate"
              rules="required"
              class="col-span-1"
              :min="0"
              :step="0.000001"
            />
            <BaseFormInput
              v-model="item.decimals"
              label="Počet desetinných míst"
              type="number"
              name="decimals"
              rules="required"
              class="col-span-1"
              :min="0"
            />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].symbol_before !== undefined
              "
              v-model="item.translations[selectedLocale].symbol_before"
              label="Symbol před"
              type="text"
              name="symbol_before"
              rules="required|min:1"
              class="col-span-1"
            />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].symbol_after !== undefined
              "
              v-model="item.translations[selectedLocale].symbol_after"
              label="Symbol za"
              type="text"
              name="symbol_after"
              rules="required|min:1"
              class="col-span-1"
            />
          </div>
          <div class="grid grid-cols-2 gap-x-8 gap-y-4 pt-6">
            <BaseFormInput
              v-model="item.bank_account_number"
              label="Číslo bankovního účtu"
              type="text"
              name="bank_account_number"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.bank_account_name"
              label="Jméno majitele bankovního účtu"
              type="text"
              name="bank_account_name"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.bank_account_iban"
              label="IBAN bankovního účtu"
              type="text"
              name="bank_account_iban"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.bank_account_swift"
              label="SWIFT bankovního účtu"
              type="text"
              name="bank_account_swift"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full">
          <div class="col-span-1 border-b pb-6">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
          <BaseFormCheckbox
            v-model="item.active"
            name="active"
            label="Aktivní"
            class="col-span-1 mt-4 flex-row-reverse justify-between"
            :checked="item.active"
            label-color="grayCustom"
            :reverse="true"
          />
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
