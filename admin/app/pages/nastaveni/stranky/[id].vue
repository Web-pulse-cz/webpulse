<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';
import { useCurrencyStore } from '~~/stores/currencyStore';

const languageStore = useLanguageStore();
const currencyStore = useCurrencyStore();

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const assigenedUser = ref(null);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová stránka' : 'Detail stránky');

const breadcrumbs = ref([
  {
    name: 'Stránky',
    link: '/nastaveni/stranky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/stranky',
    current: true,
  },
]);

const settings = {
  enabled_modules: [
    'posts',
    'pages',
    'novelties',
    'services',
    'events',
    'reviews',
    'logos',
    'careers',
    'quizzes',
    'newsletters',
    'demands',
    'contacts',
    'users_has_activities',
    'message_blueprints',
    'calendars',
    'cashflows',
    'leagues',
    'clients',
    'biographies',
    'projects',
    'price_offers',
    'trackings',
    'invoices',
    'suppliers',
    'employees',
    'tasks',
    'contracts',
    'users',
    'settings',
    'activities',
    'tax_rates',
    'languages',
    'countries',
    'currencies',
    'emails',
    'sites',
    'faqs',
    'changelogs',
  ],
  default_currency: 1,
  enabled_currencies: [1, 3],
  default_locale: 'cs',
  enabled_locales: ['cs', 'sk', 'en'],
};

const item = ref({
  id: null as number | null,
  name: '' as string,
  url: '' as string,
  is_secure: false as boolean,
  is_active: false as boolean,
  settings: {
    enabled_modules: [],
    enabled_currencies: [],
    enabled_locales: [],
    default_currency: null,
    default_locale: '',
  } as Record<string, unknown>,
  users: [] as Array<number>,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    url: string;
    is_secure: boolean;
    is_active: boolean;
    settings: Record<string, unknown>;
    users: Array<number>;
  }>('/api/admin/site/' + route.params.id, {
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
        link: '/nastaveni/stranky/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stránku. Zkuste to prosím později.',
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
    name: string;
    url: string;
    is_secure: boolean;
    is_active: boolean;
    settings: Record<string, unknown>;
    users: Array<number>;
  }>(route.params.id === 'pridat' ? '/api/admin/site' : '/api/admin/site/' + route.params.id, {
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
        router.push('/nastaveni/stranky/' + response.id + '?created=true');
      } else if (redirect) {
        router.push('/nastaveni/stranky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit stránku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addUserToItem() {
  if (!item.value.users.includes(assigenedUser.value.id)) {
    item.value.users.push(assigenedUser.value);
  }
}

const getSettingTitle = computed(() => (key: string) => {
  switch (key) {
    case 'default_locale':
      return 'Výchozí jazyk';
    case 'default_currency':
      return 'Výchozí měna';
    case 'posts':
      return 'Blogové članky';
    case 'pages':
      return 'Informační stránky';
    case 'novelties':
      return 'Novinky';
    case 'services':
      return 'Služby';
    case 'events':
      return 'Události a akce';
    case 'reviews':
      return 'Recenze';
    case 'logos':
      return 'Loga klientů';
    case 'careers':
      return 'Pracovní pozice';
    case 'quizzes':
      return 'Kvízy';
    case 'faqs':
      return 'FAQ';
    case 'newsletters':
      return 'Odběry newsletteru';
    case 'demands':
      return 'Poptávky';
    case 'contacts':
      return 'Kontakty';
    case 'users_has_activities':
      return 'Aktivita';
    case 'message_blueprints':
      return 'Šablony zpráv';
    case 'calendars':
      return 'Kalendář';
    case 'cashflows':
      return 'Cashflow';
    case 'leagues':
      return 'Ligy';
    case 'clients':
      return 'Klienti';
    case 'biographies':
      return 'Životopisy';
    case 'projects':
      return 'Projekty';
    case 'price_offers':
      return 'Cenové nabídky';
    case 'trackings':
      return 'Trackování';
    case 'invoices':
      return 'Faktury';
    case 'suppliers':
      return 'Dodavatelé';
    case 'employees':
      return 'Zaměstnanci';
    case 'tasks':
      return 'Úkoly';
    case 'contracts':
      return 'Smlouvy';
    case 'users':
      return 'Administrátoři';
    case 'settings':
      return 'Nastavení';
    case 'activities':
      return 'Aktivity';
    case 'tax_rates':
      return 'Sazby DPH';
    case 'languages':
      return 'Jazyky';
    case 'countries':
      return 'Země';
    case 'currencies':
      return 'Měny';
    case 'emails':
      return 'E-maily';
    case 'sites':
      return 'Stránky';
    case 'changelogs':
      return 'Changelog';
    default:
      return key.replace('_', ' ').toUpperCase();
  }
});

useHead({
  title: pageTitle.value,
});

function addRemoveEnabledModule(module: string) {
  if (item.value.settings.enabled_modules.includes(module)) {
    item.value.settings.enabled_modules.splice(
        item.value.settings.enabled_modules.indexOf(module),
        1,
    );
  } else {
    item.value.settings.enabled_modules.push(currency);
  }
}

function addRemoveEnabledCurrency(currency: number) {
  if (item.value.settings.enabled_currencies.includes(currency)) {
    item.value.settings.enabled_currencies.splice(
      item.value.settings.enabled_currencies.indexOf(currency),
      1,
    );
  } else {
    item.value.settings.enabled_currencies.push(currency);
  }
}

function addRemoveEnabledLocale(locale: string) {
  if (item.value.settings.enabled_locales.includes(locale)) {
    item.value.settings.enabled_locales.splice(
      item.value.settings.enabled_locales.indexOf(locale),
      1,
    );
  } else {
    item.value.settings.enabled_locales.push(locale);
  }
}

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  } else {
    item.value.settings = settings;
  }
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
      slug="sites"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-2 items-baseline gap-x-10">
        <div class="col-span-1 w-full">
          <LayoutContainer class="col-span-1 w-full">
            <div class="grid grid-cols-12 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.name"
                label="Název"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-6"
              />
              <BaseFormInput
                v-model="item.url"
                label="URL"
                type="text"
                name="url"
                rules="required|min:3"
                class="col-span-6"
              />
              <BaseFormSwitch
                v-model:enabled="item.is_secure"
                disabled-text="HTTP"
                enabled-text="HTTPS"
                class="col-span-4"
              />
              <div class="col-span-4"></div>
              <BaseFormSwitch
                v-model:enabled="item.is_active"
                disabled-text="Neaktivní"
                enabled-text="Aktivní"
                class="col-span-4"
              />
            </div>
          </LayoutContainer>
          <LayoutContainer class="col-span-1 w-full">
            <LayoutTitle>Přiřazení uživatelé</LayoutTitle>
            <div class="grid grid-cols-3 gap-x-8 gap-y-4">
              <div class="col-span-full grid grid-cols-3 items-end gap-x-8 p-1.5">
                <SiteUsersAutocomplete
                  v-model="assigenedUser"
                  label="Vyhledat uživatele"
                  class="col-span-2"
                />
                <BaseButton
                  v-if="assigenedUser !== null"
                  type="button"
                  variant="secondary"
                  size="lg"
                  @click="addUserToItem"
                  >Přidat</BaseButton
                >
              </div>
              <div
                v-for="(user, index) in item.users"
                :key="index"
                class="col-span-full grid grid-cols-3 items-center border p-1.5"
              >
                <div class="col-span-1 text-grayCustom">
                  {{ user.firstname + ' ' + user.lastname }}
                </div>
                <div class="col-span-1 text-grayCustom">
                  {{ user.email }}
                </div>
                <div class="col-span-1 text-end">
                  <BaseButton
                    type="button"
                    variant="secondary"
                    size="lg"
                    @click="item.users.splice(index, 1)"
                    >Odstranit</BaseButton
                  >
                </div>
              </div>
            </div>
          </LayoutContainer>
        </div>
        <LayoutContainer class="col-span-1 w-full">
          <LayoutTitle>Nastavení</LayoutTitle>
          <div class="grid grid-cols-4 gap-x-8 gap-y-4">
            <BaseFormSelect
              v-model="settings.default_locale"
              :options="languageStore.languageOptions"
              :label="getSettingTitle('default_locale')"
              class="col-span-2"
              name="default_locale"
            />
            <BaseFormSelect
              v-model="settings.default_currency"
              :options="currencyStore.currenciesOptions"
              :label="getSettingTitle('default_currency')"
              class="col-span-2"
              name="default_currency"
            />
          </div>
          <LayoutDivider>Povolené jazyky</LayoutDivider>
          <div class="grid grid-cols-3 gap-x-8 gap-y-4">
            <BaseFormCheckbox
              v-for="(language, index) in languageStore.languages"
              :key="index"
              :value="item.settings.enabled_locales.includes(language.code)"
              :checked="item.settings.enabled_locales.includes(language.code)"
              :label="language.name"
              :name="'enabled_locales' + index"
              @click="addRemoveEnabledLocale(language.code)"
            />
          </div>
          <LayoutDivider>Povolené měny</LayoutDivider>
          <div class="grid grid-cols-3 gap-x-8 gap-y-4">
            <BaseFormCheckbox
              v-for="(currency, index) in currencyStore.currencies"
              :key="index"
              :value="item.settings.enabled_currencies.includes(currency.id)"
              :checked="item.settings.enabled_currencies.includes(currency.id)"
              :label="currency.name"
              :name="'enabled_currencies_' + index"
              @click="addRemoveEnabledCurrency(currency.id)"
            />
          </div>
          <LayoutDivider>Povolené moduly</LayoutDivider>
          <div class="grid grid-cols-3 gap-x-8 gap-y-4">
            <BaseFormCheckbox
              v-for="(module, index) in settings.enabled_modules"
              :key="index"
              :value="item.settings.enabled_modules.includes(module)"
              :checked="item.settings.enabled_modules.includes(module)"
              class="col-span-1"
              :label="getSettingTitle(module)"
              :name="'enabled_modules' + module"
              @click="addRemoveEnabledModule(module)"
            />
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
