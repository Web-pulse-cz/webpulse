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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová kategorie' : 'Detail kategorie');

const breadcrumbs = ref([
  {
    name: 'Reference',
    link: '/obsah/reference',
    current: false,
  },
  {
    name: 'Kategorie',
    link: '/obsah/reference/kategorie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/reference/kategorie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  position: 0 as number,
  name: '' as string,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Text' as string },
  { field: 'meta_title' as string, label: 'Meta název' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    position: number;
    name: string;
    translations: object;
  }>('/api/admin/review/category/' + route.params.id, {
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
        link: '/obsah/reference/kategorie/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst kategorii. Zkuste to prosím později.',
        color: 'red',
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
    position: number;
    name: string;
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/review/category'
      : '/api/admin/review/category/' + route.params.id,
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
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Kategorie byla úspěšně vytvořena.'
            : 'Kategorie byla úspěšně upravena.',
        color: 'green',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/reference/kategorie/' + response.id);
      } else if (redirect) {
        router.push('/obsah/reference/kategorie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit kategorii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="reviews"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
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
              rules="required|min:21"
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
              label="Text"
              name="text"
              rules="required|min:21"
              class="col-span-2"
            />
            <LayoutDivider> SEO </LayoutDivider>
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
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full space-y-5">
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
            <BaseFormInput
              v-model="item.position"
              label="Pořadí ve výpisu"
              type="number"
              name="position"
              class="w-full"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
