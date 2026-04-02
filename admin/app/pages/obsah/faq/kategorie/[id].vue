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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová kategorie' : 'Detail kategorie');

const breadcrumbs = ref([
  {
    name: 'FAQ',
    link: '/obsah/faq',
    current: false,
  },
  {
    name: 'Kategorie',
    link: '/obsah/faq/kategorie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/faq/kategorie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  active: true as boolean,
  position: 0 as number,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    position: number;
    active: boolean;
    translations: object;
  }>('/api/admin/faq/category/' + route.params.id, {
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
        link: '/obsah/faq/kategorie/' + route.params.id,
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
      router.push('/obsah/faq/kategorie');
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
    position: number;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/faq/category'
      : '/api/admin/faq/category/' + route.params.id,
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
        router.push('/obsah/faq/kategorie/' + response.id);
      } else if (redirect) {
        router.push('/obsah/faq/kategorie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit kategorii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  <div class="space-y-6 pb-20">
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
                  <FolderIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Identifikace kategorie</LayoutTitle>
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

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-4">
              <div class="col-span-1">
                <BaseFormInput
                  v-model="item.position"
                  label="Pořadí výpisu"
                  name="position"
                  min="0"
                  type="number"
                  placeholder="0"
                />
                <p class="mt-1.5 text-[11px] text-slate-400">Nižší číslo = vyšší pozice.</p>
              </div>

              <div class="col-span-full lg:col-span-3">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.name !== undefined"
                  :key="`name-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].name"
                  label="Název kategorie"
                  type="text"
                  name="name"
                  rules="required|min:3"
                  placeholder="Např. Platby a fakturace"
                />
              </div>

              <div
                class="col-span-full mt-4 grid grid-cols-1 gap-6 rounded-3xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60 lg:grid-cols-2"
              >
                <div class="col-span-full mb-1 flex items-center gap-2">
                  <GlobeAltIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >SEO Optimalizace</span
                  >
                </div>

                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                  :key="`meta_title-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_title"
                  label="Meta název (Titulek)"
                  type="text"
                  name="meta_title"
                  class="col-span-full"
                />

                <BaseFormTextarea
                  v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                  :key="`meta_description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_description"
                  label="Meta popis (Snippet)"
                  name="meta_description"
                  rows="2"
                  class="col-span-full"
                />
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:active="item.active"
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-is-active="true"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
            <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-900">
              Struktura webu
            </h4>
            <p class="mt-2 text-sm leading-relaxed text-indigo-800/80">
              Kategorie slouží k logickému seskupení dotazů. Správné pojmenování pomáhá uživatelům v
              rychlejší navigaci v centru nápovědy.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
