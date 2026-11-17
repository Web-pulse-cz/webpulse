<script setup lang="ts">
import { ref, provide, computed, watch, watchEffect, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';
import {
  Bars3Icon,
  XMarkIcon,
  ArrowTopRightOnSquareIcon,
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon,
  GlobeAltIcon,
} from '@heroicons/vue/24/outline';
import { ChevronDownIcon, MagnifyingGlassIcon, StarIcon } from '@heroicons/vue/24/solid';
import { menuSections } from '~/components/Layout/navigation';
import { applyMenuOrder, useMenuOrder } from '~/composables/useMenuOrder';
import { useActivityStore } from '~/../stores/activityStore';
import { useLanguageStore } from '~/../stores/languageStore';
import { useCountryStore } from '~/../stores/countryStore';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';
import { useCashflowCategoryStore } from '~/../stores/cashflowCategoryStore';

const activityStore = useActivityStore();
const languageStore = useLanguageStore();
const countryStore = useCountryStore();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();
const cashflowCategoryStore = useCashflowCategoryStore();

const route = useRoute();
const user = useSanctumUser();
const { logout, refreshIdentity } = useSanctumAuth();
const sidebarOpen = ref(false);

const sidebarCollapsed = ref(
  import.meta.client ? localStorage.getItem('sidebarCollapsed') === '1' : false,
);
const sidebarHovered = ref(false);
const isSidebarExpanded = computed(() => !sidebarCollapsed.value || sidebarHovered.value);

function toggleSidebarCollapsed() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  if (import.meta.client) {
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? '1' : '0');
  }
}

const searchString = ref('');
provide('searchString', searchString);

// Header search filters the current list page only — reset it when leaving the page.
watch(
  () => route.path,
  () => {
    searchString.value = '';
  },
);

const commandPaletteOpen = ref(false);
const isMac = ref(false);

const selectedSiteHash = ref(
  import.meta.client ? localStorage.getItem('selectedSiteHash') || '' : '',
);
provide('selectedSiteHash', selectedSiteHash);

const navigation = ref(menuSections);

const { load: loadMenuOrder } = useMenuOrder(selectedSiteHash);

async function reloadMenuOrder() {
  const rows = await loadMenuOrder();
  navigation.value = applyMenuOrder(menuSections, rows);
}

watch(selectedSiteHash, reloadMenuOrder, { immediate: true });

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
  if (!route?.path) return;
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

const { canView, moduleBelongsToSite } = usePermissions(selectedSiteHash);

function isMenuItemVisible(item: any): boolean {
  if (item.onlyUserId && user?.value?.id !== item.onlyUserId) return false;
  return !item.slug || (moduleBelongsToSite(item.slug) && canView(item.slug));
}

// Sections containing only items the user may see (permissions, site modules, onlyUserId).
const visibleNavigation = computed(() =>
  navigation.value
    .map((group) => ({ ...group, menu: group.menu.filter(isMenuItemVisible) }))
    .filter((group) => group.menu.length > 0),
);

async function handleLogout() {
  try {
    await logout();
  } catch {
    // Token už API neuznává – identita je stejně pryč, jen přesměrujeme.
  }
  await navigateTo('/login');
}

// Periodické ověření identity. Dřív se volalo refreshIdentity() bez await a hned
// se kontroloval user – když byl user zrovna null (např. po 401 z jiného requestu),
// zavolal se logout(), který vyhodil "User is not authenticated" (token se nesmazal),
// a router.push('/login'). Rozběhnutý refreshIdentity pak usera vrátil zpět →
// uživatel stál na /login jako přihlášený a login() padal na "already authenticated".
// Interval se navíc nikdy nečistil, takže běžel dál i na login stránce.
let identityTimer: ReturnType<typeof setInterval> | undefined;

async function checkIdentity() {
  try {
    await refreshIdentity();
  } catch (error) {
    const status = (error as { response?: { status?: number } })?.response?.status;
    // Odhlásit jen při skutečně neplatném tokenu; síťové chyby / 5xx session nechají být.
    if (status === 401 || status === 419) {
      if (identityTimer) clearInterval(identityTimer);
      user.value = null;
      await navigateTo('/login');
    }
  }
}

function getQuickAccess() {
  if (user?.value?.quick_access) {
    quickAccess.value = user.value.quick_access;
  }
}

watchEffect(() => {
  getQuickAccess();
});

interface SiteOption {
  label: string;
  name: string;
  value: string;
}

const sitesForSelect = computed<SiteOption[]>(() => {
  if (!user?.value?.sites) return [];
  return user.value.sites.map((site: { name: string; hash: string }) => ({
    label: site.name,
    name: site.name,
    value: site.hash,
  }));
});

const selectedSiteName = computed(() => {
  const site = sitesForSelect.value.find((s) => s.value === selectedSiteHash.value);
  return site?.name || 'Vyberte stránku';
});

const selectedSiteInitials = computed(() => {
  const site = sitesForSelect.value.find((s) => s.value === selectedSiteHash.value);
  if (!site?.name) return '?';
  const words = String(site.name)
    .replace(/^https?:\/\//, '')
    .split(/[\s.\-_]+/)
    .filter(Boolean);
  const [first = '?', second] = words;
  const initials = second ? `${first.charAt(0)}${second.charAt(0)}` : first.slice(0, 2);
  return initials.toUpperCase();
});

function selectSite(hash: string) {
  selectedSiteHash.value = hash;
}

const selectedSiteUrl = computed(() => {
  const site = user?.value?.sites?.find((s: any) => s.hash === selectedSiteHash.value);
  if (!site?.url) return 'https://web-pulse.cz';
  return `${site.is_secure ? 'https' : 'http'}://${site.url}`;
});

watch(
  () => selectedSiteHash.value,
  (newValue) => {
    // SSR safe check
    if (import.meta.client && newValue) {
      localStorage.setItem('selectedSiteHash', newValue);
    }
    checkIdentity();
  },
);

// Auto-select first assigned site when user loads without a valid selection
// (first login, or stored hash belongs to a site the user is not assigned to)
watch(
  () => user?.value?.sites,
  (sites: any[] | undefined) => {
    if (!import.meta.client || !sites?.length) return;
    const isValid = sites.some((s: any) => s.hash === selectedSiteHash.value);
    if (!selectedSiteHash.value || !isValid) {
      selectedSiteHash.value = sites[0].hash;
    }
  },
  { immediate: true },
);

onMounted(() => {
  isMac.value = /Mac|iPhone|iPad/i.test(navigator.platform || navigator.userAgent);
  checkIdentity();
  getQuickAccess();

  activityStore.fetchActivities();
  languageStore.fetchLanguages();
  countryStore.fetchCountries();
  currencyStore.fetchCurrencies();
  taxRateStore.fetchTaxRates();
  cashflowCategoryStore.fetchCategories();

  identityTimer = setInterval(checkIdentity, 60000);
});

onBeforeUnmount(() => {
  if (identityTimer) clearInterval(identityTimer);
});
</script>

<template>
  <div class="min-h-screen bg-background font-sans">
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
                    :to="selectedSiteUrl"
                    target="_blank"
                    class="transition-transform hover:scale-105"
                  >
                    <img class="h-16 w-auto" src="/static/img/logo-gray-300.png" alt="Web Pulse" />
                    <span class="sr-only">(otevře se v novém okně)</span>
                  </NuxtLink>
                </div>
                <LayoutSidebarNav
                  :sections="visibleNavigation"
                  id-prefix="sidebar-mobile"
                  @navigate="sidebarOpen = false"
                />
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <div
      class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col lg:transition-all lg:duration-200"
      :class="isSidebarExpanded ? 'lg:w-72' : 'lg:w-20'"
      @mouseenter="sidebarCollapsed && (sidebarHovered = true)"
      @mouseleave="sidebarHovered = false"
    >
      <div
        class="flex grow flex-col gap-y-6 overflow-y-auto bg-zinc-950 pb-6 ring-1 ring-white/5 transition-shadow duration-200"
        :class="[
          isSidebarExpanded ? 'px-6' : 'px-3',
          sidebarCollapsed && sidebarHovered
            ? 'shadow-[8px_0_48px_-4px_rgba(0,0,0,0.55)]'
            : 'shadow-2xl',
        ]"
      >
        <div
          class="relative flex h-32 shrink-0 items-center justify-center border-b border-white/5"
        >
          <NuxtLink
            :to="selectedSiteUrl"
            target="_blank"
            class="transition-transform hover:scale-105"
          >
            <img
              v-if="isSidebarExpanded"
              class="h-16 w-auto"
              src="/static/img/logo-gray-300.png"
              alt="Web Pulse"
            />
            <img v-else class="size-10" src="/static/img/icon/logo-gray-300.png" alt="Web Pulse" />
            <span class="sr-only">(otevře se v novém okně)</span>
          </NuxtLink>
        </div>

        <LayoutSidebarNav
          class="mt-2"
          :sections="visibleNavigation"
          :collapsed="!isSidebarExpanded"
          id-prefix="sidebar-desktop"
        />

        <div class="shrink-0 border-t border-white/5 pt-3">
          <button
            type="button"
            class="group flex w-full items-center rounded-xl p-2.5 text-sm font-medium text-zinc-400 transition-all duration-200 hover:bg-zinc-800/50 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
            :class="isSidebarExpanded ? 'justify-start gap-x-3' : 'justify-center'"
            :title="sidebarCollapsed ? 'Rozbalit menu' : 'Sbalit menu'"
            :aria-pressed="sidebarCollapsed"
            @click="toggleSidebarCollapsed"
          >
            <ChevronDoubleRightIcon
              v-if="sidebarCollapsed"
              class="size-5 shrink-0 text-zinc-500 transition-colors group-hover:text-zinc-300"
              aria-hidden="true"
            />
            <ChevronDoubleLeftIcon
              v-else
              class="size-5 shrink-0 text-zinc-500 transition-colors group-hover:text-zinc-300"
              aria-hidden="true"
            />
            <span v-if="isSidebarExpanded">{{
              sidebarCollapsed ? 'Rozbalit menu' : 'Sbalit menu'
            }}</span>
            <span v-else class="sr-only">Rozbalit menu</span>
          </button>
        </div>
      </div>
    </div>

    <div
      class="flex min-h-screen flex-col transition-all duration-200"
      :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
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
          <div class="relative flex flex-1 items-center gap-x-3">
            <div class="relative w-full max-w-md">
              <label for="search-field" class="sr-only">Hledat v seznamu</label>
              <MagnifyingGlassIcon
                class="pointer-events-none absolute inset-y-0 left-3 h-full w-4 text-slate-400"
                aria-hidden="true"
              />
              <input
                id="search-field"
                v-model="searchString"
                class="block w-full rounded-full border-0 bg-slate-100 py-2 pl-10 pr-4 text-sm text-slate-900 shadow-inner transition-all placeholder:text-slate-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Hledat v seznamu…"
                type="search"
                name="search"
                autocomplete="off"
              />
            </div>

            <button
              type="button"
              class="hidden shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium text-slate-500 ring-1 ring-slate-200 transition-colors hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 md:flex"
              :aria-keyshortcuts="isMac ? 'Meta+K' : 'Control+K'"
              @click="commandPaletteOpen = true"
            >
              <span>Přejít na…</span>
              <kbd
                class="rounded-md bg-slate-100 px-1.5 py-0.5 font-sans text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                >{{ isMac ? '⌘' : 'Ctrl' }} K</kbd
              >
            </button>
          </div>

          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <Menu v-if="sitesForSelect.length > 0" as="div" class="relative">
              <MenuButton
                class="-m-1.5 flex items-center rounded-full p-1.5 transition-colors hover:bg-slate-100"
              >
                <span class="sr-only">Vybrat stránku, aktuálně: </span>
                <span class="flex items-center px-2">
                  <GlobeAltIcon class="size-5 text-primary-500" aria-hidden="true" />
                  <span
                    class="ml-1.5 flex h-6 min-w-6 items-center justify-center rounded-md bg-primary-50 px-1 text-xs font-bold text-primary-700 ring-1 ring-primary-100 lg:hidden"
                    :title="selectedSiteName"
                    aria-hidden="true"
                    >{{ selectedSiteInitials }}</span
                  >
                  <span class="sr-only lg:hidden">{{ selectedSiteName }}</span>
                  <span
                    class="ml-2 hidden max-w-[160px] truncate text-sm font-semibold text-slate-700 lg:block"
                    >{{ selectedSiteName }}</span
                  >
                  <ChevronDownIcon
                    class="ml-2 hidden size-4 text-slate-400 lg:block"
                    aria-hidden="true"
                  />
                </span>
              </MenuButton>
              <LayoutDropdownPanel width="w-60">
                <div class="label-caps px-3 py-2 text-slate-400">Vyberte stránku</div>
                <MenuItem v-for="site in sitesForSelect" :key="site.value" v-slot="{ active }">
                  <button
                    :class="[
                      active ? 'bg-slate-50 text-primary-600' : 'text-slate-700',
                      selectedSiteHash === site.value
                        ? 'bg-primary-50 font-semibold text-primary-600'
                        : '',
                      'group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors',
                    ]"
                    :aria-current="selectedSiteHash === site.value ? 'true' : undefined"
                    @click="selectSite(site.value)"
                  >
                    <span
                      :class="[
                        selectedSiteHash === site.value ? 'bg-primary-500' : 'bg-slate-300',
                        'size-2 shrink-0 rounded-full',
                      ]"
                      aria-hidden="true"
                    />
                    <span class="truncate">{{ site.name }}</span>
                  </button>
                </MenuItem>
              </LayoutDropdownPanel>
            </Menu>

            <div
              v-if="sitesForSelect.length > 0"
              class="h-6 w-px bg-slate-200"
              aria-hidden="true"
            />

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
              <LayoutDropdownPanel width="w-56">
                <MenuItem v-for="item in quickAccess" :key="item.name" v-slot="{ active }">
                  <NuxtLink
                    :to="item.link"
                    :target="item.target"
                    :class="[
                      active ? 'bg-slate-50 text-primary-600' : 'text-slate-700',
                      'group flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition-colors',
                    ]"
                  >
                    <span>{{ item.name }}</span>
                    <template v-if="item.target === '_blank'">
                      <ArrowTopRightOnSquareIcon
                        class="ml-4 size-4 text-amber-500"
                        aria-hidden="true"
                      />
                      <span class="sr-only">(otevře se v novém okně)</span>
                    </template>
                  </NuxtLink>
                </MenuItem>
              </LayoutDropdownPanel>
            </Menu>

            <div class="h-6 w-px bg-slate-200" aria-hidden="true" />

            <Menu as="div" class="relative">
              <MenuButton
                class="-m-1.5 flex items-center rounded-full p-1.5 transition-colors hover:bg-slate-100"
              >
                <span class="sr-only">Otevřít uživatelské menu</span>
                <span class="hidden px-2 lg:flex lg:items-center">
                  <div
                    class="mr-2 flex h-8 w-8 items-center justify-center rounded-full border border-primary-200 bg-primary-100 text-xs font-bold text-primary-600"
                  >
                    {{ user?.firstname?.charAt(0) }}{{ user?.lastname?.charAt(0) }}
                  </div>
                  <span class="text-sm font-semibold text-slate-700" aria-hidden="true">
                    {{ user?.firstname }} {{ user?.lastname }}
                  </span>
                  <ChevronDownIcon class="ml-2 size-4 text-slate-400" aria-hidden="true" />
                </span>
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full border border-primary-200 bg-primary-100 text-xs font-bold text-primary-600 lg:hidden"
                >
                  {{ user?.firstname?.charAt(0) || 'U' }}
                </div>
              </MenuButton>
              <LayoutDropdownPanel width="w-48">
                <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                  <NuxtLink
                    v-if="item.link != null"
                    :to="item.link"
                    :class="[
                      active ? 'bg-slate-50 text-primary-600' : 'text-slate-700',
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
              </LayoutDropdownPanel>
            </Menu>
          </div>
        </div>
      </header>

      <main class="flex-1 py-8">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>

    <LayoutCommandPalette
      v-model:open="commandPaletteOpen"
      :sections="visibleNavigation"
      :quick-access="quickAccess"
      :sites="sitesForSelect"
      :selected-site-hash="selectedSiteHash"
      @select-site="selectSite"
    />

    <UiToastContainer />
  </div>
</template>
