<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { FolderIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová kategorie' : 'Detail kategorie');

const breadcrumbs = ref([
  {
    name: 'Jídla',
    link: '/restaurace/jidla',
    current: false,
  },
  {
    name: 'Kategorie',
    link: '/restaurace/jidla/kategorie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/restaurace/jidla/kategorie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  meal_category_id: null as number | null,
  translations: {} as object,
  sites: [] as number[],
});

const translatableAttributes = ref([
  { field: 'name', label: 'Název' },
  { field: 'slug', label: 'Slug' },
  { field: 'perex', label: 'Perex' },
  { field: 'text', label: 'Popis' },
  { field: 'meta_title', label: 'Meta title' },
  { field: 'meta_description', label: 'Meta popis' },
]);

const allParentCategories = ref([]);

async function loadParentCategories() {
  const client = useSanctumClient();
  await client('/api/admin/food/meal/category', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((response) => {
    allParentCategories.value = response.filter((c) => c.id !== Number(route.params.id));
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/food/meal/category/' + route.params.id, {
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
        link: '/restaurace/jidla/kategorie/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorii. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/restaurace/jidla/kategorie');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/meal/category'
      : '/api/admin/food/meal/category/' + route.params.id,
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
            ? 'Kategorie byla úspěšně vytvořena.'
            : 'Kategorie byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/restaurace/jidla/kategorie/' + response.id);
      } else if (redirect) {
        router.push('/restaurace/jidla/kategorie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit kategorii. Zkontrolujte, že máte vyplněna všechna povinná pole.',
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
  loadParentCategories();
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
      slug="meals"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <FolderIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Obsah kategorie</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyk:</span
                >
                <span
                  class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-tight text-white"
                >
                  {{ selectedLocale }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                :key="`name-${selectedLocale}`"
                v-model="item.translations[selectedLocale].name"
                label="Název kategorie"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full lg:col-span-1"
              />

              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                :key="`slug-${selectedLocale}`"
                v-model="item.translations[selectedLocale].slug"
                label="Slug (URL)"
                type="text"
                name="slug"
                class="col-span-full lg:col-span-1"
              />

              <BaseFormSelect
                v-if="allParentCategories.length > 0"
                v-model="item.meal_category_id"
                label="Nadřazená kategorie"
                name="meal_category_id"
                :options="[
                  { value: null, name: '— bez nadřazené —' },
                  ...allParentCategories.map((c) => ({ value: c.id, name: c.name })),
                ]"
                class="col-span-full lg:col-span-1"
              />

              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                :key="`meta_title-${selectedLocale}`"
                v-model="item.translations[selectedLocale].meta_title"
                label="Meta název"
                type="text"
                name="meta_title"
                class="col-span-full lg:col-span-1"
              />

              <div
                class="col-span-full rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60"
              >
                <div class="mb-4 flex items-center gap-2">
                  <GlobeAltIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >SEO Optimalizace</span
                  >
                </div>
                <BaseFormTextarea
                  v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                  :key="`meta_description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_description"
                  label="Meta popisek"
                  name="meta_description"
                  rows="2"
                  class="bg-white"
                />
              </div>

              <div class="col-span-full space-y-10 pt-4">
                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.perex !== undefined"
                  :key="`perex-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].perex"
                  label="Perex"
                  name="perex"
                />
                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.text !== undefined"
                  :key="`text-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].text"
                  label="Popis"
                  name="text"
                />
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-is-active="false"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
