<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  CubeIcon,
  ExclamationTriangleIcon,
  FolderIcon,
  GlobeAltIcon,
  NewspaperIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový recept' : 'Detail receptu');

const breadcrumbs = ref([
  {
    name: 'Recepty',
    link: '/restaurace/recepty',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/restaurace/recepty/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  difficulty: 'medium' as string,
  time_to_prepare: null as number | null,
  allergen_ids: [] as number[],
  foodstuffs: [] as { id: number; name: string; quantity: number | null; unit: string }[],
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

const difficultyOptions = [
  { value: 'easy', name: 'Snadné' },
  { value: 'medium', name: 'Střední' },
  { value: 'hard', name: 'Náročné' },
];

const allAllergens = ref([]);
const allFoodstuffs = ref([]);
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

async function loadFoodstuffs() {
  const client = useSanctumClient();
  await client('/api/admin/food/foodstuff', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((response) => {
    allFoodstuffs.value = Array.isArray(response) ? response : (response.data ?? []);
  });
}

async function loadCategories() {
  const client = useSanctumClient();
  await client('/api/admin/food/recipe/category', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((response) => {
    allCategories.value = response;
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/food/recipe/' + route.params.id, {
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
      item.value.foodstuffs = response.foodstuffs;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/restaurace/recepty/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst recept. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/restaurace/recepty');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;

  const payload = {
    ...item.value,
    allergens: item.value.allergen_ids,
    categories: item.value.category_ids,
  };

  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/recipe'
      : '/api/admin/food/recipe/' + route.params.id,
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
            ? 'Recept byl úspěšně vytvořen.'
            : 'Recept byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/restaurace/recepty/' + response.id);
      } else if (redirect) {
        router.push('/restaurace/recepty');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit recept. Zkontrolujte, že máte vyplněna všechna povinná pole.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

const ingredientSearch = ref('');
const showIngredientDropdown = ref(-1);
const creatingFoodstuff = ref(false);

const filteredFoodstuffs = computed(() => {
  if (!ingredientSearch.value) return allFoodstuffs.value.slice(0, 20);
  const q = ingredientSearch.value.toLowerCase();
  return allFoodstuffs.value.filter((f: any) => f.name?.toLowerCase().includes(q)).slice(0, 20);
});

function addFoodstuff() {
  item.value.foodstuffs.push({ id: 0, name: '', quantity: null, unit: '' });
}

function removeFoodstuff(index: number) {
  item.value.foodstuffs.splice(index, 1);
}

function selectFoodstuff(index: number, foodstuff: any) {
  item.value.foodstuffs[index].id = foodstuff.id;
  item.value.foodstuffs[index].name = foodstuff.name;
  ingredientSearch.value = '';
  showIngredientDropdown.value = -1;
}

function openIngredientSearch(index: number) {
  ingredientSearch.value = item.value.foodstuffs[index].name || '';
  showIngredientDropdown.value = index;
}

async function createAndSelectFoodstuff(index: number) {
  if (!ingredientSearch.value.trim()) return;
  creatingFoodstuff.value = true;
  const client = useSanctumClient();
  await client('/api/admin/food/foodstuff', {
    method: 'POST',
    body: JSON.stringify({
      translations: { cs: { name: ingredientSearch.value.trim(), slug: '' } },
      sites: item.value.sites || [],
    }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      allFoodstuffs.value.push(r);
      selectFoodstuff(index, r);
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se vytvořit potravinu.',
        severity: 'error',
      });
    })
    .finally(() => {
      creatingFoodstuff.value = false;
    });
}

function closeDropdowns() {
  showIngredientDropdown.value = -1;
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
  loadFoodstuffs();
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
  <div class="space-y-6 pb-24" @click="closeDropdowns">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="recipes"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600"
                >
                  <NewspaperIcon class="size-5" />
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
                label="Název receptu"
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
                v-model="item.difficulty"
                label="Obtížnost"
                name="difficulty"
                :options="difficultyOptions"
                class="col-span-full lg:col-span-1"
              />

              <BaseFormInput
                v-model="item.time_to_prepare"
                label="Čas přípravy (minuty)"
                type="number"
                name="time_to_prepare"
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
                  label="Postup přípravy"
                  name="text"
                />
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-green-50 text-green-600"
                >
                  <CubeIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Ingredience</LayoutTitle>
              </div>
              <BaseButton type="button" variant="secondary" size="sm" @click="addFoodstuff">
                + Přidat ingredienci
              </BaseButton>
            </div>

            <div
              v-if="item.foodstuffs.length === 0"
              class="py-6 text-center text-sm text-slate-400"
            >
              Zatím žádné ingredience. Klikněte na „Přidat ingredienci".
            </div>

            <div class="space-y-3">
              <div
                v-for="(foodstuff, index) in item.foodstuffs"
                :key="index"
                class="flex items-end gap-4 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
              >
                <div class="relative flex-1">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700">Potravina</label>
                  <input
                    :value="foodstuff.name || ''"
                    type="text"
                    class="block w-full rounded-xl border-0 bg-white py-2.5 pl-4 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                    placeholder="Vyhledejte potravinu..."
                    @focus="openIngredientSearch(index)"
                    @input="
                      (e) => {
                        ingredientSearch = (e.target as HTMLInputElement).value;
                        showIngredientDropdown = index;
                      }
                    "
                  />
                  <div
                    v-if="showIngredientDropdown === index"
                    class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl bg-white shadow-lg ring-1 ring-slate-200"
                  >
                    <button
                      v-for="f in filteredFoodstuffs"
                      :key="f.id"
                      type="button"
                      class="flex w-full items-center px-4 py-2 text-left text-sm transition hover:bg-indigo-50"
                      @mousedown.prevent="selectFoodstuff(index, f)"
                    >
                      {{ f.name }}
                    </button>
                    <div v-if="!filteredFoodstuffs.length && ingredientSearch" class="p-3">
                      <p class="mb-2 text-xs text-slate-400">Potravina nenalezena.</p>
                      <button
                        type="button"
                        class="w-full rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
                        :disabled="creatingFoodstuff"
                        @mousedown.prevent="createAndSelectFoodstuff(index)"
                      >
                        {{ creatingFoodstuff ? 'Vytvářím...' : `Vytvořit "${ingredientSearch}"` }}
                      </button>
                    </div>
                  </div>
                </div>
                <div class="w-28">
                  <BaseFormInput
                    v-model="foodstuff.quantity"
                    label="Množství"
                    type="number"
                    :name="`quantity_${index}`"
                  />
                </div>
                <div class="w-24">
                  <BaseFormInput
                    v-model="foodstuff.unit"
                    label="Jednotka"
                    type="text"
                    :name="`unit_${index}`"
                    placeholder="g, ml, ks"
                  />
                </div>
                <button
                  type="button"
                  class="mb-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-400 ring-1 ring-inset ring-slate-200 transition hover:bg-red-50 hover:text-red-500 hover:ring-red-200"
                  @click="removeFoodstuff(index)"
                >
                  <TrashIcon class="size-4" />
                </button>
              </div>
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
