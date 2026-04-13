<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';
import { LightBulbIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový FAQ dotaz' : 'Detail FAQ dotazu');

const breadcrumbs = ref([
  {
    name: 'FAQ',
    link: '/obsah/faq',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/faq/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  question: '' as string,
  answer: 0 as number,
  position: 0 as number,
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
  categories: [] as number[],
});

const translatableAttributes = ref([
  { field: 'question' as string, label: 'Dotaz' as string },
  { field: 'answer' as string, label: 'Odpověď' as string },
]);

const categories = ref([]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    question: string;
    answer: string;
    position: number;
    active: boolean;
    translations: object;
    categories: number[];
  }>('/api/admin/faq/' + route.params.id, {
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
      pageTitle.value = item.value.question;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/faq/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst dotaz. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/faq');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/faq/category', {
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
    question: string;
    answer: string;
    position: number;
    active: boolean;
    translations: object;
    categories: number[];
  }>(route.params.id === 'pridat' ? '/api/admin/faq' : '/api/admin/faq/' + route.params.id, {
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
            ? 'Dotaz byl úspěšně vytvořen.'
            : 'Dotaz byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/faq/' + response.id);
      } else if (redirect) {
        router.push('/obsah/faq');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit dotaz. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addRemoveItemCategory(categoryId) {
  if (item.value.categories.includes(categoryId)) {
    item.value.categories = item.value.categories.filter((category) => category !== categoryId);
    return;
  } else {
    item.value.categories.push(categoryId);
  }
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

watch(selectedSiteHash, () => {
  loadItem();
  loadCategories();
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
    loadCategories();
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
      slug="faqs"
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
                  <QuestionMarkCircleIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Obsah dotazu</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyk:</span
                >
                <span
                  class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase text-white"
                  >{{ selectedLocale }}</span
                >
              </div>
            </div>

            <div class="grid grid-cols-1 gap-y-6">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.question !== undefined"
                :key="`question-${selectedLocale}`"
                v-model="item.translations[selectedLocale].question"
                label="Znění otázky / dotazu"
                type="text"
                name="question"
                rules="required|min:3"
                placeholder="Např. Jaká je doba dodání?"
                class="col-span-full"
              />

              <div class="col-span-full pt-2">
                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.answer !== undefined"
                  :key="`answer-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].answer"
                  label="Podrobná odpověď"
                  name="answer"
                />
              </div>
            </div>

            <div v-if="categories.length > 0" class="mt-12">
              <LayoutDivider>Zařazení do tématických kategorií</LayoutDivider>

              <div class="mt-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  <div
                    v-for="(category, key) in categories"
                    :key="key"
                    class="flex items-center rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 transition-all hover:ring-indigo-300"
                  >
                    <BaseFormCheckbox
                      :label="category.name"
                      :name="String(category.id)"
                      :value="item.categories.includes(category.id)"
                      :checked="item.categories.includes(category.id)"
                      class="w-full flex-row-reverse justify-between font-medium text-slate-700"
                      @change="addRemoveItemCategory(category.id)"
                    />
                  </div>
                </div>
                <p v-if="!categories.length" class="text-center text-sm italic text-slate-400">
                  Nejsou definovány žádné kategorie pro FAQ.
                </p>
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:position="item.position"
            v-model:active="item.active"
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-position="true"
            :allow-is-active="true"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-4 text-indigo-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-900">
                Dobrá praxe
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-indigo-800/80">
              Stručné a jasné otázky pomáhají zákazníkům rychleji najít to, co hledají. Odpověď se
              snažte formátovat pomocí odrážek pro lepší čitelnost.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
