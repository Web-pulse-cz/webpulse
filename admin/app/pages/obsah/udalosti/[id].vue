<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { Form } from 'vee-validate';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const toast = useToast();

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
    },
  })
    .then((response) => {
      item.value = response;
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst událost. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
      });
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorie. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
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
      toast.add({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Událost byla úspěšně vytvořena.'
            : 'Událost byla úspěšně upravena.',
        severity: 'succcess',
        group: 'bc',
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
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit událost. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
        group: 'bc',
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
      toast.add({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Registrace byla úspěšně vytvořena.'
            : 'Registrace byla úspěšně upravena.',
        severity: 'succcess',
        group: 'bc',
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
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit registraci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
        group: 'bc',
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
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se smazat položku registrace.',
        severity: 'error',
        group: 'bc',
      });
    })
    .finally(() => {
      loading.value = false;
      loadItem();
    });
}

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

function updateItemImage(files) {
  item.value.image = files[0];
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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="events"
      @save="saveItem"
    />
    <div>
      <div class="mt-5 block">
        <nav class="isolate flex divide-x divide-gray-200 shadow-sm" aria-label="Tabs">
          <NuxtLink
            v-for="(tab, index) in tabs"
            :key="index"
            :to="tab.link"
            class="group relative min-w-0 flex-1 overflow-hidden bg-white px-2 py-2.5 text-center text-xs font-medium text-grayCustom hover:bg-gray-50 hover:text-grayDark focus:z-10 lg:px-4 lg:py-4 lg:text-sm"
          >
            <span>{{ tab.name }}</span>
            <span
              aria-hidden="true"
              :class="
                tab.current
                  ? 'absolute inset-x-0 bottom-0 h-0.5 bg-primaryCustom'
                  : 'absolute inset-x-0 bottom-0 h-0.5 bg-transparent'
              "
            />
          </NuxtLink>
        </nav>
      </div>
    </div>
    <Form @submit="saveItem">
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-7">
          <LayoutContainer class="col-span-5 w-full">
            <div class="grid grid-cols-4 gap-8">
              <BaseFormInput
                v-model="item.code"
                label="Kód"
                type="text"
                name="code"
                class="col-span-2"
                disabled
              />
              <br />
              <BaseFormInput
                v-model="item.start_date"
                label="Začátek"
                type="datetime-local"
                name="published_from"
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.end_date"
                label="Konec"
                type="datetime-local"
                name="published_to"
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.place"
                label="Místo konání události"
                type="text"
                name="place"
                class="col-span-3"
                :disabled="item.is_online"
              />
              <br />
              <BaseFormCheckbox
                v-model="item.is_online"
                label="Online událost"
                name="is_online"
                class="col-span-1"
              />
              <BaseFormCheckbox
                v-model="item.registration_required"
                label="Povinná registrace"
                name="registration_required"
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.max_participants"
                label="Maximální počet účastníků"
                type="number"
                name="max_participants"
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.registration_from"
                label="Začátek registrace"
                type="datetime-local"
                name="registration_from"
                class="col-span-2"
              />
              <BaseFormInput
                v-model="item.price"
                label="Cena"
                type="number"
                name="price"
                class="col-span-2"
                :min="0"
              />
              <BaseFormSelect
                v-model="item.currency_id"
                label="Měna"
                name="currency_id"
                class="col-span-1"
                :options="currencyStore.currenciesOptions"
              />
              <BaseFormSelect
                v-model="item.tax_rate_id"
                label="Sazba DPH"
                name="tax_rate_id"
                class="col-span-1"
                :options="taxRateStore.taxRateOptions"
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
              <BaseFormInput
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].meta_title !== undefined
                "
                :key="`meta_title-${selectedLocale}`"
                v-model="item.translations[selectedLocale].meta_title"
                label="Meta název"
                type="text"
                name="meta_title"
                class="col-span-full"
              />
              <BaseFormTextarea
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].meta_description !== undefined
                "
                :key="`meta_description-${selectedLocale}`"
                v-model="item.translations[selectedLocale].meta_description"
                label="Meta popis"
                name="meta_description"
                class="col-span-full"
              />
              <BaseFormEditor
                v-if="
                  item.translations &&
                  item.translations[selectedLocale] !== undefined &&
                  item.translations[selectedLocale].perex !== undefined
                "
                :key="`perex-${selectedLocale}`"
                v-model="item.translations[selectedLocale].perex"
                label="Perex"
                name="perex"
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
              <BaseFormUploadImage
                v-model="item.image"
                :multiple="false"
                type="event"
                format="medium"
                label="Náhledový obrázek"
                class="col-span-full pt-6"
                @update-files="updateItemImage"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer class="col-span-2 w-full">
            <div class="col-span-1 border-b py-6">
              <BaseFormSelect
                v-model="selectedLocale"
                label="Jazyk"
                name="locale"
                class="w-full"
                :options="languageStore.languageOptions"
              />
            </div>
            <div class="col-span-1 border-b py-6">
              <BaseFormSelect
                v-model="item.status"
                label="Stav"
                name="status"
                class="col-span-1"
                :options="[
                  { value: 'draft', name: 'Koncept' },
                  { value: 'published', name: 'Publikováno' },
                  { value: 'archived', name: 'Archivováno' },
                ]"
              />
            </div>
            <div class="col-span-1 border-b py-6">
              <BaseFormInput
                v-model="item.position"
                label="Pořadí ve výpisu"
                name="position"
                class="col-span-1"
                :min="0"
                type="number"
              />
            </div>
            <div class="col-span-1 py-6">
              <BaseFormSelect
                v-model="item.event_category_id"
                label="Kategorie"
                name="event_category_id"
                class="col-span-1"
                :options="eventCategories"
              />
            </div>
          </LayoutContainer>
        </div>
      </template>
      <template v-if="tabs.find((tab) => tab.current && tab.link === '#registrace')">
        <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-9">
          <LayoutContainer class="col-span-full w-full">
            <div class="flex items-center justify-between">
              <p class="text-base text-grayCustom">
                Počet přihlášených lidí: {{ item.registrations.data.length }}
              </p>
              <BaseButton
                v-if="
                  item.max_participants > 0 &&
                  item.registrations.data.length < item.max_participants
                "
                class="mb-4"
                type="button"
                variant="primary"
                size="lg"
                @click="openRegistrationDialog()"
                >Vytvořit registraci</BaseButton
              >
            </div>
            <BaseTable
              :items="item.registrations"
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
                  key: 'is_paid',
                  name: 'Zaplaceno',
                  type: 'status',
                  width: 80,
                  hidden: false,
                  sortable: false,
                },
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
