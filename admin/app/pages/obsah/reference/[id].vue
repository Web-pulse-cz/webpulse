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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová reference' : 'Detail reference');

const breadcrumbs = ref([
  {
    name: 'Reference',
    link: '/obsah/reference',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/reference/pridat',
    current: true,
  },
]);

const categories = ref([]);

const item = ref({
  id: null as number | null,
  image: null as string | null,
  name: '' as string,
  active: true as boolean,
  translations: {} as object,
  categories: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Slug' as string },
  { field: 'meta_title' as string, label: 'Meta název' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    image: string | null;
    name: string;
    active: boolean;
    translations: object;
    categories: number[];
  }>('/api/admin/review/' + route.params.id, {
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
        link: '/obsah/novinky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst referenci. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadReviewCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/review/category', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      categories.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorie referencí. Zkuste to prosím později.',
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
    image: string | null;
    name: string;
    active: boolean;
    translations: object;
    categories: number[];
  }>(route.params.id === 'pridat' ? '/api/admin/review' : '/api/admin/review/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Reference byla úspěšně vytvořena.'
            : 'Reference byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/reference/' + response.id);
      } else if (redirect) {
        router.push('/obsah/reference');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit referenci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function addRemoveItemCategory(categoryId) {
  if (item.value.categories.includes(categoryId)) {
    item.value.categories = item.value.categories.filter((category) => category !== categoryId);
    return;
  } else {
    item.value.categories.push(categoryId);
  }
}

function fillEmptyTranslations() {
  // Set default translations for all languages
  languageStore.languages.forEach((language) => {
    if (item.value.translations[language.code] === undefined) {
      item.value.translations[language.code] = {};
      translatableAttributes.value.forEach((attribute) => {
        if (item.value.translations[language.code][attribute.field] === undefined) {
          item.value.translations[language.code][attribute.field] = '';
        }
        route.params.id === 'pridat' ? 'Nový projekt' : item.value.name;
      });
    }
  });
}

/* function updateRating(newRating: number) {
  item.value.rating = newRating;
} */

function updateItemImage(files) {
  item.value.image = files[0];
}

function updateItemImages(files) {
  item.value.images = files;
}

onMounted(() => {
  loadReviewCategories();
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
              class="col-span-full"
            />
            <BaseFormUploadImage
              v-model="item.images"
              :multiple="true"
              type="review"
              format="medium"
              label="Galerie obrázků"
              @update-files="updateItemImages"
            />
            <LayoutDivider>Zařazení do kategorií</LayoutDivider>
            <div class="col-span-full grid grid-cols-4 gap-x-4 gap-y-6 pt-6">
              <BaseFormCheckbox
                v-for="(category, key) in categories"
                :key="key"
                :label="category.name"
                :name="category.id"
                :value="item.categories.includes(category.id)"
                :checked="item.categories.includes(category.id)"
                class="col-span-1"
                label-color="grayCustom"
                @change="addRemoveItemCategory(category.id)"
              />
            </div>
            <LayoutDivider>SEO</LayoutDivider>
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
            <BaseFormCheckbox
              v-model="item.active"
              name="active"
              label="Aktivní"
              class="col-span-1 flex-row-reverse justify-between"
              :checked="item.active"
              label-color="grayCustom"
              :reverse="true"
            />
          </div>
          <div class="col-span-1">
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="false"
              type="review"
              format="medium"
              label="Náhledový obrázek"
              @update-files="updateItemImage"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
