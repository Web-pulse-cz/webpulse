<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { ChevronRightIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import type { MenuLinkItem, MenuSectionDef } from '~/components/Layout/navigation';

const props = withDefaults(
  defineProps<{
    /** Sections with already permission-filtered items. */
    sections: MenuSectionDef[];
    /** Icon-only rail mode (desktop collapsed sidebar). */
    collapsed?: boolean;
    /** Show "Hledat v menu" filter input in expanded mode. */
    showFilter?: boolean;
    /** Unique prefix for generated element ids (mobile vs. desktop instance). */
    idPrefix?: string;
  }>(),
  { collapsed: false, showFilter: true, idPrefix: 'sidebar' },
);

const emit = defineEmits<{ navigate: [] }>();

// ---------------------------------------------------------------------------
// Collapsible section groups — shared between instances, persisted in localStorage
// ---------------------------------------------------------------------------
const GROUPS_STORAGE_KEY = 'sidebarCollapsedGroups';
const collapsedGroups = useState<string[]>('sidebar-collapsed-groups', () => []);

onMounted(() => {
  try {
    const raw = localStorage.getItem(GROUPS_STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    if (Array.isArray(parsed)) collapsedGroups.value = parsed.filter((k) => typeof k === 'string');
  } catch {
    // storage unavailable / corrupted — keep defaults
  }
});

function toggleGroup(key: string) {
  const set = new Set(collapsedGroups.value);
  if (set.has(key)) set.delete(key);
  else set.add(key);
  collapsedGroups.value = [...set];
  try {
    localStorage.setItem(GROUPS_STORAGE_KEY, JSON.stringify(collapsedGroups.value));
  } catch {
    // ignore
  }
}

function isGroupCollapsed(key: string): boolean {
  if (props.collapsed || isFiltering.value) return false;
  return collapsedGroups.value.includes(key);
}

function groupHasActive(group: MenuSectionDef): boolean {
  return group.menu.some((item) => item.current);
}

// ---------------------------------------------------------------------------
// Filter
// ---------------------------------------------------------------------------
const filter = ref('');

function normalize(value: string): string {
  return value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

const isFiltering = computed(() => !props.collapsed && normalize(filter.value) !== '');

const displayedSections = computed<MenuSectionDef[]>(() => {
  if (!isFiltering.value) return props.sections;
  const q = normalize(filter.value);

  return props.sections
    .map((section) => {
      const menu = section.menu
        .map((item) => {
          if (normalize(item.name).includes(q)) return item;
          const submenu = item.submenu?.filter((sub) => normalize(sub.name).includes(q));
          return submenu?.length ? { ...item, submenu } : null;
        })
        .filter((item): item is MenuLinkItem => item !== null);
      return { ...section, menu };
    })
    .filter((section) => section.menu.length > 0);
});

// ---------------------------------------------------------------------------
// Submenus (expanded mode)
// ---------------------------------------------------------------------------
const openItems = ref<Record<string, boolean>>({});

function isItemOpen(item: MenuLinkItem): boolean {
  if (isFiltering.value) return true;
  return openItems.value[item.key] ?? item.current;
}

function toggleItem(item: MenuLinkItem) {
  openItems.value = { ...openItems.value, [item.key]: !isItemOpen(item) };
}

function onNavigate() {
  emit('navigate');
}

// ---------------------------------------------------------------------------
// Flyout (collapsed mode) — teleported so it is not clipped by the scrolling sidebar
// ---------------------------------------------------------------------------
const flyoutItem = ref<MenuLinkItem | null>(null);
const flyoutPos = ref({ top: 0, left: 0 });
const flyoutPanel = ref<HTMLElement | null>(null);
let flyoutTrigger: HTMLElement | null = null;
let closeTimer: ReturnType<typeof setTimeout> | null = null;

function cancelClose() {
  if (closeTimer) clearTimeout(closeTimer);
  closeTimer = null;
}

function openFlyout(item: MenuLinkItem, trigger: HTMLElement) {
  cancelClose();
  flyoutTrigger = trigger;
  const rect = trigger.getBoundingClientRect();
  flyoutPos.value = {
    top: Math.max(8, Math.min(rect.top, window.innerHeight - 320)),
    left: rect.right + 8,
  };
  flyoutItem.value = item;
}

function closeFlyout(returnFocus = false) {
  cancelClose();
  flyoutItem.value = null;
  if (returnFocus) flyoutTrigger?.focus();
}

function scheduleClose() {
  cancelClose();
  closeTimer = setTimeout(() => closeFlyout(), 150);
}

async function openFlyoutAndFocus(item: MenuLinkItem, trigger: HTMLElement) {
  openFlyout(item, trigger);
  await nextTick();
  flyoutPanel.value?.querySelector<HTMLElement>('a')?.focus();
}

function onTriggerClick(item: MenuLinkItem, event: MouseEvent) {
  openFlyoutAndFocus(item, event.currentTarget as HTMLElement);
}

function onTriggerFocusOut(event: FocusEvent) {
  const next = event.relatedTarget as Node | null;
  if (next && flyoutPanel.value?.contains(next)) return;
  scheduleClose();
}

function onPanelFocusOut(event: FocusEvent) {
  const next = event.relatedTarget as Node | null;
  if (next && (flyoutPanel.value?.contains(next) || flyoutTrigger?.contains(next))) return;
  scheduleClose();
}

function onPanelKeydown(event: KeyboardEvent) {
  const links = Array.from(flyoutPanel.value?.querySelectorAll<HTMLElement>('a') ?? []);
  const index = links.indexOf(document.activeElement as HTMLElement);

  if (event.key === 'Escape' || event.key === 'ArrowLeft') {
    event.preventDefault();
    closeFlyout(true);
  } else if (event.key === 'ArrowDown') {
    event.preventDefault();
    links[(index + 1) % links.length]?.focus();
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    links[(index - 1 + links.length) % links.length]?.focus();
  } else if (event.key === 'Tab') {
    // Panel lives at the end of <body>: hand focus back to the trigger so Tab continues
    // through the sidebar in natural order.
    if (event.shiftKey) event.preventDefault();
    closeFlyout(true);
  }
}

watch(
  () => props.collapsed,
  (isCollapsed) => {
    if (!isCollapsed) closeFlyout();
  },
);

onBeforeUnmount(cancelClose);

// ---------------------------------------------------------------------------
// Shared classes
// ---------------------------------------------------------------------------
const itemBase =
  'group flex w-full items-center rounded-xl p-2.5 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400';

function itemClass(item: MenuLinkItem): string[] {
  return [
    item.current
      ? 'bg-zinc-800 text-white shadow-sm ring-1 ring-white/10'
      : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
    props.collapsed ? 'justify-center' : 'gap-x-3',
    itemBase,
  ];
}

function iconClass(active: boolean): string[] {
  return [
    active ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300',
    'size-5 shrink-0 transition-colors',
  ];
}

function subItemClass(active: boolean): string[] {
  return [
    active ? 'bg-zinc-800/80 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white',
    'group flex w-full items-center gap-x-3 rounded-lg p-2 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400',
  ];
}
</script>

<template>
  <nav class="flex flex-1 flex-col" aria-label="Hlavní menu">
    <div v-if="showFilter && !collapsed" class="relative mb-6">
      <label :for="`${idPrefix}-menu-filter`" class="sr-only">Hledat v menu</label>
      <MagnifyingGlassIcon
        class="pointer-events-none absolute inset-y-0 left-3 h-full w-4 text-zinc-500"
        aria-hidden="true"
      />
      <input
        :id="`${idPrefix}-menu-filter`"
        v-model="filter"
        type="text"
        autocomplete="off"
        placeholder="Hledat v menu"
        class="block w-full rounded-xl border-0 bg-zinc-900 py-2 pl-9 pr-8 text-sm text-zinc-100 ring-1 ring-white/10 placeholder:text-zinc-500 focus:ring-2 focus:ring-primary-500"
        @keydown.esc.prevent="filter = ''"
      />
      <button
        v-if="filter"
        type="button"
        class="absolute inset-y-0 right-1.5 my-auto flex size-6 items-center justify-center rounded-md text-zinc-500 hover:text-zinc-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
        @click="filter = ''"
      >
        <span class="sr-only">Vymazat hledání v menu</span>
        <XMarkIcon class="size-4" aria-hidden="true" />
      </button>
    </div>

    <p v-if="isFiltering && displayedSections.length === 0" class="px-1 text-sm text-zinc-500">
      Nic nenalezeno.
    </p>

    <ul role="list" class="flex flex-1 flex-col gap-y-6">
      <li v-for="group in displayedSections" :key="group.key">
        <!-- Group header -->
        <button
          v-if="!collapsed"
          type="button"
          class="label-caps group/header -mx-1 mb-2 flex w-[calc(100%+0.5rem)] items-center justify-between rounded-md px-1 py-1 text-zinc-500 transition-colors hover:text-zinc-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
          :aria-expanded="!isGroupCollapsed(group.key)"
          :aria-controls="`${idPrefix}-group-${group.key}`"
          @click="toggleGroup(group.key)"
        >
          <span class="flex items-center gap-2">
            {{ group.title }}
            <span
              v-if="isGroupCollapsed(group.key) && groupHasActive(group)"
              class="size-1.5 rounded-full bg-primary-500"
              aria-hidden="true"
            />
          </span>
          <ChevronRightIcon
            class="size-3.5 shrink-0 transition-transform duration-200"
            :class="isGroupCollapsed(group.key) ? '' : 'rotate-90'"
            aria-hidden="true"
          />
        </button>
        <div v-else class="mb-3 flex justify-center" aria-hidden="true">
          <span class="h-px w-6 bg-white/10" />
        </div>

        <ul
          v-show="!isGroupCollapsed(group.key)"
          :id="`${idPrefix}-group-${group.key}`"
          role="list"
          class="-mx-2 space-y-1.5"
        >
          <li v-for="item in group.menu" :key="item.key">
            <!-- Plain link -->
            <NuxtLink
              v-if="!item.submenu"
              :to="item.link"
              :title="collapsed ? item.name : undefined"
              :aria-current="item.current ? 'page' : undefined"
              :class="itemClass(item)"
              @click="onNavigate"
            >
              <component :is="item.icon" :class="iconClass(item.current)" aria-hidden="true" />
              <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
              <span v-else class="sr-only">{{ item.name }}</span>
            </NuxtLink>

            <!-- Submenu, collapsed rail: flyout trigger -->
            <button
              v-else-if="collapsed"
              type="button"
              :title="item.name"
              aria-haspopup="true"
              :aria-expanded="flyoutItem?.key === item.key"
              :class="itemClass(item)"
              @mouseenter="openFlyout(item, $event.currentTarget as HTMLElement)"
              @mouseleave="scheduleClose"
              @focus="openFlyout(item, $event.currentTarget as HTMLElement)"
              @focusout="onTriggerFocusOut"
              @click="onTriggerClick(item, $event)"
              @keydown.right.prevent="openFlyoutAndFocus(item, $event.currentTarget as HTMLElement)"
              @keydown.down.prevent="openFlyoutAndFocus(item, $event.currentTarget as HTMLElement)"
              @keydown.esc="closeFlyout()"
            >
              <component :is="item.icon" :class="iconClass(item.current)" aria-hidden="true" />
              <span class="sr-only">{{ item.name }}</span>
            </button>

            <!-- Submenu, expanded -->
            <template v-else>
              <button
                type="button"
                :class="[...itemClass(item), 'justify-between']"
                :aria-expanded="isItemOpen(item)"
                :aria-controls="`${idPrefix}-sub-${item.key}`"
                @click="toggleItem(item)"
              >
                <span class="flex min-w-0 items-center gap-x-3">
                  <component :is="item.icon" :class="iconClass(item.current)" aria-hidden="true" />
                  <span class="truncate">{{ item.name }}</span>
                </span>
                <ChevronRightIcon
                  :class="[
                    isItemOpen(item)
                      ? 'rotate-90 text-white'
                      : 'text-zinc-500 group-hover:text-zinc-300',
                    'size-4 shrink-0 transition-transform duration-200',
                  ]"
                  aria-hidden="true"
                />
              </button>
              <ul
                v-show="isItemOpen(item)"
                :id="`${idPrefix}-sub-${item.key}`"
                role="list"
                class="mt-1 space-y-1 px-2"
              >
                <li v-for="subItem in item.submenu" :key="subItem.link || subItem.name">
                  <NuxtLink
                    v-if="subItem.link"
                    :to="subItem.link"
                    :aria-current="subItem.current ? 'page' : undefined"
                    :class="[...subItemClass(subItem.current), 'pl-10']"
                    @click="onNavigate"
                  >
                    <span
                      :class="[
                        subItem.current ? 'bg-primary-500' : 'bg-zinc-700',
                        'size-1.5 shrink-0 rounded-full',
                      ]"
                      aria-hidden="true"
                    />
                    <span class="truncate">{{ subItem.name }}</span>
                  </NuxtLink>
                </li>
              </ul>
            </template>
          </li>
        </ul>
      </li>
    </ul>

    <ClientOnly>
      <Teleport to="body">
        <Transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="opacity-0 -translate-x-1"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="flyoutItem"
            ref="flyoutPanel"
            class="fixed z-[60] w-60 rounded-2xl bg-zinc-900 p-1.5 shadow-2xl ring-1 ring-white/10"
            :style="{ top: `${flyoutPos.top}px`, left: `${flyoutPos.left}px` }"
            role="menu"
            :aria-label="flyoutItem.name"
            @mouseenter="cancelClose"
            @mouseleave="scheduleClose"
            @focusout="onPanelFocusOut"
            @keydown="onPanelKeydown"
          >
            <div class="label-caps px-3 pb-1 pt-2 text-zinc-500">{{ flyoutItem.name }}</div>
            <template v-for="subItem in flyoutItem.submenu" :key="subItem.link || subItem.name">
              <NuxtLink
                v-if="subItem.link"
                :to="subItem.link"
                role="menuitem"
                :aria-current="subItem.current ? 'page' : undefined"
                :class="[...subItemClass(subItem.current), 'px-3']"
                @click="
                  closeFlyout();
                  onNavigate();
                "
              >
                <span
                  :class="[
                    subItem.current ? 'bg-primary-500' : 'bg-zinc-700',
                    'size-1.5 shrink-0 rounded-full',
                  ]"
                  aria-hidden="true"
                />
                <span class="truncate">{{ subItem.name }}</span>
              </NuxtLink>
            </template>
          </div>
        </Transition>
      </Teleport>
    </ClientOnly>
  </nav>
</template>
