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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="careers"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" />
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
          <LayoutContainer class="col-span-9 w-full">
            <div class="grid grid-cols-4 gap-4">
              <BaseFormInput
                v-model="item.code"
                label="Kód"
                type="text"
                name="code"
                class="col-span-2"
                disabled
              />
              <br />
              <BaseFormSelect
                v-model="item.type"
                label="Typ úvazku"
                name="type"
                class="col-span-2"
                :options="[
                  { value: 'full-time', name: 'Plný úvazek' },
                  { value: 'part-time', name: 'Částečný úvazek' },
                  { value: 'freelance', name: 'Freelance' },
                  { value: 'internship', name: 'Stáž' },
                  { value: 'volunteer', name: 'Dobrovolník' },
                ]"
              />
              <BaseFormSelect
                v-model="item.salary_type"
                label="Typ mzdy"
                name="salary_type"
                class="col-span-2"
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
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.salary_to"
                label="Mzda do"
                type="number"
                name="salary_to"
                class="col-span-2"
              />

              <BaseFormSelect
                v-model="item.start_date"
                label="Nástup možný od"
                name="start_date"
                class="col-span-2"
                :options="[
                  { value: 'immediate', name: 'Ihned' },
                  { value: 'negotiable', name: 'Dohodou' },
                ]"
              />
              <div class="relative col-span-full mb-4 mt-8">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                  <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center">
                  <span class="bg-white px-3 text-base font-semibold text-gray-900"
                    >Popis pozice & SEO</span
                  >
                </div>
              </div>
              <BaseFormInput
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].location !== undefined
                "
                :key="`location-${selectedLocale}`"
                v-model="item.translations[selectedLocale].location"
                label="Místo výkonu práce"
                type="text"
                name="location"
                rules="required|min:3"
                class="col-span-2"
              />
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
                class="col-span-full"
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
                class="col-span-full"
              />
              <BaseFormEditor
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].benefits !== undefined
                "
                :key="`benefits-${selectedLocale}`"
                v-model="item.translations[selectedLocale].benefits"
                label="Benefity"
                name="text"
                class="col-span-2"
              />
              <BaseFormEditor
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].requirements !== undefined
                "
                :key="`requirements-${selectedLocale}`"
                v-model="item.translations[selectedLocale].requirements"
                label="Požadavky"
                name="requirements"
                class="col-span-2"
              />
            </div>
          </LayoutContainer>
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
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
            class="col-span-3"
          />
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#zadosti')">
        <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-9">
          <LayoutContainer class="col-span-full w-full">
            <div class="flex items-center justify-between">
              <p class="text-base text-grayCustom">
                Počet žádostí: {{ item.applications.data.length }}
              </p>
            </div>
            <BaseTable
              :items="item.applications"
              :columns="[
                {
                  key: 'id',
                  name: 'ID',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'firstname',
                  name: 'Jméno',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'lastname',
                  name: 'Příjmení',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'email',
                  name: 'E-mail',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'phone',
                  name: 'Telefon',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'availability',
                  name: 'Nástup možný od',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'salary_expectation',
                  name: 'Očekáváná mzda',
                  type: 'text',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
                {
                  key: 'status',
                  name: 'Stav přihlášky',
                  type: 'enum',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
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
              singular="Pracovní pozici"
              plural="Pracovní pozice"
              slug="careers"
            />
          </LayoutContainer>
        </div>
      </template>
    </Form>
  </div>
</template>
