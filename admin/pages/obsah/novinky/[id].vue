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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová novinka' : 'Detail novinky');

const breadcrumbs = ref([
  {
    name: 'Novinky',
    link: '/obsah/novinky',
    current: false,
  },
  {
    name: 'Nová novinka',
    link: '/obsah/novinky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  priority: 1 as number,
  image: '' as string,
  active: true as boolean,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    priority: number;
    image: string;
    active: boolean;
    translations: object;
  }>('/api/admin/novelty/' + route.params.id, {
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
        link: '/obsah/novinky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst novinku. Zkuste to prosím později.',
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
    priority: number;
    image: string;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/novelty' : '/api/admin/novelty/' + route.params.id,
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
            ? 'Novinka byla úspěšně vytvořena.'
            : 'Novinka byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/obsah/novinky');
      languageStore.fetchLanguages();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit novinku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      :title="route.params.id === 'pridat' ? 'Nová novinka' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="novelties"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-4 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormSelect
              v-model="item.priority"
              label="Priorita"
              name="priority"
              class="col-span-1"
              :options="[
                { value: 1, name: 'Vysoká' },
                { value: 2, name: 'Normální' },
                { value: 3, name: 'Nízká' },
              ]"
            />
            <br />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].name !== undefined
              "
              v-model="item.translations[selectedLocale].name"
              label="Název"
              type="text"
              :key="`name-${selectedLocale}`"
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
              v-model="item.translations[selectedLocale].meta_title"
              label="Meta název"
              type="text"
              :key="`meta_title-${selectedLocale}`"
              name="meta_title"
              class="col-span-1"
            />
            <BaseFormTextarea
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].meta_description !== undefined
              "
              v-model="item.translations[selectedLocale].meta_description"
              label="Meta popis"
              :key="`meta_description-${selectedLocale}`"
              name="meta_description"
              class="col-span-full"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].perex !== undefined
              "
              v-model="item.translations[selectedLocale].perex"
              label="Perex"
              :key="`perex-${selectedLocale}`"
              name="perex"
              class="col-span-2"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].text !== undefined
              "
              v-model="item.translations[selectedLocale].text"
              label="Popis"
              name="text"
              :key="`text-${selectedLocale}`"
              class="col-span-2"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-3 w-full">
          <div class="col-span-1 border-b pb-6">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
          <div class="col-span-1 border-b pb-6">
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
          <div class="col-span-1 border-b pb-6">
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="false"
              type="novelty"
              format="medium"
              label="Náhledový obrázek"
              class="pt-6"
              @update-files="updateItemImage"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
