<script setup lang="ts">
import { ref, provide, computed, watch, watchEffect, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
} from '@headlessui/vue';
import {
  Bars3Icon,
  CalendarIcon,
  ChartPieIcon,
  StarIcon,
  HomeIcon,
  UsersIcon,
  XMarkIcon,
  ArrowTopRightOnSquareIcon,
  BanknotesIcon,
  ClockIcon,
  BuildingOfficeIcon,
  WalletIcon,
  DocumentTextIcon,
  ChatBubbleBottomCenterTextIcon,
  ChartBarSquareIcon,
  AtSymbolIcon,
  TrophyIcon,
  CalendarDaysIcon,
  DocumentCurrencyEuroIcon,
  LanguageIcon,
  GlobeEuropeAfricaIcon,
  CurrencyEuroIcon,
  WrenchScrewdriverIcon,
  DocumentIcon,
  ArchiveBoxIcon,
  NewspaperIcon,
  QuestionMarkCircleIcon,
  CogIcon,
  PhotoIcon,
  ChevronRightIcon,
  ChatBubbleLeftRightIcon,
  AcademicCapIcon,
  GlobeAltIcon,
  CodeBracketIcon,
  ExclamationTriangleIcon,
  ShoppingCartIcon,
} from '@heroicons/vue/24/outline';
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/solid';
import { useUserGroupStore } from '~/../stores/userGroupStore';
import { useActivityStore } from '~/../stores/activityStore';
import { useLanguageStore } from '~/../stores/languageStore';
import { useCountryStore } from '~/../stores/countryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';
import { useCashflowCategoryStore } from '~/../stores/cashflowCategoryStore';

const userGroupStore = useUserGroupStore();
const activityStore = useActivityStore();
const languageStore = useLanguageStore();
const countryStore = useCountryStore();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();
const cashflowCategoryStore = useCashflowCategoryStore();

const route = useRoute();
const router = useRouter();
const user = useSanctumUser();
const { logout, refreshIdentity } = useSanctumAuth();
const sidebarOpen = ref(false);

const searchString = ref('');
provide('searchString', searchString);

const selectedSiteHash = ref('');
provide('selectedSiteHash', selectedSiteHash);

const navigation = ref([
  {
    title: 'Úvod',
    menu: [
      { name: 'Přehled', link: '/', icon: HomeIcon, current: true },
      {
        name: 'Statistiky',
        link: '/statistiky',
        icon: ChartPieIcon,
        current: false,
      },
    ],
  },
  {
    title: 'Obsah',
    menu: [
      {
        name: 'Blogové članky',
        icon: ArchiveBoxIcon,
        current: false,
        slug: 'posts',
        submenu: [
          {
            name: 'Články',
            link: '/obsah/clanky',
            current: false,
          },
          {
            name: 'Kategorie',
            link: '/obsah/clanky/kategorie',
            current: false,
          },
        ],
      },
      {
        name: 'Informační stránky',
        link: '/obsah/stranky',
        icon: DocumentIcon,
        current: false,
        slug: 'pages',
      },
      {
        name: 'Novinky',
        link: '/obsah/novinky',
        icon: NewspaperIcon,
        current: false,
        slug: 'novelties',
      },
      {
        name: 'Služby',
        link: '/obsah/sluzby',
        icon: WrenchScrewdriverIcon,
        current: false,
        slug: 'services',
      },
      {
        name: 'Události a akce',
        icon: CalendarDaysIcon,
        current: false,
        slug: 'events',
        submenu: [
          {
            name: 'Události',
            link: '/obsah/udalosti',
            current: false,
          },
          {
            name: 'Kategorie',
            link: '/obsah/udalosti/kategorie',
            current: false,
          },
          {
            name: 'Registrace',
            link: '/obsah/udalosti/registrace',
            current: false,
          },
        ],
      },
      {
        name: 'Recenze',
        link: '/obsah/reference',
        icon: StarIcon,
        current: false,
        slug: 'reviews',
      },
      {
        name: 'Loga klientů',
        link: '/obsah/loga',
        icon: PhotoIcon,
        current: false,
        slug: 'logos',
      },
      {
        name: 'Pracovní pozice',
        icon: AcademicCapIcon,
        current: false,
        slug: 'careers',
        submenu: [
          {
            name: 'Pracovní pozice',
            link: '/obsah/pracovni-pozice',
            current: false,
          },
          {
            name: 'Žádosti',
            link: '/obsah/pracovni-pozice/zadosti',
            current: false,
          },
        ],
      },
      {
        name: 'Kvízy',
        link: '/obsah/kvizy',
        icon: QuestionMarkCircleIcon,
        current: false,
        slug: 'quizzes',
      },
      {
        name: 'FAQ',
        icon: ChatBubbleLeftRightIcon,
        current: false,
        slug: 'faqs',
        submenu: [
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
        ],
      },
    ],
  },
  {
    title: 'Restaurace',
    menu: [
      {
        name: 'Alergeny',
        link: '/restaurace/alergeny',
        icon: ExclamationTriangleIcon,
        current: false,
        slug: 'allergens',
      },
      {
        name: 'Potraviny',
        icon: ShoppingCartIcon,
        current: false,
        slug: 'foodstuffs',
        submenu: [
          {
            name: 'Potraviny',
            link: '/restaurace/potraviny',
            current: false,
          },
          {
            name: 'Kategorie potravin',
            link: '/restaurace/potraviny/kategorie',
            current: false,
          },
        ],
      },
      {
        name: 'Jídelní lístky',
        link: '/restaurace/jidelni-listky',
        icon: DocumentTextIcon,
        current: false,
        slug: 'menus',
      },
      {
        name: 'Rezervace',
        link: '/restaurace/rezervace',
        icon: CalendarIcon,
        current: false,
        slug: 'reservations',
      },
    ],
  },
  {
    title: 'Uživatelé',
    menu: [
      {
        name: 'Odběry newsletteru',
        link: '/uzivatele/newslettery',
        icon: AtSymbolIcon,
        current: false,
        slug: 'newsletters',
      },
      {
        name: 'Poptávky',
        link: '/uzivatele/poptavky',
        icon: QuestionMarkCircleIcon,
        current: false,
        slug: 'demands',
      },
    ],
  },
  {
    title: 'Byznys a osobní růst',
    menu: [
      {
        name: 'Kontakty',
        icon: UsersIcon,
        current: false,
        slug: 'contacts',
        submenu: [
          {
            name: 'Kontakty',
            link: '/kontakty',
            current: false,
          },
          {
            name: 'Seznamy kontaktů',
            link: '/kontakty/seznamy',
            current: false,
          },
          {
            name: 'Zdroje',
            link: '/kontakty/zdroje',
            current: false,
          },
          {
            name: 'Fáze',
            link: '/kontakty/faze',
            current: false,
          },
          {
            name: 'Úkoly',
            link: '/kontakty/ukoly',
            current: false,
          },
        ],
      },
      {
        name: 'Aktivita',
        link: '/aktivita',
        icon: CalendarIcon,
        current: false,
        slug: 'users_has_activities',
      },
      {
        name: 'Šablony zpráv',
        link: '/sablony-zprav',
        icon: ChatBubbleBottomCenterTextIcon,
        current: false,
        slug: 'message_blueprints',
      },
      {
        name: 'Kalendář',
        link: '/demo',
        icon: CalendarIcon,
        current: false,
        slug: 'calendars',
      },
      {
        name: 'Cashflow',
        link: '/cashflow',
        icon: BanknotesIcon,
        current: false,
        slug: 'cashflows',
      },
      {
        name: 'Ligy',
        link: '/demo',
        icon: TrophyIcon,
        current: false,
        slug: 'leagues',
      },
    ],
  },
  {
    title: 'Vedení firmy',
    menu: [
      {
        name: 'Klienti',
        link: '/demo',
        icon: BuildingOfficeIcon,
        current: false,
        slug: 'clients',
      },
      {
        name: 'Životopisy',
        link: '/zivotopisy',
        icon: DocumentIcon,
        current: false,
        slug: 'biographies',
      },
      {
        name: 'Projekty',
        link: '/projekty',
        icon: BuildingOfficeIcon,
        current: false,
        slug: 'projects',
      },
      {
        name: 'Cenové nabídky',
        link: '/cenove-nabidky',
        icon: DocumentTextIcon,
        current: false,
        slug: 'price_offers',
      },
      {
        name: 'Trackování',
        link: '/demo',
        icon: ClockIcon,
        current: false,
        slug: 'trackings',
      },
      {
        name: 'Faktury',
        link: '/demo',
        icon: WalletIcon,
        current: false,
        slug: 'invoices',
      },
      {
        name: 'Dodavatelé',
        link: '/demo',
        icon: BuildingOfficeIcon,
        current: false,
        slug: 'suppliers',
      },
      {
        name: 'Zaměstnanci',
        link: '/demo',
        icon: UsersIcon,
        current: false,
        slug: 'employees',
      },
      {
        name: 'Úkoly',
        link: '/demo',
        icon: ChartBarSquareIcon,
        current: false,
        slug: 'tasks',
      },
      {
        name: 'Smlouvy',
        link: '/demo',
        icon: DocumentTextIcon,
        current: false,
        slug: 'contracts',
      },
    ],
  },
  {
    title: 'Nastavení a správa',
    menu: [
      {
        name: 'Administrátoři',
        icon: UsersIcon,
        current: false,
        slug: 'users',
        submenu: [
          {
            name: 'Administrátoři',
            link: '/administratori',
            current: false,
          },
          {
            name: 'Skupiny a oprávnění',
            link: '/administratori/skupiny',
            current: false,
          },
        ],
      },
      {
        name: 'Stránky',
        link: '/nastaveni/stranky',
        icon: GlobeAltIcon,
        current: false,
        slug: 'sites',
      },
      {
        name: 'Nastavení',
        link: '/nastaveni',
        icon: CogIcon,
        current: false,
        slug: 'settings',
      },
      {
        name: 'Changelog',
        link: '/nastaveni/changelog',
        icon: CodeBracketIcon,
        current: false,
        slug: 'changelogs',
      },
      {
        name: 'Aktivity',
        link: '/nastaveni/aktivity',
        icon: ChartBarSquareIcon,
        current: false,
        slug: 'activities',
      },
      {
        name: 'Sazby DPH',
        link: '/nastaveni/dph',
        icon: DocumentCurrencyEuroIcon,
        current: false,
        slug: 'tax_rates',
      },
      {
        name: 'Jazyky',
        link: '/nastaveni/jazyky',
        icon: LanguageIcon,
        current: false,
        slug: 'languages',
      },
      {
        name: 'Země',
        link: '/nastaveni/zeme',
        icon: GlobeEuropeAfricaIcon,
        current: false,
        slug: 'countries',
      },
      {
        name: 'Měny',
        link: '/nastaveni/meny',
        icon: CurrencyEuroIcon,
        current: false,
        slug: 'currencies',
      },
      {
        name: 'E-maily',
        link: '/nastaveni/logy/emaily',
        icon: AtSymbolIcon,
        current: false,
        slug: 'emails',
      },
    ],
  },
]);

const userNavigation = [
  { name: 'Profil', link: '/profil' },
  { name: 'Rychlý přístup', link: '/profil/rychly-pristup' },
  { name: 'Odhlásit se', link: null, action: 'handleLogout' },
];

const quickAccess = ref([{ name: '', link: '/', target: null }]);

function isRouteActive(link: string | undefined | null, currentPath: string): boolean {
  if (!link) return false;
  if (link === '/') return currentPath === '/';

  return currentPath === link || currentPath.startsWith(link + '/');
}

watchEffect(() => {
  const currentPath = route.path;

  navigation.value.forEach((group) => {
    group.menu.forEach((item) => {
      let hasActiveSubmenu = false;

      if (item.submenu) {
        item.submenu.forEach((subItem) => {
          subItem.current = isRouteActive(subItem.link, currentPath);
          if (subItem.current) {
            hasActiveSubmenu = true;
          }
        });
      }

      item.current = hasActiveSubmenu || isRouteActive(item.link, currentPath);
    });
  });
});

function filterNavigationGroups(navigation: any[]): any[] {
  return navigation.filter((group: any) =>
    group.menu.some(
      (item: any) =>
        !item.slug ||
        (item.slug && canViewBySite(item.slug) && item.slug && canViewBySlug(item.slug)),
    ),
  );
}

function canViewBySlug(slug: string): boolean {
  if (user?.value && (user.value as any).user_group_id && userGroupStore.userGroups) {
    const userGroup = userGroupStore.userGroups.find(
      (group: any) => group.id === (user.value as any).user_group_id,
    );
    if (userGroup && userGroup.permissions) {
      const currentPermissionSlug = userGroup.permissions.find(
        (permission: any) => permission.slug === slug,
      );
      if (currentPermissionSlug && currentPermissionSlug.permissions.view === true) {
        return true;
      }
    }
  }
  return false;
}

function canViewBySite(slug: string): boolean {
  if (!user?.value?.sites?.length) {
    return true;
  }
  if (user?.value?.sites) {
    const currentSite = user.value.sites.find((site: any) => site.hash === selectedSiteHash.value);
    if (
      currentSite &&
      currentSite.settings &&
      currentSite.settings.enabled_modules &&
      currentSite.is_active
    ) {
      const currentModuleSlug = currentSite.settings.enabled_modules.find(
        (module: any) => module === slug,
      );
      if (currentModuleSlug) {
        return true;
      }
    }
  }
  return false;
}

function handleLogout() {
  logout();
  router.push('/login');
}

function getQuickAccess() {
  if (user?.value?.quick_access) {
    quickAccess.value = user.value.quick_access;
  }
}

watchEffect(() => {
  getQuickAccess();
});

const sitesForSelect = computed(() => {
  if (!user?.value?.sites) return [];
  return user.value.sites.map((site: any) => ({
    label: site.name,
    name: site.name,
    value: site.hash,
  }));
});

watch(
  () => selectedSiteHash.value,
  (newValue) => {
    // SSR safe check
    if (import.meta.client && newValue) {
      localStorage.setItem('selectedSiteHash', newValue);
    }
    refreshIdentity();
  },
);

onMounted(() => {
  refreshIdentity();
  getQuickAccess();

  if (
    import.meta.client &&
    !localStorage.getItem('selectedSiteHash') &&
    user?.value?.sites?.length > 0
  ) {
    localStorage.setItem('selectedSiteHash', user.value.sites[0].hash);
  }

  if (import.meta.client && localStorage.getItem('selectedSiteHash')) {
    selectedSiteHash.value = localStorage.getItem('selectedSiteHash') || '';
  }

  userGroupStore.fetchUserGroups();
  activityStore.fetchActivities();
  languageStore.fetchLanguages();
  countryStore.fetchCountries();
  currencyStore.fetchCurrencies();
  taxRateStore.fetchTaxRates();
  cashflowCategoryStore.fetchCategories();

  setTimeout(() => {
    navigation.value = filterNavigationGroups(navigation.value);
  }, 1000);

  setInterval(() => {
    refreshIdentity();
    if (!user?.value) {
      logout();
      router.push('/login');
    }
  }, 60000);
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans">
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="-translate-x-full"
          >
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
              >
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button
                    type="button"
                    class="-m-2.5 rounded-full p-2.5 transition-colors hover:bg-white/10"
                    @click="sidebarOpen = false"
                  >
                    <span class="sr-only">Zavřít menu</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>

              <div
                class="flex grow flex-col gap-y-6 overflow-y-auto bg-zinc-950 px-6 pb-6 shadow-2xl"
              >
                <div class="flex h-24 shrink-0 items-center justify-center border-b border-white/5">
                  <NuxtLink
                    to="https://web-pulse.cz"
                    target="_blank"
                    class="transition-transform hover:scale-105"
                  >
                    <img
                      class="h-10 w-auto"
                      src="/static/img/logo-gray-300.png"
                      alt="Your Company"
                    />
                  </NuxtLink>
                </div>
                <div v-if="user?.sites?.length" class="mt-2">
                  <BaseFormSelect
                    v-model="selectedSiteHash"
                    :options="sitesForSelect"
                    theme="dark"
                  />
                </div>
                <nav class="flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-8">
                    <li v-for="(group, index) in navigation" :key="index">
                      <div
                        class="mb-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500"
                      >
                        {{ group.title }}
                      </div>
                      <ul role="list" class="-mx-2 space-y-1.5">
                        <li
                          v-for="(item, key) in group.menu"
                          :key="key"
                          @click="sidebarOpen = false"
                        >
                          <NuxtLink
                            v-if="
                              (!item.slug ||
                                (item.slug &&
                                  canViewBySite(item.slug) &&
                                  canViewBySlug(item.slug))) &&
                              !item.submenu
                            "
                            :to="item.link"
                            :class="[
                              item.current
                                ? 'bg-zinc-800 text-white shadow-sm ring-1 ring-white/10'
                                : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                              'group flex gap-x-3 rounded-xl p-2.5 text-sm font-medium transition-all duration-200',
                            ]"
                          >
                            <component
                              :is="item.icon"
                              :class="[
                                item.current
                                  ? 'text-white'
                                  : 'text-zinc-500 group-hover:text-zinc-300',
                                'size-5 shrink-0 transition-colors',
                              ]"
                              aria-hidden="true"
                            />
                            <span class="truncate">{{ item.name }}</span>
                          </NuxtLink>

                          <Disclosure
                            v-else-if="
                              !item.slug ||
                              (item.slug && canViewBySite(item.slug) && canViewBySlug(item.slug))
                            "
                            v-slot="{ open }"
                            as="div"
                            class="w-full"
                          >
                            <DisclosureButton
                              :class="[
                                item.current
                                  ? 'bg-zinc-800 text-white shadow-sm ring-1 ring-white/10'
                                  : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                'group flex w-full items-center justify-between gap-x-3 rounded-xl p-2.5 text-sm font-medium transition-all duration-200',
                              ]"
                            >
                              <div class="flex items-center gap-x-3">
                                <component
                                  :is="item.icon"
                                  :class="[
                                    item.current
                                      ? 'text-white'
                                      : 'text-zinc-500 group-hover:text-zinc-300',
                                    'size-5 shrink-0 transition-colors',
                                  ]"
                                  aria-hidden="true"
                                />
                                {{ item.name }}
                              </div>
                              <ChevronRightIcon
                                :class="[
                                  open
                                    ? 'rotate-90 text-white'
                                    : 'text-zinc-500 group-hover:text-zinc-300',
                                  'size-4 shrink-0 transition-transform duration-200',
                                ]"
                                aria-hidden="true"
                              />
                            </DisclosureButton>
                            <Transition
                              enter-active-class="transition duration-200 ease-out"
                              enter-from-class="transform scale-95 opacity-0"
                              enter-to-class="transform scale-100 opacity-100"
                              leave-active-class="transition duration-75 ease-out"
                              leave-from-class="transform scale-100 opacity-100"
                              leave-to-class="transform scale-95 opacity-0"
                            >
                              <DisclosurePanel as="ul" class="mt-1 px-2">
                                <li
                                  v-for="subItem in item.submenu"
                                  :key="subItem.name"
                                  class="mt-1"
                                >
                                  <DisclosureButton as="div" class="w-full">
                                    <NuxtLink
                                      v-if="subItem.link"
                                      :to="subItem.link"
                                      :class="[
                                        subItem.current
                                          ? 'bg-zinc-800/80 text-white'
                                          : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                                        'group flex w-full cursor-pointer items-center gap-x-3 rounded-lg p-2 pl-10 text-sm font-medium transition-all duration-200',
                                      ]"
                                    >
                                      <div
                                        :class="[
                                          subItem.current ? 'bg-indigo-500' : 'bg-zinc-700',
                                          'h-1.5 w-1.5 rounded-full',
                                        ]"
                                      ></div>
                                      {{ subItem.name }}
                                    </NuxtLink>
                                  </DisclosureButton>
                                </li>
                              </DisclosurePanel>
                            </Transition>
                          </Disclosure>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <div
        class="flex grow flex-col gap-y-6 overflow-y-auto bg-zinc-950 px-6 pb-6 shadow-2xl ring-1 ring-white/5"
      >
        <div class="flex h-24 shrink-0 items-center justify-center border-b border-white/5">
          <NuxtLink
            to="https://web-pulse.cz"
            target="_blank"
            class="transition-transform hover:scale-105"
          >
            <img class="h-10 w-auto" src="/static/img/logo-gray-300.png" alt="Your Company" />
          </NuxtLink>
        </div>

        <div v-if="user?.sites?.length" class="mt-2">
          <BaseFormSelect v-model="selectedSiteHash" :options="sitesForSelect" theme="dark" />
        </div>

        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-8">
            <li v-for="(group, index) in navigation" :key="index">
              <div class="mb-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500">
                {{ group.title }}
              </div>
              <ul role="list" class="-mx-2 space-y-1.5">
                <li v-for="(item, key) in group.menu" :key="key">
                  <NuxtLink
                    v-if="
                      (!item.slug ||
                        (item.slug && canViewBySite(item.slug) && canViewBySlug(item.slug))) &&
                      !item.submenu
                    "
                    :to="item.link"
                    :class="[
                      item.current
                        ? 'bg-zinc-800 text-white shadow-sm ring-1 ring-white/10'
                        : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                      'group flex items-center gap-x-3 rounded-xl p-2.5 text-sm font-medium transition-all duration-200',
                    ]"
                  >
                    <component
                      :is="item.icon"
                      :class="[
                        item.current ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300',
                        'size-5 shrink-0 transition-colors',
                      ]"
                      aria-hidden="true"
                    />
                    <span class="truncate">{{ item.name }}</span>
                  </NuxtLink>

                  <Disclosure
                    v-else-if="
                      !item.slug ||
                      (item.slug && canViewBySite(item.slug) && canViewBySlug(item.slug))
                    "
                    v-slot="{ open }"
                    as="div"
                    class="w-full"
                  >
                    <DisclosureButton
                      :class="[
                        item.current
                          ? 'bg-zinc-800 text-white shadow-sm ring-1 ring-white/10'
                          : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                        'group flex w-full items-center justify-between gap-x-3 rounded-xl p-2.5 text-sm font-medium transition-all duration-200',
                      ]"
                    >
                      <div class="flex items-center gap-x-3">
                        <component
                          :is="item.icon"
                          :class="[
                            item.current ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300',
                            'size-5 shrink-0 transition-colors',
                          ]"
                          aria-hidden="true"
                        />
                        {{ item.name }}
                      </div>
                      <ChevronRightIcon
                        :class="[
                          open ? 'rotate-90 text-white' : 'text-zinc-500 group-hover:text-zinc-300',
                          'size-4 shrink-0 transition-transform duration-200',
                        ]"
                        aria-hidden="true"
                      />
                    </DisclosureButton>
                    <Transition
                      enter-active-class="transition duration-200 ease-out"
                      enter-from-class="transform scale-95 opacity-0"
                      enter-to-class="transform scale-100 opacity-100"
                      leave-active-class="transition duration-75 ease-out"
                      leave-from-class="transform scale-100 opacity-100"
                      leave-to-class="transform scale-95 opacity-0"
                    >
                      <DisclosurePanel as="ul" class="mt-1 px-2">
                        <li v-for="subItem in item.submenu" :key="subItem.name" class="mt-1">
                          <NuxtLink
                            v-if="subItem.link"
                            :to="subItem.link"
                            :class="[
                              subItem.current
                                ? 'bg-zinc-800/80 text-white'
                                : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
                              'group flex w-full items-center gap-x-3 rounded-lg p-2 pl-10 text-sm font-medium transition-all duration-200',
                            ]"
                          >
                            <div
                              :class="[
                                subItem.current ? 'bg-indigo-500' : 'bg-zinc-700',
                                'h-1.5 w-1.5 rounded-full',
                              ]"
                            ></div>
                            {{ subItem.name }}
                          </NuxtLink>
                        </li>
                      </DisclosurePanel>
                    </Transition>
                  </Disclosure>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div class="flex min-h-screen flex-col lg:pl-72">
      <header
        class="no-print sticky top-0 z-40 flex h-20 shrink-0 items-center gap-x-4 border-b border-slate-200 bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all sm:gap-x-6 sm:px-6 lg:px-8"
      >
        <button
          type="button"
          class="-m-2.5 p-2.5 text-slate-700 hover:text-slate-900 lg:hidden"
          @click="sidebarOpen = true"
        >
          <span class="sr-only">Otevřít menu</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>

        <div class="h-6 w-px bg-slate-200 lg:hidden" aria-hidden="true" />

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
          <div class="relative flex flex-1 items-center">
            <div class="relative w-full max-w-md">
              <label for="search-field" class="sr-only">Hledat</label>
              <MagnifyingGlassIcon
                class="pointer-events-none absolute inset-y-0 left-3 h-full w-4 text-slate-400"
                aria-hidden="true"
              />
              <input
                id="search-field"
                v-model="searchString"
                class="block w-full rounded-full border-0 bg-slate-100 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-inner transition-all placeholder:text-slate-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Hledat cokoliv..."
                type="search"
                name="search"
                autocomplete="off"
              />
            </div>
          </div>

          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <Menu v-if="quickAccess && quickAccess.length" as="div" class="relative">
              <MenuButton
                class="-m-1.5 flex items-center rounded-full p-1.5 transition-colors hover:bg-slate-100"
              >
                <span class="sr-only">Otevřít rychlý přístup</span>
                <span class="flex items-center px-2">
                  <span
                    class="hidden text-sm font-semibold text-slate-700 lg:block"
                    aria-hidden="true"
                    >Rychlý přístup</span
                  >
                  <ChevronDownIcon
                    class="ml-2 hidden size-4 text-slate-400 lg:block"
                    aria-hidden="true"
                  />
                  <StarIcon
                    class="size-5 text-amber-400 drop-shadow-sm lg:hidden"
                    aria-hidden="true"
                  />
                </span>
              </MenuButton>
              <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="transform opacity-0 scale-95 translate-y-1"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 translate-y-1"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-3 w-56 origin-top-right rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
                >
                  <MenuItem v-for="item in quickAccess" :key="item.name" v-slot="{ active }">
                    <NuxtLink
                      :to="item.link"
                      :target="item.target"
                      :class="[
                        active ? 'bg-slate-50 text-indigo-600' : 'text-slate-700',
                        'group flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition-colors',
                      ]"
                    >
                      <span>{{ item.name }}</span>
                      <ArrowTopRightOnSquareIcon
                        v-if="item.target === '_blank'"
                        class="ml-4 size-4 text-amber-500"
                        aria-hidden="true"
                      />
                    </NuxtLink>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>

            <div class="h-6 w-px bg-slate-200" aria-hidden="true" />

            <Menu as="div" class="relative">
              <MenuButton
                class="-m-1.5 flex items-center rounded-full p-1.5 transition-colors hover:bg-slate-100"
              >
                <span class="sr-only">Otevřít uživatelské menu</span>
                <span class="hidden px-2 lg:flex lg:items-center">
                  <div
                    class="mr-2 flex h-8 w-8 items-center justify-center rounded-full border border-indigo-200 bg-indigo-100 text-xs font-bold text-indigo-600"
                  >
                    {{ user?.firstname?.charAt(0) }}{{ user?.lastname?.charAt(0) }}
                  </div>
                  <span class="text-sm font-semibold text-slate-700" aria-hidden="true">
                    {{ user?.firstname }} {{ user?.lastname }}
                  </span>
                  <ChevronDownIcon class="ml-2 size-4 text-slate-400" aria-hidden="true" />
                </span>
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full border border-indigo-200 bg-indigo-100 text-xs font-bold text-indigo-600 lg:hidden"
                >
                  {{ user?.firstname?.charAt(0) || 'U' }}
                </div>
              </MenuButton>
              <transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="transform opacity-0 scale-95 translate-y-1"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 translate-y-1"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-3 w-48 origin-top-right rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
                >
                  <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                    <NuxtLink
                      v-if="item.link != null"
                      :to="item.link"
                      :class="[
                        active ? 'bg-slate-50 text-indigo-600' : 'text-slate-700',
                        'block rounded-xl px-3 py-2 text-sm font-medium transition-colors',
                      ]"
                    >
                      {{ item.name }}
                    </NuxtLink>
                    <button
                      v-else
                      :class="[
                        active ? 'bg-red-50 text-red-600' : 'text-slate-700',
                        'block w-full rounded-xl px-3 py-2 text-left text-sm font-medium transition-colors',
                      ]"
                      @click="handleLogout"
                    >
                      {{ item.name }}
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </header>

      <main class="flex-1 py-8">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
          <UiToastContainer />
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
