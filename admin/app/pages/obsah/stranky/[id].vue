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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová stránka' : 'Detail stránky');

const breadcrumbs = ref([
  {
    name: 'Informační stránky',
    link: '/obsah/stranky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/stranky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  active: true as boolean,
  name: '' as string,
  translations: {} as object,
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

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    active: boolean;
    name: string;
    translations: object;
  }>('/api/admin/page/' + route.params.id, {
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
        link: '/obsah/stranky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stránku. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/stranky');
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
  }>(route.params.id === 'pridat' ? '/api/admin/page' : '/api/admin/page/' + route.params.id, {
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
            ? 'Stránka byla úspěšně vytvořena.'
            : 'Stránka byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/stranky/' + response.id);
      } else if (redirect) {
        router.push('/obsah/stranky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit stránku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="posts"
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
                  <DocumentTextIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Obsah příspěvku</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyková verze:</span
                >
                <span
                  class="inline-flex items-center rounded-md bg-slate-900 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-white"
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
                label="Titulek článku"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full"
                placeholder="Napište poutavý nadpis..."
              />

              <div
                class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60 lg:grid-cols-2"
              >
                <div class="col-span-full mb-1 flex items-center gap-2">
                  <GlobeAltIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >SEO Nastavení</span
                  >
                </div>

                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                  :key="`meta_title-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_title"
                  label="Meta název (Titulek v Googlu)"
                  type="text"
                  name="meta_title"
                  class="col-span-full"
                />

                <BaseFormTextarea
                  v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                  :key="`meta_description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_description"
                  label="Meta popis (Snippet ve vyhledávání)"
                  name="meta_description"
                  rows="2"
                  class="col-span-full"
                />
              </div>

              <div class="col-span-full space-y-10 pt-4">
                <div class="space-y-2">
                  <BaseFormEditor
                    v-if="item.translations?.[selectedLocale]?.perex !== undefined"
                    :key="`perex-${selectedLocale}`"
                    v-model="item.translations[selectedLocale].perex"
                    label="Perex (Stručný úvod článku)"
                    name="perex"
                  />
                  <p class="text-[11px] italic text-slate-400">
                    Tento text se zobrazuje v náhledu na blogu a na úvodní straně.
                  </p>
                </div>

                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.text !== undefined"
                  :key="`text-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].text"
                  label="Hlavní obsah článku"
                  name="text"
                />
              </div>
            </div>
          </LayoutContainer>
        </div>

        <div class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:sites="item.sites"
            v-model:active="item.active"
            :allow-image="false"
            :allow-is-active="true"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-4 text-amber-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-amber-900">
                Tip pro článek
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-amber-800/80">
              Ujistěte se, že <strong>Perex</strong> obsahuje nejdůležitější klíčová slova. Pomůže
              to nejen čtenářům v orientaci, ale i vašemu SEO.
            </p>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
