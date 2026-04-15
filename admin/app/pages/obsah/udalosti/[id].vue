<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { BanknotesIcon, CalendarIcon, PencilSquareIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';
import { useLanguageStore } from '~~/stores/languageStore';

const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

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

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: false },
  { name: 'Registrace', link: '#registrace', current: false },
]);

const eventCategories = ref([{ value: null as number | null, name: 'Žádná kategorie' }]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová událost' : 'Detail události');

const breadcrumbs = ref([
  {
    name: 'Události a akce',
    link: '/obsah/udalosti',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/udalosti/pridat',
    current: true,
  },
]);

const registrationDialog = ref({
  show: false as boolean,
  item: {
    event_id: route.params.id,
    id: null as number | null,
    firstname: '' as string,
    lastname: '' as string,
    email: '' as string,
    phone: '' as string,
    note: '' as string,
    ico: '' as string,
    dic: '' as string,
    company: '' as string,
    street: '' as string,
    city: '' as string,
    zip: '' as string,
    country_id: 1 as number,
    is_paid: false as boolean,
  },
});

const item = ref({
  id: null as number | null,
  name: '' as string,
  code: '' as string,
  image: '' as string,
  status: 'draft' as string,
  position: 0 as number,
  place: '' as string,
  is_online: false as boolean,
  max_participants: 0 as number,
  registration_required: false as boolean,
  price: 0 as number,
  registration_from: null as string | null,
  start_date: null as string | null,
  end_date: null as string | null,
  event_category_id: null as number | null,
  currency_id: null as number | null,
  tax_rate_id: null as number | null,
  registrations: [] as Array<object>,
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
    name: string;
    code: string;
    image: string;
    status: string;
    position: number;
    place: string;
    is_online: boolean;
    max_participants: number;
    registration_required: boolean;
    price: number;
    registration_from: string | null;
    start_date: string | null;
    end_date: string | null;
    event_category_id: number | null;
    currency_id: number | null;
    tax_rate_id: number | null;
    registrations: Array<object>;
    translations: object;
  }>('/api/admin/event/' + route.params.id, {
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
        link: '/obsah/udalosti/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
      item.value.registration_from = item.value.registration_from
        ? new Date(item.value.registration_from).toISOString().slice(0, 16)
        : null;
      item.value.start_date = item.value.start_date
        ? new Date(item.value.start_date).toISOString().slice(0, 16)
        : null;
      item.value.end_date = item.value.end_date
        ? new Date(item.value.end_date).toISOString().slice(0, 16)
        : null;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst událost. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/udalosti');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/event/category', {
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
      const categories = response.map((category) => ({
        value: category.id,
        name: category.translations?.['cs']?.name || 'Neznámá kategorie',
      }));
      eventCategories.value = [{ value: null, name: 'Žádná kategorie' }, ...categories];
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
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    code: string;
    image: string;
    status: string;
    position: number;
    place: string;
    is_online: boolean;
    max_participants: number;
    registration_required: boolean;
    price: number;
    registration_from: string | null;
    start_date: string | null;
    end_date: string | null;
    event_category_id: number | null;
    currency_id: number | null;
    tax_rate_id: number | null;
    registrations: Array<object>;
    translations: object;
  }>(route.params.id === 'pridat' ? '/api/admin/event' : '/api/admin/event/' + route.params.id, {
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
            ? 'Událost byla úspěšně vytvořena.'
            : 'Událost byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/udalosti/' + response.id);
      } else if (redirect) {
        router.push('/obsah/udalosti');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit událost. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveRegistrationItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    event_id: string | number;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    note: string;
    ico: string;
    dic: string;
    company: string;
    street: string;
    city: string;
    zip: string;
    country_id: number;
    is_paid: boolean;
  }>(
    registrationDialog.value.item.id === null
      ? '/api/admin/event/registration'
      : '/api/admin/event/registration/' + registrationDialog.value.item.id,
    {
      method: 'POST',
      body: JSON.stringify(registrationDialog.value.item),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Registrace byla úspěšně vytvořena.'
            : 'Registrace byla úspěšně upravena.',
        severity: 'success',
      });
      registrationDialog.value.show = false;
      registrationDialog.value.item = {
        id: null,
        event_id: route.params.id,
        firstname: '',
        lastname: '',
        email: '',
        phone: '',
        note: '',
        ico: '',
        dic: '',
        company: '',
        street: '',
        city: '',
        zip: '',
        country_id: 1,
        is_paid: false,
      };
      loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit registraci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function deleteRegistrationItem(id: number) {
  loading.value = true;
  const client = useSanctumClient();

  await client<{ id: number }>('/api/admin/event/registration/' + id, {
    method: 'DELETE',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku registrace.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItem();
    });
}

watch(selectedSiteHash, () => {
  loadItem();
  loadCategories();
});

useHead({
  title: pageTitle.value,
});

function openRegistrationDialog(item?: any) {
  registrationDialog.value.item = item ?? registrationDialog.value.item;
  registrationDialog.value.show = true;
}

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
  loadCategories();
  if (route.params.id !== 'pridat') {
    loadItem();
  } else {
    // remove #registration tab from tabs
    tabs.value = tabs.value.filter((tab) => tab.link !== '#registrace');
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
      slug="events"
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
                    <CalendarIcon class="size-5" />
                  </div>
                  <LayoutTitle class="!mb-0">Termín a místo konání</LayoutTitle>
                </div>
                <div class="text-right">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                    >Interní kód</span
                  >
                  <p class="font-mono text-sm font-bold text-slate-600">{{ item.code || '---' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <BaseFormInput
                  v-model="item.start_date"
                  label="Začátek události"
                  type="datetime-local"
                  name="published_from"
                />
                <BaseFormInput
                  v-model="item.end_date"
                  label="Konec události"
                  type="datetime-local"
                  name="published_to"
                />
                <BaseFormInput
                  v-model="item.place"
                  label="Místo konání / Adresa"
                  type="text"
                  name="place"
                  class="col-span-1 lg:col-span-2"
                  :disabled="item.is_online"
                />

                <div
                  class="col-span-full rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
                >
                  <BaseFormCheckbox
                    v-model="item.is_online"
                    label="Toto je online událost (webinář/stream)"
                    name="is_online"
                    class="w-full flex-row-reverse justify-between"
                  />
                </div>
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <BanknotesIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Registrace a cena</LayoutTitle>
              </div>

              <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-4 rounded-2xl border border-slate-100 p-5">
                  <BaseFormCheckbox
                    v-model="item.registration_required"
                    label="Vyžadovat registraci"
                    name="registration_required"
                    class="w-full flex-row-reverse justify-between border-b border-slate-100 pb-4"
                  />
                  <BaseFormInput
                    v-model="item.max_participants"
                    label="Kapacita účastníků"
                    type="number"
                    name="max_participants"
                  />
                  <BaseFormInput
                    v-model="item.registration_from"
                    label="Registrace spuštěna od"
                    type="datetime-local"
                    name="registration_from"
                  />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:col-span-2">
                  <div class="col-span-full sm:col-span-2">
                    <BaseFormInput
                      v-model="item.price"
                      label="Základní cena (vstupný)"
                      type="number"
                      name="price"
                      :min="0"
                    />
                  </div>
                  <BaseFormSelect
                    v-model="item.currency_id"
                    label="Měna"
                    name="currency_id"
                    :options="currencyStore.currenciesOptions"
                  />
                  <BaseFormSelect
                    v-model="item.tax_rate_id"
                    label="Sazba DPH"
                    name="tax_rate_id"
                    :options="taxRateStore.taxRateOptions"
                  />
                </div>
              </div>
            </LayoutContainer>

            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                >
                  <PencilSquareIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0"
                  >Obsah události ({{ selectedLocale.toUpperCase() }})</LayoutTitle
                >
              </div>

              <div class="space-y-6">
                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]"
                  v-model="item.translations[selectedLocale].name"
                  label="Název události"
                  type="text"
                  name="name"
                  rules="required|min:3"
                  class="col-span-full"
                />

                <div class="space-y-6 rounded-2xl bg-slate-50 p-6">
                  <BaseFormInput
                    v-if="item.translations?.[selectedLocale]"
                    v-model="item.translations[selectedLocale].meta_title"
                    label="SEO Titulek (Meta Title)"
                    type="text"
                    name="meta_title"
                  />
                  <BaseFormTextarea
                    v-if="item.translations?.[selectedLocale]"
                    v-model="item.translations[selectedLocale].meta_description"
                    label="SEO Popis (Meta Description)"
                    name="meta_description"
                    rows="2"
                  />
                </div>

                <div class="space-y-8">
                  <BaseFormEditor
                    v-if="item.translations?.[selectedLocale]"
                    v-model="item.translations[selectedLocale].perex"
                    label="Krátký perex (upoutávka)"
                    name="perex"
                  />
                  <BaseFormEditor
                    v-if="item.translations?.[selectedLocale]"
                    v-model="item.translations[selectedLocale].text"
                    label="Kompletní popis události"
                    name="text"
                  />
                </div>
              </div>
            </LayoutContainer>
          </div>

          <div class="col-span-1 lg:col-span-3">
            <LayoutActionsDetailBlock
              v-model:state="item.status"
              v-model:category-id="item.event_category_id"
              v-model:position="item.position"
              v-model:sites="item.sites"
              v-model:image="item.image"
              v-model:selected-locale="selectedLocale"
              v-model:translate-automatically="item.translateAutomatically"
              :allow-state="true"
              :states="[
                { value: 'draft', name: 'Koncept' },
                { value: 'published', name: 'Publikováno' },
                { value: 'archived', name: 'Archivováno' },
              ]"
              :allow-categories="true"
              :categories="eventCategories"
              :allow-position="true"
              image-type="event"
              class="sticky top-24"
            />
          </div>
        </div>
      </template>

      <template v-if="tabs.find((tab) => tab.current && tab.link === '#registrace')">
        <div class="space-y-6">
          <LayoutContainer>
            <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <LayoutTitle class="!mb-1">Přihlášení účastníci</LayoutTitle>
                <p class="text-sm font-medium text-slate-500">
                  Aktuálně registrováno:
                  <span class="font-bold text-indigo-600">{{
                    item.registrations.data.length
                  }}</span>
                  <span v-if="item.max_participants > 0"> / {{ item.max_participants }}</span>
                </p>
              </div>

              <BaseButton
                v-if="
                  item.max_participants === 0 ||
                  item.registrations.data.length < item.max_participants
                "
                type="button"
                variant="primary"
                size="lg"
                @click="openRegistrationDialog()"
              >
                <PlusIcon class="mr-2 size-5" />
                Vytvořit registraci
              </BaseButton>
            </div>

            <BaseTable
              :items="item.registrations"
              :columns="[
                { key: 'id', name: 'ID', type: 'text', width: 60 },
                { key: 'firstname', name: 'Jméno', type: 'text' },
                { key: 'lastname', name: 'Příjmení', type: 'text' },
                { key: 'email', name: 'E-mail', type: 'text' },
                { key: 'phone', name: 'Telefon', type: 'text' },
                { key: 'is_paid', name: 'Zaplaceno', type: 'status', width: 100 },
              ]"
              :actions="[{ type: 'edit-dialog' }, { type: 'delete' }]"
              :loading="loading"
              :error="error"
              singular="Registrace"
              plural="Registrace"
              slug="events"
              @delete-item="deleteRegistrationItem"
              @open-dialog="openRegistrationDialog($event)"
            />
          </LayoutContainer>
        </div>
      </template>
    </Form>

    <EventRegistrationDialog
      v-model:show="registrationDialog.show"
      :item="registrationDialog.item"
      @save="saveRegistrationItem"
    />
  </div>
</template>
