<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useLanguageStore } from '~~/stores/languageStore';
import { useCurrencyStore } from '~~/stores/currencyStore';
import { AdjustmentsHorizontalIcon, BanknotesIcon, ExclamationTriangleIcon, GlobeAltIcon, LanguageIcon, SquaresPlusIcon, TrashIcon, UsersIcon } from '@heroicons/vue/24/outline';

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
    'allergens',
    'foodstuffs',
    'meals',
    'recipes',
    'menus',
    'restaurant_tables',
    'reservations',
    'posts',
    'pages',
    'novelties',
    'services',
    'events',
    'reviews',
    'logos',
    'careers',
    'quizzes',
    'customers',
    'vouchers',
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
    'project_time_entries',
    'trackings',
    'invoices',
    'suppliers',
    'employees',
    'employee_contracts',
    'shifts',
    'tasks',
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
  fakturoid_client_id: '' as string,
  fakturoid_client_secret: '' as string,
  fakturoid_slug: '' as string,
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
    case 'customers':
      return 'Zákazníci';
    case 'vouchers':
      return 'Slevové vouchery';
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
    case 'employee_contracts':
      return 'Smlouvy';
    case 'shifts':
      return 'Směny';
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
    case 'allergens':
      return 'Alergeny';
    case 'foodstuffs':
      return 'Potraviny';
    case 'meals':
      return 'Jídla';
    case 'recipes':
      return 'Recepty';
    case 'menus':
      return 'Jídelní lístky';
    case 'restaurant_tables':
      return 'Stoly';
    case 'reservations':
      return 'Rezervace';
    case 'project_time_entries':
      return 'Sledování času';
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
    item.value.settings.enabled_modules.push(module);
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
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="sites"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-2">
        <div class="space-y-8">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <GlobeAltIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Identita webu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.name"
                label="Název projektu"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. Barbershop Praha"
              />
              <BaseFormInput
                v-model="item.url"
                label="Doména (URL)"
                type="text"
                name="url"
                rules="required|min:3"
                placeholder="www.barbershop-praha.cz"
              />

              <div
                class="col-span-full grid grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
              >
                <div class="flex flex-col gap-2">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                    >Zabezpečení</span
                  >
                  <BaseFormSwitch
                    v-model:enabled="item.is_secure"
                    disabled-text="HTTP"
                    enabled-text="HTTPS"
                  />
                </div>
                <div class="flex flex-col gap-2 border-l border-slate-200 pl-4">
                  <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                    >Stav webu</span
                  >
                  <BaseFormSwitch
                    v-model:enabled="item.is_active"
                    disabled-text="Neaktivní"
                    enabled-text="Aktivní"
                  />
                </div>
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <CurrencyEuroIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Fakturoid API</LayoutTitle>
            </div>

            <p class="mb-4 text-xs text-slate-500">Přístupové údaje pro synchronizaci klientů a faktur s Fakturoidem. Ponechte prázdné pokud tato stránka Fakturoid nepoužívá.</p>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
              <BaseFormInput
                v-model="item.fakturoid_client_id"
                label="Client ID"
                type="text"
                name="fakturoid_client_id"
                placeholder="Vaše Fakturoid Client ID"
              />
              <BaseFormInput
                v-model="item.fakturoid_client_secret"
                label="Client Secret"
                type="password"
                name="fakturoid_client_secret"
                placeholder="Vaše Fakturoid Client Secret"
              />
              <BaseFormInput
                v-model="item.fakturoid_slug"
                label="Slug účtu"
                type="text"
                name="fakturoid_slug"
                placeholder="Např. moje-firma"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UsersIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Správa přístupů</LayoutTitle>
            </div>

            <div
              class="flex items-end gap-3 rounded-2xl border border-slate-100 bg-slate-50/50 p-4"
            >
              <SiteUsersAutocomplete
                v-model="assigenedUser"
                label="Vyhledat uživatele podle e-mailu nebo jména"
                class="flex-1"
              />
              <BaseButton
                v-if="assigenedUser !== null"
                type="button"
                variant="primary"
                size="xl"
                @click="addUserToItem"
              >
                Přidat
              </BaseButton>
            </div>

            <div class="mt-6 space-y-3">
              <div
                v-for="(user, index) in item.users"
                :key="index"
                class="group flex items-center justify-between rounded-2xl bg-white p-4 ring-1 ring-slate-200 transition-all hover:shadow-md"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="flex size-10 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-600"
                  >
                    {{ user.firstname.charAt(0) }}{{ user.lastname.charAt(0) }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-900">
                      {{ user.firstname }} {{ user.lastname }}
                    </p>
                    <p class="text-xs text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
                <BaseButton
                  type="button"
                  variant="danger"
                  size="sm"
                  class="opacity-0 transition-opacity group-hover:opacity-100"
                  @click="item.users.splice(index, 1)"
                >
                  <TrashIcon class="size-4" />
                </BaseButton>
              </div>

              <div
                v-if="!item.users?.length"
                class="rounded-3xl border-2 border-dashed border-slate-100 py-8 text-center"
              >
                <p class="text-sm text-slate-400">
                  K tomuto webu nejsou přiřazeni žádní uživatelé.
                </p>
              </div>
            </div>
          </LayoutContainer>
        </div>

        <div class="space-y-8">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
              >
                <AdjustmentsHorizontalIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Regionální nastavení & Moduly</LayoutTitle>
            </div>

            <div class="mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormSelect
                v-model="settings.default_locale"
                :options="languageStore.languageOptions"
                :label="getSettingTitle('default_locale')"
                name="default_locale"
              />
              <BaseFormSelect
                v-model="settings.default_currency"
                :options="currencyStore.currenciesOptions"
                :label="getSettingTitle('default_currency')"
                name="default_currency"
              />
            </div>

            <div class="space-y-8">
              <div class="rounded-2xl border border-slate-100 p-6">
                <div class="mb-4 flex items-center gap-2 border-b border-slate-50 pb-2">
                  <LanguageIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >Povolené jazyky</span
                  >
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                  <BaseFormCheckbox
                    v-for="(language, index) in languageStore.languages"
                    :key="index"
                    :value="item.settings.enabled_locales.includes(language.code)"
                    :checked="item.settings.enabled_locales.includes(language.code)"
                    :label="language.name"
                    :name="'enabled_locales' + index"
                    class="flex-row-reverse justify-between rounded-lg bg-slate-50 p-2"
                    @click="addRemoveEnabledLocale(language.code)"
                  />
                </div>
              </div>

              <div class="rounded-2xl border border-slate-100 p-6">
                <div class="mb-4 flex items-center gap-2 border-b border-slate-50 pb-2">
                  <BanknotesIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >Povolené měny</span
                  >
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                  <BaseFormCheckbox
                    v-for="(currency, index) in currencyStore.currencies"
                    :key="index"
                    :value="item.settings.enabled_currencies.includes(currency.id)"
                    :checked="item.settings.enabled_currencies.includes(currency.id)"
                    :label="currency.name"
                    :name="'enabled_currencies_' + index"
                    class="flex-row-reverse justify-between rounded-lg bg-slate-50 p-2"
                    @click="addRemoveEnabledCurrency(currency.id)"
                  />
                </div>
              </div>

              <div class="rounded-2xl border border-slate-100 p-6">
                <div class="mb-4 flex items-center gap-2 border-b border-slate-50 pb-2">
                  <SquaresPlusIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >Aktivní moduly systému</span
                  >
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  <BaseFormCheckbox
                    v-for="(module, index) in settings.enabled_modules"
                    :key="index"
                    :value="item.settings.enabled_modules.includes(module)"
                    :checked="item.settings.enabled_modules.includes(module)"
                    :label="getSettingTitle(module)"
                    :name="'enabled_modules' + module"
                    class="flex-row-reverse justify-between rounded-lg bg-slate-50 p-2"
                    @click="addRemoveEnabledModule(module)"
                  />
                </div>
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
            <div class="flex items-start gap-4">
              <ExclamationTriangleIcon class="size-6 shrink-0 text-amber-600" />
              <p class="text-sm leading-relaxed text-amber-900">
                <strong>Pozor:</strong> Deaktivace modulu, který se na webu aktivně používá, může
                způsobit chyby v zobrazení frontendu. Před vypnutím se ujistěte, že modul není nikde
                nasazen.
              </p>
            </div>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
