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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový jazyk' : 'Detail jazyka');

const breadcrumbs = ref([
  {
    name: 'Jazyky',
    link: '/nastaveni/jazyky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/jazyky/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  code: '' as string,
  iso: '' as string,
  active: true as boolean,
  translations: {} as object,
});
const translatableAttributes = ref([{ field: 'name' as string, label: 'Název' as string }]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    code: string;
    iso: string;
    active: boolean;
    translations: object;
  }>('/api/admin/language/' + route.params.id, {
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
        link: '/nastaveni/jazyky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst jazyk. Zkuste to prosím později.',
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
    code: string;
    iso: string;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/language' : '/api/admin/language/' + route.params.id,
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
            ? 'Jazyk byl úspěšně vytvořen.'
            : 'Jazyk byl úspěšně upraven.',
        severity: 'success',
      });
      languageStore.fetchLanguages();
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/jazyky/' + response.id);
      } else if (redirect) {
        router.push('/nastaveni/jazyky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit jazyk. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
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
      slug="languages"
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
                  <LanguageIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Systémové nastavení jazyka</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Právě upravujete:</span
                >
                <span
                  class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase tracking-tight text-white"
                >
                  {{ selectedLocale }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                v-model="item.translations[selectedLocale].name"
                label="Název jazyka"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full"
                placeholder="Např. Čeština"
              />

              <div
                class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60 sm:grid-cols-2"
              >
                <BaseFormInput
                  v-model="item.code"
                  label="Kód jazyka (např. cs)"
                  type="text"
                  name="code"
                  rules="required|min:2"
                  placeholder="cs"
                />
                <BaseFormInput
                  v-model="item.iso"
                  label="ISO kód jazyka (např. cs_CZ)"
                  type="text"
                  name="iso"
                  rules="required|min:2"
                  placeholder="cs_CZ"
                />
              </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
              Kódy jazyků slouží k propojení s překladovými soubory a nastavení správných meta tagů
              na frontendu.
            </p>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400"
              >Správa a stav</LayoutTitle
            >

            <div class="mt-6 space-y-6">
              <BaseFormSelect
                v-model="selectedLocale"
                label="Upravit název v jazyce"
                name="locale"
                class="w-full"
                :options="languageStore.languageOptions"
              />

              <div
                class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition-all hover:bg-white hover:shadow-sm"
              >
                <BaseFormCheckbox
                  v-model="item.active"
                  name="active"
                  label="Jazyk je aktivní"
                  class="flex-row-reverse justify-between font-bold text-slate-700"
                  :checked="item.active"
                  :reverse="true"
                />
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
            <div class="mb-3 flex items-center gap-2">
              <GlobeAltIcon class="size-4 text-indigo-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-900">
                Standardizace
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-indigo-800/80">
              Pro maximální kompatibilitu s vyhledávači používejte standardy
              <strong>ISO 639-1</strong>.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
