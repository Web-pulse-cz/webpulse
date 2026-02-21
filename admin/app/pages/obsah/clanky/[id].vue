<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje a zařazení', link: '#info', current: false },
  { name: 'Obsah článku', link: '#obsah', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový článek' : 'Detail článku');

const breadcrumbs = ref([
  {
    name: 'Blogové články',
    link: '/obsah/clanky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/clanky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  status: 'draft' as string,
  published_from: null as string | null,
  published_to: null as string | null,
  image: '' as string,
  translations: {} as object,
  categories: [] as number[],
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

const categories = ref([]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    status: string;
    published_from: string | null;
    published_to: string | null;
    image: string;
    translations: object;
    categories: number[];
  }>('/api/admin/post/' + route.params.id, {
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
        link: '/obsah/clanky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
      item.value.published_to = item.value.published_to
        ? new Date(item.value.published_to).toISOString().slice(0, 16)
        : null;
      item.value.published_from = item.value.published_from
        ? new Date(item.value.published_from).toISOString().slice(0, 16)
        : null;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst článek. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/clanky');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/post/category', {
    method: 'GET',
    query: {
      orderBy: 'position',
      orderWay: 'asc',
    },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      categories.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorie. Zkuste to prosím později.',
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
    status: string;
    published_from: string | null;
    published_to: string | null;
    image: string;
    translations: object;
    categories: number[];
  }>(route.params.id === 'pridat' ? '/api/admin/post' : '/api/admin/post/' + route.params.id, {
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
            ? 'Článek byl úspěšně vytvořen.'
            : 'Článek byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/clanky/' + response.id);
      } else if (redirect) {
        router.push('/obsah/clanky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit článek. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadItem();
  loadCategories();
});

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
      });
    }
  });
}

watchEffect(() => {
  const routeTabHash = route.hash;
  if (routeTabHash && routeTabHash !== '') {
    tabs.value.forEach((tab) => {
      tab.current = tab.link === routeTabHash;
    });
  } else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
});

watch(selectedSiteHash, () => {
  loadItem();
  loadCategories();
});

onMounted(() => {
  loadCategories();
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
      :modify-bottom="false"
      slug="posts"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" />
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
          <LayoutContainer class="col-span-9 w-full">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.published_from"
                label="Publikovat od"
                type="datetime-local"
                name="published_from"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.published_to"
                label="Publikovat do"
                type="datetime-local"
                name="published_to"
                class="col-span-1"
              />
              <LayoutDivider>Zaření do kategorií</LayoutDivider>
              <div class="col-span-full grid grid-cols-4 gap-x-4 gap-y-6">
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
            </div>
          </LayoutContainer>
          <LayoutActionsDetailBlock
            v-model:image="item.image"
            v-model:state="item.status"
            v-model:sites="item.sites"
            :allow-translations="false"
            :allow-state="true"
            :states="[
              { value: 'draft', name: 'Koncept' },
              { value: 'published', name: 'Publikováno' },
              { value: 'archived', name: 'Archivováno' },
            ]"
            class="col-span-3"
          />
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#obsah')">
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
          <LayoutContainer class="col-span-9 w-full">
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
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            :allow-image="false"
            :allow-sites="false"
            class="col-span-3"
          />
        </div>
      </template>
    </Form>
  </div>
</template>
