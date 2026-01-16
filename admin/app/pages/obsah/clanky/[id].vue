<script setup lang="ts">
import {inject, ref} from 'vue';
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

function updateItemImage(files) {
  item.value.image = files[0];
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

function addRemoveItemSite(siteId) {
  if (item.value.sites.includes(siteId)) {
    item.value.sites = item.value.sites.filter((site) => site !== siteId);
    return;
  } else {
    item.value.sites.push(siteId);
  }
}

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
      slug="posts"
      @save="saveItem"
    />
    <div>
      <div class="mt-5 block">
        <nav class="isolate flex divide-x divide-gray-200 shadow-sm" aria-label="Tabs">
          <NuxtLink
            v-for="(tab, index) in tabs"
            :key="index"
            :to="tab.link"
            class="group relative min-w-0 flex-1 overflow-hidden bg-white px-2 py-2.5 text-center text-xs font-medium text-grayCustom hover:bg-gray-50 hover:text-grayDark focus:z-10 lg:px-4 lg:py-4 lg:text-sm"
          >
            <span>{{ tab.name }}</span>
            <span
              aria-hidden="true"
              :class="
                tab.current
                  ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                  : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
              "
            />
          </NuxtLink>
        </nav>
      </div>
    </div>
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-7">
          <LayoutContainer class="col-span-5 w-full">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormSelect
                v-model="item.status"
                label="Stav"
                name="priority"
                class="col-span-1"
                :options="[
                  { value: 'draft', name: 'Koncept' },
                  { value: 'published', name: 'Publikováno' },
                  { value: 'archived', name: 'Archivováno' },
                ]"
              />
              <br />
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
          <LayoutContainer class="col-span-2 w-full space-y-6">
            <div class="col-span-1">
              <BaseFormUploadImage
                v-model="item.image"
                :multiple="false"
                type="post"
                format="medium"
                label="Náhledový obrázek"
                @update-files="updateItemImage"
              />
            </div>
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#obsah')">
        <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-9">
          <LayoutContainer class="col-span-7 w-full">
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
          <LayoutContainer class="col-span-2 w-full">
            <div class="col-span-1">
              <BaseFormSelect
                v-model="selectedLocale"
                label="Jazyk"
                name="locale"
                class="w-full"
                :options="languageStore.languageOptions"
              />
            </div>
            <LayoutDivider v-if="user && user.sites">Zařazení do stránek</LayoutDivider>
            <BaseFormCheckbox
              v-for="(site, key) in user.sites"
              v-if="item.sites && user.sites"
              :key="key"
              :label="site.name"
              :name="site.id"
              :value="item.sites.includes(site.id)"
              :checked="item.sites.includes(site.id)"
              class="col-span-full"
              :reverse="true"
              label-color="grayCustom"
              @change="addRemoveItemSite(site.id)"
            />
          </LayoutContainer>
        </div>
      </template>
    </Form>
  </div>
</template>
