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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová kategorie služeb' : 'Detail služby');

const breadcrumbs = ref([
  {
    name: 'Služby',
    link: '/obsah/sluzby',
    current: false,
  },
  {
    name: 'Kategorie služeb',
    link: '/obsah/sluzby/kategorie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/sluzby/kategorie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  position: 0 as number,
  name: '' as string,
  image_icon: '' as string,
  image_hero: '' as string,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
  { field: 'hero_text' as string, label: 'Popis hero' as string },
  { field: 'content_title' as string, label: 'Nadpis hlavní části' as string },
  { field: 'content_text' as string, label: 'Text hlavní části' as string },
  { field: 'json' as string, label: 'Dlaždice' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    position: number;
    image_icon: string;
    image_hero: string;
    translations: object;
  }>('/api/admin/service/category/' + route.params.id, {
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
        link: '/obsah/sluzby/kategorie/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorii služeb. Zkuste to prosím později.',
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
    position: number;
    image_icon: string;
    image_hero: string;
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/service/category'
      : '/api/admin/service/category/' + route.params.id,
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
            ? 'Kategorii služeb byla úspěšně vytvořena.'
            : 'Kategorii služeb byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/sluzby/kategorie/' + response.id);
      } else if (redirect) {
        router.push('/obsah/sluzby/kategorie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit kategorii služeb. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addTile() {
  console.log(item.value.translations);
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
        if (
          item.value.translations[language.code][attribute.field] === undefined &&
          attribute.field !== 'json'
        ) {
          item.value.translations[language.code][attribute.field] = '';
        } else if (
          item.value.translations[language.code][attribute.field] === undefined &&
          attribute.field === 'json'
        ) {
          item.value.translations[language.code][attribute.field] = [
            { title: '', perex: '' },
            { title: '', perex: '' },
            { title: '', perex: '' },
            { title: '', perex: '' },
          ];
          console.log(item.value.translations[language.code][attribute.field]);
        }
      });
    }
  });
}

function updateItemImageIcon(files) {
  item.value.image_icon = files[0];
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
              :maxlength="255"
              class="col-span-2"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].hero_text !== undefined
              "
              :key="`hero_text-${selectedLocale}`"
              v-model="item.translations[selectedLocale].hero_text"
              label="Text v hero sekci"
              name="hero_text"
              class="col-span-2"
            />
            <LayoutDivider>Hlavní obsah</LayoutDivider>
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].content_title !== undefined
              "
              :key="`content_title-${selectedLocale}`"
              v-model="item.translations[selectedLocale].content_title"
              label="Nadpis hlavní části"
              type="text"
              name="content_title"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].content_text !== undefined
              "
              :key="`content_text-${selectedLocale}`"
              v-model="item.translations[selectedLocale].content_text"
              label="Text hlavní části"
              name="content_text"
              class="col-span-2"
            />
            <pre
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].json !== undefined
              "
              >{{ item.translations[selectedLocale].json }}</pre
            >
            <LayoutDivider>Dlaždice</LayoutDivider>
            <div
              v-for="(tile, index) in item.translations[selectedLocale].json"
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].json !== undefined
              "
              :key="index"
              class="col-span-full grid grid-cols-3 gap-x-8 gap-y-4"
            >
              <BaseFormInput
                v-model="tile.title"
                label="Název dlaždice"
                type="text"
                :name="`tile_title-${index}`"
                rules="required|min:3"
                class="col-span-1"
              />
              <BaseFormTextarea
                v-model="tile.perex"
                label="Perex dlaždice"
                :name="`tile_perex-${index}`"
                rules="required|min:3"
                class="col-span-2"
              />
            </div>
            <div class="col-span-full text-center">
              <BaseButton variant="primary" size="lg" @click="addTile">Přidat dlaždici</BaseButton>
            </div>
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
            <BaseFormInput
              v-model="item.position"
              label="Pořadí ve výpisu"
              type="number"
              name="position"
              rules="required|min:1"
              class="col-span-1"
            />
          </div>
          <div class="col-span-1">
            <BaseFormUploadImage
              v-model="item.image_icon"
              :multiple="false"
              type="service"
              format="small"
              label="Obrázek ikony"
              @update-files="updateItemImageIcon"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
