<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  DocumentIcon,
  ExclamationTriangleIcon,
  FolderIcon,
  GlobeAltIcon,
  NewspaperIcon,
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

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Recept', link: '#recept', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové jídlo' : 'Detail jídla');
const breadcrumbs = ref([
  { name: 'Jídla', link: '/restaurace/jidla', current: false },
  { name: pageTitle.value, link: '/restaurace/jidla/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  price: 0 as number,
  weight: '' as string,
  recipe_id: null as number | null,
  recipe: null as any,
  allergen_ids: [] as number[],
  foodstuff_ids: [] as number[],
  category_ids: [] as number[],
  translations: {} as Record<string, any>,
  sites: [] as number[],
});

const allAllergens = ref([]);
const allCategories = ref([]);
const allRecipes = ref([]);
const recipeSearch = ref('');

// Filtered recipes for autocomplete
const filteredRecipes = computed(() => {
  if (!recipeSearch.value) return allRecipes.value.slice(0, 20);
  const q = recipeSearch.value.toLowerCase();
  return allRecipes.value.filter((r: any) => r.name?.toLowerCase().includes(q)).slice(0, 20);
});

const showRecipeDropdown = ref(false);

async function loadAllergens() {
  const client = useSanctumClient();
  await client('/api/admin/food/allergen', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((r) => {
    allAllergens.value = r;
  });
}

async function loadCategories() {
  const client = useSanctumClient();
  await client('/api/admin/food/meal/category', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((r) => {
    allCategories.value = r;
  });
}

async function loadRecipes() {
  const client = useSanctumClient();
  await client('/api/admin/food/recipe', {
    method: 'GET',
    headers: { Accept: 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  }).then((r) => {
    const d = Array.isArray(r) ? r : (r.data ?? []);
    allRecipes.value = d;
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/food/meal/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = r;
      item.value.sites = r.sites.map((s: any) => s.id);
      item.value.allergen_ids = r.allergens.map((a: any) => a.id);
      item.value.foodstuff_ids = r.foodstuffs?.map((f: any) => f.id) || [];
      item.value.category_ids = r.categories.map((c: any) => c.id);
      pageTitle.value = r.name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/restaurace/jidla/' + route.params.id,
        current: true,
      };
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst jídlo.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true) {
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
      ? '/api/admin/food/meal'
      : '/api/admin/food/meal/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(payload),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Jídlo vytvořeno.' : 'Jídlo upraveno.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') router.push('/restaurace/jidla/' + r.id);
      else if (redirect) router.push('/restaurace/jidla');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit jídlo.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

function selectRecipe(recipe: any) {
  item.value.recipe_id = recipe.id;
  item.value.recipe = recipe;
  recipeSearch.value = recipe.name;
  showRecipeDropdown.value = false;
}

function clearRecipe() {
  item.value.recipe_id = null;
  item.value.recipe = null;
  recipeSearch.value = '';
}

function fillEmptyTranslations() {
  languageStore.languages.forEach((lang) => {
    if (!item.value.translations[lang.code]) {
      item.value.translations[lang.code] = {
        name: '',
        slug: '',
        perex: '',
        text: '',
        meta_title: '',
        meta_description: '',
      };
    }
  });
}

const difficultyLabels: Record<string, string> = {
  easy: 'Snadné',
  medium: 'Střední',
  hard: 'Náročné',
};

watchEffect(() => {
  const h = route.hash;
  if (h)
    tabs.value.forEach((t) => {
      t.current = t.link === h;
    });
  else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
});

watch(selectedSiteHash, () => {
  loadItem();
});

useHead({ title: pageTitle.value });
onMounted(() => {
  loadAllergens();
  loadCategories();
  loadRecipes();
  if (route.params.id !== 'pridat') loadItem();
  fillEmptyTranslations();
});
definePageMeta({ middleware: 'sanctum:auth' });
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
    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form ref="formRef" @submit="saveItem">
      <!-- ═══ Tab: Základní údaje ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600"
                >
                  <NewspaperIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.name !== undefined"
                  :key="`name-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].name"
                  label="Název jídla"
                  type="text"
                  name="name"
                  rules="required|min:3"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                  :key="`slug-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].slug"
                  label="Slug (URL)"
                  type="text"
                  name="slug"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.price"
                  label="Cena (Kč)"
                  type="number"
                  name="price"
                  :step="1"
                />
                <BaseFormInput
                  v-model="item.weight"
                  label="Gramáž"
                  type="text"
                  name="weight"
                  placeholder="Např. 200g"
                />
              </div>
            </LayoutContainer>

            <!-- Popis a SEO -->
            <LayoutContainer>
              <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-5">
                <div class="flex items-center gap-3">
                  <div
                    class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                  >
                    <DocumentIcon class="size-5" />
                  </div>
                  <LayoutTitle class="!mb-0">Popis a SEO</LayoutTitle>
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

            <!-- Recept — autocomplete -->
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600"
                >
                  <DocumentIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Přiřazený recept</LayoutTitle>
              </div>

              <div
                v-if="item.recipe_id && item.recipe"
                class="flex items-center justify-between rounded-xl bg-purple-50 p-4 ring-1 ring-purple-200"
              >
                <div>
                  <span class="font-medium text-purple-900">{{ item.recipe.name }}</span>
                  <span v-if="item.recipe.difficulty" class="ml-2 text-xs text-purple-600">{{
                    difficultyLabels[item.recipe.difficulty] || item.recipe.difficulty
                  }}</span>
                  <span v-if="item.recipe.time_to_prepare" class="ml-2 text-xs text-purple-500"
                    >{{ item.recipe.time_to_prepare }} min</span
                  >
                </div>
                <button
                  type="button"
                  class="rounded-lg px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                  @click="clearRecipe"
                >
                  Odebrat
                </button>
              </div>

              <div v-else class="relative">
                <BaseFormInput
                  v-model="recipeSearch"
                  label=""
                  name="recipe_search"
                  placeholder="Vyhledejte recept..."
                  @focus="showRecipeDropdown = true"
                  @input="showRecipeDropdown = true"
                />
                <div
                  v-if="showRecipeDropdown && filteredRecipes.length"
                  class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-y-auto rounded-xl bg-white shadow-lg ring-1 ring-slate-200"
                >
                  <button
                    v-for="recipe in filteredRecipes"
                    :key="recipe.id"
                    type="button"
                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm transition hover:bg-indigo-50"
                    @mousedown.prevent="selectRecipe(recipe)"
                  >
                    <span class="font-medium text-slate-900">{{ recipe.name }}</span>
                    <span class="text-xs text-slate-400">{{
                      difficultyLabels[recipe.difficulty] || ''
                    }}</span>
                  </button>
                </div>
                <div
                  v-if="showRecipeDropdown && !filteredRecipes.length && recipeSearch"
                  class="absolute left-0 right-0 top-full z-20 mt-1 rounded-xl bg-white p-4 text-center text-sm text-slate-400 shadow-lg ring-1 ring-slate-200"
                >
                  Žádný recept nenalezen.
                </div>
              </div>
            </LayoutContainer>

            <!-- Alergeny -->
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
                  <span class="text-sm font-medium text-slate-700"
                    >{{ allergen.number }}. {{ allergen.name }}</span
                  >
                </label>
              </div>
            </LayoutContainer>

            <!-- Kategorie -->
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

          <aside class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
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
      </template>

      <!-- ═══ Tab: Recept ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#recept')">
        <div v-if="!item.recipe_id || !item.recipe" class="py-16 text-center">
          <p class="text-sm text-slate-400">Tomuto jídlu není přiřazen žádný recept.</p>
          <button
            type="button"
            class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
            @click="
              tabs.forEach((t) => (t.current = t.link === '#info'));
              router.push(route.path + '#info');
            "
          >
            Přiřadit recept
          </button>
        </div>

        <div v-else class="space-y-8">
          <LayoutContainer>
            <div class="mb-6 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600"
                >
                  <DocumentIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">{{ item.recipe.name }}</LayoutTitle>
              </div>
              <NuxtLink
                :to="'/restaurace/recepty/' + item.recipe.id"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
              >
                Upravit recept &rarr;
              </NuxtLink>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                <div class="text-lg font-bold text-slate-900">
                  {{ difficultyLabels[item.recipe.difficulty] || '—' }}
                </div>
                <div class="text-xs text-slate-500">Obtížnost</div>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                <div class="text-lg font-bold text-slate-900">
                  {{ item.recipe.time_to_prepare || '—' }} min
                </div>
                <div class="text-xs text-slate-500">Čas přípravy</div>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                <div class="text-lg font-bold text-slate-900">
                  {{ item.recipe.foodstuffs?.length || 0 }}
                </div>
                <div class="text-xs text-slate-500">Ingrediencí</div>
              </div>
              <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                <div class="text-lg font-bold text-slate-900">
                  {{ item.recipe.allergens?.length || 0 }}
                </div>
                <div class="text-xs text-slate-500">Alergenů</div>
              </div>
            </div>
          </LayoutContainer>

          <!-- Ingredience -->
          <LayoutContainer v-if="item.recipe.foodstuffs?.length">
            <LayoutTitle>Ingredience</LayoutTitle>
            <div class="divide-y divide-slate-100">
              <div
                v-for="ing in item.recipe.foodstuffs"
                :key="ing.id"
                class="flex items-center justify-between py-2.5"
              >
                <span class="text-sm font-medium text-slate-700">{{ ing.name }}</span>
                <span class="text-sm text-slate-500">{{ ing.quantity }} {{ ing.unit }}</span>
              </div>
            </div>
          </LayoutContainer>

          <!-- Alergeny receptu -->
          <LayoutContainer v-if="item.recipe.allergens?.length">
            <LayoutTitle>Alergeny z receptu</LayoutTitle>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="a in item.recipe.allergens"
                :key="a.id"
                class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 ring-1 ring-red-200"
              >
                {{ a.number }}. {{ a.name }}
              </span>
            </div>
          </LayoutContainer>

          <!-- Postup -->
          <LayoutContainer v-if="item.recipe.perex || item.recipe.text">
            <LayoutTitle>Postup přípravy</LayoutTitle>
            <div
              v-if="item.recipe.perex"
              class="mb-4 text-sm text-slate-600"
              v-html="item.recipe.perex"
            ></div>
            <div
              v-if="item.recipe.text"
              class="prose prose-sm max-w-none text-slate-700"
              v-html="item.recipe.text"
            ></div>
          </LayoutContainer>
        </div>
      </template>
    </Form>
  </div>
</template>
