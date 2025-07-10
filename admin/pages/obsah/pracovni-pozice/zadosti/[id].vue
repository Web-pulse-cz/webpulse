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

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová pracovní pozice' : 'Detail pracovní pozice',
);

const breadcrumbs = ref([
  {
    name: 'Pracovní pozice',
    link: '/obsah/pracovni-pozice',
    current: false,
  },
  {
    name: 'Nová pracovní pozice',
    link: '/obsah/pracovni-pozice/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  position: 0 as number,
  type: 'full-time' as string,
  status: 'open' as string,
  salary_from: null as number | null,
  salary_to: null as number | null,
  salary_type: 'hourly' as string,
  start_date: null as string | null,
  name: '' as string,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
  { field: 'location' as string, label: 'Lokace' as string },
  { field: 'benefits' as string, label: 'Benefity' as string },
  { field: 'requirements' as string, label: 'Požadavky' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: null | number;
    position: number;
    type: string;
    status: string;
    salary_from: number | null;
    salary_to: number | null;
    salary_type: string;
    start_date: string | null;
    name: string;
    translations: object;
  }>('/api/admin/career/application/' + route.params.id, {
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
        link: '/obsah/pracovni-pozice/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst pracovní pozici. Zkuste to prosím později.',
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
    id: null | number;
    position: number;
    type: string;
    status: string;
    salary_from: number | null;
    salary_to: number | null;
    salary_type: string;
    start_date: string | null;
    name: string;
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/career/application'
      : '/api/admin/career/application/' + route.params.id,
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
            ? 'Pracovní pozice byla úspěšně vytvořena.'
            : 'Pracovní pozice byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/obsah/pracovni-pozice');
      languageStore.fetchLanguages();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit pracovní pozici. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      :title="route.params.id === 'pridat' ? 'Nová pracovní pozice' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="careers"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-4 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
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
                item.translations[selectedLocale].text !== undefined
              "
              :key="`text-${selectedLocale}`"
              v-model="item.translations[selectedLocale].text"
              label="Popis"
              name="text"
              class="col-span-2"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-3 w-full">
          <div class="col-span-1 border-b py-6">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
