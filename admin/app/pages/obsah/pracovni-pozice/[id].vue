<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { BriefcaseIcon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová pracovní pozice' : 'Detail pracovní pozice',
);

const breadcrumbs = ref([
  {
    name: 'Pracovní pozice',
    link: '/obsah/pracovni-pozice',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/pracovni-pozice/pridat',
    current: true,
  },
]);

const tabs = ref([
  { name: 'Popis pracovní pozice', link: '#info', current: false },
  { name: 'Žádosti', link: '#zadosti', current: false },
]);

const item = ref({
  id: null as number | null,
  code: '' as string,
  image: null as File | null,
  position: 0 as number,
  type: 'full-time' as string,
  status: 'open' as string,
  salary_from: null as number | null,
  salary_to: null as number | null,
  salary_type: 'hourly' as string,
  start_date: null as string | null,
  name: '' as string,
  translations: {} as object,
  applications: [] as Array<object>,
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
  { field: 'location' as string, label: 'Lokace' as string },
  { field: 'benefits' as string, label: 'Benefity' as string },
  { field: 'requirements' as string, label: 'Požadavky' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: null | number;
    code: string;
    image: File | null;
    position: number;
    type: string;
    status: string;
    salary_from: number | null;
    salary_to: number | null;
    salary_type: string;
    start_date: string | null;
    name: string;
    translations: object;
    applications: Array<object>;
  }>('/api/admin/career/' + route.params.id, {
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
        link: '/obsah/pracovni-pozice/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst pracovní pozici. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/pracovni-pozice');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: null | number;
    code: string;
    image: File | null;
    position: number;
    type: string;
    status: string;
    salary_from: number | null;
    salary_to: number | null;
    salary_type: string;
    start_date: string | null;
    name: string;
    translations: object;
    applications: Array<object>;
  }>(route.params.id === 'pridat' ? '/api/admin/career' : '/api/admin/career/' + route.params.id, {
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
            ? 'Pracovní pozice byla úspěšně vytvořena.'
            : 'Pracovní pozice byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/pracovni-pozice/' + response.id);
      } else if (redirect) {
        router.push('/obsah/pracovni-pozice');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit pracovní pozici. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  } else {
    tabs.value = tabs.value.filter((tab) => tab.link !== '#zadosti');
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
      :modify-bottom="false"
      slug="careers"
      @save="saveItem"
    />

    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form ref="formRef" @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                  >
                    <BriefcaseIcon class="size-5" />
                  </div>
                  <LayoutTitle class="!mb-0">Parametry spolupráce</LayoutTitle>
                </div>
                <div class="text-right">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                    >Kód pozice</span
                  >
                  <p class="font-mono text-sm font-bold text-slate-600">{{ item.code || '---' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <BaseFormSelect
                  v-model="item.type"
                  label="Typ úvazku"
                  name="type"
                  class="col-span-1 lg:col-span-2"
                  :options="[
                    { value: 'full-time', name: 'Plný úvazek' },
                    { value: 'part-time', name: 'Částečný úvazek' },
                    { value: 'freelance', name: 'Freelance' },
                    { value: 'internship', name: 'Stáž' },
                    { value: 'volunteer', name: 'Dobrovolník' },
                  ]"
                />
                <BaseFormSelect
                  v-model="item.start_date"
                  label="Možný nástup"
                  name="start_date"
                  class="col-span-1 lg:col-span-2"
                  :options="[
                    { value: 'immediate', name: 'Ihned' },
                    { value: 'negotiable', name: 'Dohodou' },
                  ]"
                />

                <div
                  class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200 sm:grid-cols-3"
                >
                  <BaseFormSelect
                    v-model="item.salary_type"
                    label="Typ mzdy"
                    name="salary_type"
                    :options="[
                      { value: 'hourly', name: 'Hodinová' },
                      { value: 'monthly', name: 'Měsíční' },
                    ]"
                  />
                  <BaseFormInput
                    v-model="item.salary_from"
                    label="Mzda od"
                    type="number"
                    name="salary_from"
                  />
                  <BaseFormInput
                    v-model="item.salary_to"
                    label="Mzda do"
                    type="number"
                    name="salary_to"
                  />
                </div>
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-8">
                <LayoutDivider
                  >Popis pozice & SEO ({{ selectedLocale.toUpperCase() }})</LayoutDivider
                >
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.location !== undefined"
                  :key="`location-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].location"
                  label="Místo výkonu práce"
                  type="text"
                  name="location"
                  rules="required|min:3"
                  class="col-span-1"
                />

                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.name !== undefined"
                  :key="`name-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].name"
                  label="Název pozice"
                  type="text"
                  name="name"
                  rules="required|min:3"
                  class="col-span-full"
                  placeholder="Např. Senior Frontend Developer"
                />

                <div class="col-span-full space-y-8 pt-4">
                  <BaseFormEditor
                    v-if="item.translations?.[selectedLocale]?.text !== undefined"
                    :key="`text-${selectedLocale}`"
                    v-model="item.translations[selectedLocale].text"
                    label="Detailní popis pozice"
                    name="text"
                  />

                  <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <BaseFormEditor
                      v-if="item.translations?.[selectedLocale]?.benefits !== undefined"
                      :key="`benefits-${selectedLocale}`"
                      v-model="item.translations[selectedLocale].benefits"
                      label="Co nabízíme (Benefity)"
                      name="benefits"
                    />
                    <BaseFormEditor
                      v-if="item.translations?.[selectedLocale]?.requirements !== undefined"
                      :key="`requirements-${selectedLocale}`"
                      v-model="item.translations[selectedLocale].requirements"
                      label="Co požadujeme (Požadavky)"
                      name="requirements"
                    />
                  </div>
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:selected-locale="selectedLocale"
              v-model:translate-automatically="item.translateAutomatically"
              v-model:sites="item.sites"
              v-model:image="item.image"
              v-model:position="item.position"
              v-model:state="item.status"
              :allow-position="true"
              :allow-state="true"
              :states="[
                { value: 'open', name: 'Otevřená' },
                { value: 'closed', name: 'Uzavřená' },
              ]"
              image-type="career"
              class="shadow-sm"
            />
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#zadosti')">
        <LayoutContainer>
          <div class="mb-8 flex items-center justify-between">
            <div>
              <LayoutTitle class="!mb-1">Přijaté žádosti</LayoutTitle>
              <p class="text-sm font-medium text-slate-500">
                Celkový počet uchazečů:
                <span class="font-bold text-indigo-600">{{ item.applications.data.length }}</span>
              </p>
            </div>
          </div>

          <BaseTable
            :items="item.applications"
            :columns="[
              { key: 'id', name: 'ID', type: 'text', width: 60 },
              { key: 'firstname', name: 'Jméno', type: 'text' },
              { key: 'lastname', name: 'Příjmení', type: 'text' },
              { key: 'email', name: 'E-mail', type: 'text' },
              { key: 'phone', name: 'Telefon', type: 'text' },
              { key: 'availability', name: 'Nástup od', type: 'text' },
              { key: 'salary_expectation', name: 'Očekávaná mzda', type: 'text' },
              { key: 'status', name: 'Stav', type: 'enum', width: 120 },
            ]"
            :enums="{
              status: {
                pending: 'Čeká',
                reviewed: 'Zobrazeno',
                accepted: 'Přijato',
                rejected: 'Zamítnuto',
              },
            }"
            :actions="[{ type: 'edit' }, { type: 'delete' }]"
            :loading="loading"
            :error="error"
            singular="Uchazeče"
            plural="Uchazeči"
            slug="careers"
          />
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
