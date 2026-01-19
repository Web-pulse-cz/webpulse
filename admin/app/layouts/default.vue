<script setup lang="ts">
import { ref, provide } from 'vue';
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
        name: 'Aktivity',
        link: '/aktivity',
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

watchEffect(() => {
  const currentPath = route.path;
  navigation.value.forEach((group) => {
    group.menu.forEach((item) => {
      item.current = currentPath === item.link;
    });
  });
});

function filterNavigationGroups(navigation: any[]): any[] {
  return navigation.filter((group: any) =>
    group.menu.some((item: any) => !item.slug || (item.slug && canViewBySlug(item.slug))),
  );
}

function canViewBySlug(slug: string): boolean {
  if (user && user.value && (user.value as any).user_group_id && userGroupStore.userGroups) {
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
  if(user && (!user.value.sites || !user.value.sites.length)) {
    return true;
  }
  if (user && user.value && user.value.sites) {
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
  if (user && user.value && user.value.quick_access) {
    quickAccess.value = user.value.quick_access;
  }
}
watchEffect(() => {
  getQuickAccess();
});

const sitesForSelect = computed(() => {
  return user.value.sites.map((site: any) => ({
    label: site.name,
    name: site.name,
    value: site.hash,
  }));
});

watch(
  () => selectedSiteHash.value,
  (newValue) => {
    localStorage.setItem('selectedSiteHash', newValue);
    refreshIdentity();
  },
);

onMounted(() => {
  refreshIdentity();
  getQuickAccess();

  if (
    !localStorage.getItem('selectedSiteHash') &&
    user.value.sites &&
    user.value.sites.length > 0
  ) {
    localStorage.setItem('selectedSiteHash', user.value.sites[0].hash);
  }

  if (localStorage.getItem('selectedSiteHash')) {
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
    if (!user) {
      logout();
      router.push('/login');
    }
  }, 60000);
});
</script>

<template>
  <div>
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-10 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-900/80" />
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
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <!-- Sidebar component, swap this element with another sidebar if you like -->
              <div
                class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4 ring-1 ring-white/10"
              >
                <div class="flex h-24 shrink-0 items-center justify-center">
                  <NuxtLink to="https://web-pulse.cz" target="_blank">
                    <img
                      class="h-8 w-auto"
                      src="/static/img/logo-gray-300.png"
                      alt="Your Company"
                    />
                  </NuxtLink>
                </div>
                <div v-if="user.sites && user.sites.length">
                  <BaseFormSelect
                    v-model="selectedSiteHash"
                    :options="sitesForSelect"
                    theme="dark"
                  />
                </div>
                <nav class="flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li v-for="(group, index) in navigation" :key="index">
                      <div class="text-xs/6 font-semibold text-gray-300">
                        {{ group.title }}
                      </div>
                      <ul role="list" class="-mx-2 mt-2 space-y-1">
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
                                ? 'bg-gray-800 text-white'
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                              'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                            ]"
                          >
                            <component :is="item.icon" class="size-6 shrink-0" aria-hidden="true" />
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
                                  ? 'bg-gray-800 text-white'
                                  : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                                'group flex w-full justify-between gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                              ]"
                            >
                              <div class="flex gap-x-3">
                                <component
                                  :is="item.icon"
                                  class="size-6 shrink-0"
                                  aria-hidden="true"
                                />
                                {{ item.name }}
                              </div>
                              <ChevronRightIcon
                                :class="[
                                  open ? 'rotate-90 text-gray-600' : 'text-gray-600',
                                  'size-6 shrink-0',
                                ]"
                                aria-hidden="true"
                              />
                            </DisclosureButton>
                            <DisclosurePanel as="ul" class="mt-1 px-2">
                              <li v-for="subItem in item.submenu" :key="subItem.name">
                                <DisclosureButton as="div" class="w-full">
                                  <NuxtLink
                                    v-if="subItem.link"
                                    :to="subItem.link"
                                    class="group flex w-full cursor-pointer gap-x-3 rounded-md p-2 pl-9 text-sm/6 font-semibold text-gray-600 hover:bg-gray-800 hover:text-white"
                                    >{{ subItem.name }}</NuxtLink
                                  >
                                </DisclosureButton>
                              </li>
                            </DisclosurePanel>
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

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-10 lg:flex lg:w-64 lg:flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4">
        <div class="flex h-24 shrink-0 items-center justify-center">
          <NuxtLink to="https://web-pulse.cz" target="_blank">
            <img class="h-12 w-auto" src="/static/img/logo-gray-300.png" alt="Your Company" />
          </NuxtLink>
        </div>
        <!-- site select -->
        <div v-if="user.sites && user.sites.length">
          <BaseFormSelect v-model="selectedSiteHash" :options="sitesForSelect" theme="dark" />
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li v-for="(group, index) in navigation" :key="index">
              <div class="text-xs/6 font-semibold text-gray-300">
                {{ group.title }}
              </div>
              <ul role="list" class="-mx-2 mt-2 space-y-1">
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
                        ? 'bg-gray-800 text-white'
                        : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                      'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                    ]"
                  >
                    <component :is="item.icon" class="size-6 shrink-0" aria-hidden="true" />
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
                          ? 'bg-gray-800 text-white'
                          : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                        'group flex w-full justify-between gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                      ]"
                    >
                      <div class="flex gap-x-3">
                        <component :is="item.icon" class="size-6 shrink-0" aria-hidden="true" />
                        {{ item.name }}
                      </div>
                      <ChevronRightIcon
                        :class="[
                          open ? 'rotate-90 text-gray-600' : 'text-gray-600',
                          'size-6 shrink-0',
                        ]"
                        aria-hidden="true"
                      />
                    </DisclosureButton>
                    <DisclosurePanel as="ul" class="mt-1 px-2">
                      <li v-for="subItem in item.submenu" :key="subItem.name">
                        <DisclosureButton as="div" class="w-full">
                          <NuxtLink
                            v-if="subItem.link"
                            :to="subItem.link"
                            class="group flex w-full cursor-pointer gap-x-3 rounded-md p-2 pl-9 text-sm/6 font-semibold text-gray-600 hover:bg-gray-800 hover:text-white"
                            >{{ subItem.name }}</NuxtLink
                          >
                        </DisclosureButton>
                      </li>
                    </DisclosurePanel>
                  </Disclosure>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div class="lg:pl-64">
      <div
        class="no-print sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8"
      >
        <button
          type="button"
          class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
          @click="sidebarOpen = true"
        >
          <span class="sr-only">Menu</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>

        <!-- Separator -->
        <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true" />

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
          <div class="relative flex flex-1">
            <label for="search-field" class="sr-only">Hledat</label>
            <MagnifyingGlassIcon
              class="pointer-events-none absolute inset-y-0 left-0 h-full w-3 text-grayCustom lg:w-5"
              aria-hidden="true"
            />
            <input
              id="search-field"
              v-model="searchString"
              class="block size-full border-0 py-0 pl-6 pr-0 text-xs text-grayDark placeholder:text-grayCustom focus:ring-0 lg:pl-8 lg:text-sm"
              placeholder="Hledat..."
              type="search"
              name="search"
              autocomplete="none"
            />
          </div>
          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <!--						<button
							type="button"
							class="-m-2.5 p-2.5 text-gray-300 hover:text-gray-500"
						>
							<span class="sr-only">View notifications</span>
							<BellIcon
								class="size-6"
								aria-hidden="true"
							/>
						</button> -->

            <!-- Separator -->
            <div
              v-if="quickAccess && quickAccess.length"
              class="block lg:h-6 lg:w-px lg:bg-gray-900/10"
              aria-hidden="true"
            />

            <!-- Quick access dropdown -->
            <Menu v-if="quickAccess && quickAccess.length" as="div" class="relative">
              <MenuButton class="-m-1.5 flex items-center p-1.5">
                <span class="sr-only">Open qick access menu</span>
                <span class="flex lg:items-center">
                  <span
                    class="hidden text-sm/6 font-semibold text-gray-900 lg:block"
                    aria-hidden="true"
                    >Rychlý přístup</span
                  >
                  <ChevronDownIcon
                    class="ml-2 hidden size-5 text-gray-300 lg:block"
                    aria-hidden="true"
                  />
                  <StarIcon class="ml-2 size-5 text-yellow-600 lg:hidden" aria-hidden="true" />
                </span>
              </MenuButton>
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-2.5 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                >
                  <MenuItem v-for="item in quickAccess" :key="item.name" v-slot="{ active }">
                    <NuxtLink
                      :to="item.link"
                      :target="item.target"
                      :class="[
                        active ? 'bg-gray-50 outline-none' : '',
                        'block flex flex-grow items-center justify-between px-3 py-1 text-sm/6 text-gray-900',
                      ]"
                    >
                      <span>{{ item.name }}</span>
                      <ArrowTopRightOnSquareIcon
                        v-if="item.target === '_blank'"
                        class="ml-4 size-4 text-warning"
                        aria-hidden="true"
                      />
                    </NuxtLink>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
            <!-- Separator -->
            <div class="h-6 w-px bg-gray-900/10" aria-hidden="true" />
            <!-- Profile dropdown -->
            <Menu as="div" class="relative">
              <MenuButton class="-m-1.5 flex items-center p-1.5">
                <span class="sr-only">Open user menu</span>
                <!--                <img
                  class="size-8 rounded-full bg-gray-50"
                  :src="'http://api.chpp.cz/content/images/user/icon/' + user.avatar"
                  alt=""
                /> -->
                <span class="hidden lg:flex lg:items-center">
                  <span class="text-sm/6 font-semibold text-gray-900" aria-hidden="true"
                    >{{ user.firstname }} {{ user.lastname }}</span
                  >
                  <ChevronDownIcon class="ml-2 size-5 text-gray-300" aria-hidden="true" />
                </span>
              </MenuButton>
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                >
                  <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                    <NuxtLink
                      v-if="item.link != null"
                      :to="item.link"
                      :class="[
                        active ? 'bg-gray-50 outline-none' : '',
                        'block px-3 py-1 text-sm/6 text-gray-900',
                      ]"
                      >{{ item.name }}
                    </NuxtLink>
                    <span
                      v-else
                      :class="[
                        active ? 'bg-gray-50 outline-none' : '',
                        'block cursor-pointer px-3 py-1 text-sm/6 text-gray-900',
                      ]"
                      @click="handleLogout"
                      >{{ item.name }}</span
                    >
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>

      <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
          <UiToastContainer />
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
