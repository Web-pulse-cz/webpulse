<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová potravina' : 'Detail potraviny');

const breadcrumbs = ref([
  {
    name: 'Potraviny',
    link: '/restaurace/potraviny',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/restaurace/potraviny/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  macronutrients: {
    calories: null as number | null,
    proteins: null as number | null,
    carbohydrates: null as number | null,
    fats: null as number | null,
    fiber: null as number | null,
  },
  allergen_ids: [] as number[],
  category_ids: [] as number[],
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

const allAllergens = ref([]);
const allCategories = ref([]);

async function loadAllergens() {
  const client = useSanctumClient();
  await client('/api/admin/food/allergen', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((response) => {
    allAllergens.value = response;
  });
}

async function loadCategories() {
  const client = useSanctumClient();
  await client('/api/admin/food/foodstuff/category', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((response) => {
    allCategories.value = response;
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/food/foodstuff/' + route.params.id, {
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
      item.value.allergen_ids = response.allergens.map((a) => a.id);
      item.value.category_ids = response.categories.map((c) => c.id);
      item.value.macronutrients = response.macronutrients ?? {
        calories: null,
        proteins: null,
        carbohydrates: null,
        fats: null,
        fiber: null,
      };
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/restaurace/potraviny/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst potravinu. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/restaurace/potraviny');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  const payload = {
    ...item.value,
    allergens: item.value.allergen_ids,
    categories: item.value.category_ids,
  };

  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/foodstuff'
      : '/api/admin/food/foodstuff/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(payload),
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
            ? 'Potravina byla úspěšně vytvořena.'
            : 'Potravina byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/restaurace/potraviny/' + response.id);
      } else if (redirect) {
        router.push('/restaurace/potraviny');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit potravinu. Zkontrolujte, že máte vyplněna všechna povinná pole.',
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
  loadAllergens();
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
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="foodstuffs"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-green-50 text-green-600"
                >
                  <CubeIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Obsah</LayoutTitle>
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
                label="Název potraviny"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full lg:col-span-1"
              />

<!--              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                :key="`slug-${selectedLocale}`"
                v-model="item.translations[selectedLocale].slug"
                label="Slug (URL)"
                type="text"
                name="slug"
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
              </div>-->
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
              >
                <ChartBarIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Nutriční hodnoty (na 100 g)</LayoutTitle>
            </div>

            <div class="grid grid-cols-2 gap-x-8 gap-y-6 lg:grid-cols-5">
              <BaseFormInput
                v-model="item.macronutrients.calories"
                label="Energie (kcal)"
                type="number"
                name="calories"
              />
              <BaseFormInput
                v-model="item.macronutrients.proteins"
                label="Bílkoviny (g)"
                type="number"
                name="proteins"
              />
              <BaseFormInput
                v-model="item.macronutrients.carbohydrates"
                label="Sacharidy (g)"
                type="number"
                name="carbohydrates"
              />
              <BaseFormInput
                v-model="item.macronutrients.fats"
                label="Tuky (g)"
                type="number"
                name="fats"
              />
              <BaseFormInput
                v-model="item.macronutrients.fiber"
                label="Vláknina (g)"
                type="number"
                name="fiber"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="allAllergens.length > 0">
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-red-50 text-red-600"
              >
                <ExclamationTriangleIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Alergeny</LayoutTitle>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
              <label
                v-for="allergen in allAllergens"
                :key="allergen.id"
                class="flex cursor-pointer items-center gap-3 rounded-xl p-3 ring-1 ring-inset transition-all"
                :class="
                  item.allergen_ids.includes(allergen.id)
                    ? 'bg-red-50 ring-red-200'
                    : 'bg-white ring-slate-200 hover:ring-slate-300'
                "
              >
                <input
                  v-model="item.allergen_ids"
                  type="checkbox"
                  :value="allergen.id"
                  class="size-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                <span class="text-sm font-medium text-slate-700">
                  {{ allergen.number }}. {{ allergen.name }}
                </span>
              </label>
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="allCategories.length > 0">
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <FolderIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Kategorie</LayoutTitle>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
              <label
                v-for="category in allCategories"
                :key="category.id"
                class="flex cursor-pointer items-center gap-3 rounded-xl p-3 ring-1 ring-inset transition-all"
                :class="
                  item.category_ids.includes(category.id)
                    ? 'bg-indigo-50 ring-indigo-200'
                    : 'bg-white ring-slate-200 hover:ring-slate-300'
                "
              >
                <input
                  v-model="item.category_ids"
                  type="checkbox"
                  :value="category.id"
                  class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-sm font-medium text-slate-700">{{ category.name }}</span>
              </label>
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
