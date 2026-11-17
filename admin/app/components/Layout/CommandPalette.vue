<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch, type Component } from 'vue';
import {
  Combobox,
  ComboboxInput,
  ComboboxOption,
  ComboboxOptions,
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';
import {
  ArrowTopRightOnSquareIcon,
  GlobeAltIcon,
  MagnifyingGlassIcon,
  StarIcon,
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/20/solid';
import type { MenuSectionDef } from '~/components/Layout/navigation';

interface QuickAccessItem {
  name: string;
  link: string;
  target?: string | null;
}

interface SiteOption {
  name: string;
  value: string;
}

type PaletteItem =
  | {
      id: string;
      kind: 'link';
      label: string;
      hint?: string;
      link: string;
      external: boolean;
      icon?: Component;
    }
  | { id: string; kind: 'site'; label: string; hint?: string; value: string };

interface PaletteGroup {
  key: string;
  title: string;
  items: PaletteItem[];
}

const props = withDefaults(
  defineProps<{
    /** Permission-filtered menu sections (same data the sidebar renders). */
    sections: MenuSectionDef[];
    quickAccess?: QuickAccessItem[];
    sites?: SiteOption[];
    selectedSiteHash?: string;
  }>(),
  { quickAccess: () => [], sites: () => [], selectedSiteHash: '' },
);

const emit = defineEmits<{ 'select-site': [hash: string] }>();

const open = defineModel<boolean>('open', { default: false });
const query = ref('');

function normalize(value: string): string {
  return value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

const menuItems = computed<PaletteItem[]>(() => {
  const items: PaletteItem[] = [];
  for (const section of props.sections) {
    for (const item of section.menu) {
      if (item.link && !item.submenu) {
        items.push({
          id: `menu:${item.key}`,
          kind: 'link',
          label: item.name,
          hint: section.title,
          link: item.link,
          external: false,
          icon: item.icon,
        });
      }
      for (const sub of item.submenu ?? []) {
        if (!sub.link) continue;
        items.push({
          id: `menu:${item.key}:${sub.link}`,
          kind: 'link',
          label: sub.name,
          hint: `${section.title} › ${item.name}`,
          link: sub.link,
          external: false,
          icon: item.icon,
        });
      }
    }
  }
  return items;
});

const quickAccessItems = computed<PaletteItem[]>(() =>
  props.quickAccess
    .filter((item) => item?.name && item?.link)
    .map((item, index) => ({
      id: `quick:${index}:${item.link}`,
      kind: 'link' as const,
      label: item.name,
      hint: 'Rychlý přístup',
      link: item.link,
      external: item.target === '_blank' || /^https?:\/\//.test(item.link),
    })),
);

const siteItems = computed<PaletteItem[]>(() =>
  props.sites.map((site) => ({
    id: `site:${site.value}`,
    kind: 'site' as const,
    label: site.name,
    hint: site.value === props.selectedSiteHash ? 'Aktuální stránka' : 'Přepnout stránku',
    value: site.value,
  })),
);

function matches(item: PaletteItem, q: string): boolean {
  return normalize(`${item.label} ${item.hint ?? ''}`).includes(q);
}

const groups = computed<PaletteGroup[]>(() => {
  const q = normalize(query.value);
  const all: PaletteGroup[] = [
    { key: 'quick', title: 'Rychlý přístup', items: quickAccessItems.value },
    { key: 'menu', title: 'Menu', items: menuItems.value },
    { key: 'sites', title: 'Stránky', items: siteItems.value },
  ];
  return all
    .map((group) => ({
      ...group,
      items: q ? group.items.filter((item) => matches(item, q)) : group.items,
    }))
    .filter((group) => group.items.length > 0);
});

function onQueryInput(event: Event) {
  query.value = (event.target as HTMLInputElement).value;
}

const hasResults = computed(() => groups.value.length > 0);

function onSelect(item: PaletteItem | null) {
  if (!item) return;
  open.value = false;

  if (item.kind === 'site') {
    emit('select-site', item.value);
    return;
  }
  if (item.external) {
    window.open(item.link, '_blank', 'noopener');
    return;
  }
  navigateTo(item.link);
}

watch(open, (isOpen) => {
  if (isOpen) query.value = '';
});

function onGlobalKeydown(event: KeyboardEvent) {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    open.value = !open.value;
  }
}

onMounted(() => window.addEventListener('keydown', onGlobalKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onGlobalKeydown));
</script>

<template>
  <TransitionRoot :show="open" as="template" appear @after-leave="query = ''">
    <Dialog class="relative z-[70]" @close="open = false">
      <TransitionChild
        as="template"
        enter="ease-out duration-200"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-150"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" aria-hidden="true" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6 md:p-20">
        <TransitionChild
          as="template"
          enter="ease-out duration-200"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-150"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="mx-auto max-w-xl transform overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 transition-all"
          >
            <DialogTitle class="sr-only">Rychlé hledání</DialogTitle>
            <Combobox @update:model-value="onSelect">
              <div class="relative">
                <MagnifyingGlassIcon
                  class="pointer-events-none absolute left-4 top-3.5 size-5 text-slate-400"
                  aria-hidden="true"
                />
                <ComboboxInput
                  class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0"
                  placeholder="Přejít na stránku, sekci nebo web…"
                  autocomplete="off"
                  @change="onQueryInput"
                />
              </div>

              <ComboboxOptions
                v-if="hasResults"
                static
                class="max-h-96 scroll-py-2 overflow-y-auto border-t border-slate-100 p-2"
              >
                <li v-for="group in groups" :key="group.key" class="pb-2 last:pb-0">
                  <h2 class="label-caps px-3 py-2 text-slate-400">{{ group.title }}</h2>
                  <ul class="text-sm text-slate-700">
                    <ComboboxOption
                      v-for="item in group.items"
                      :key="item.id"
                      v-slot="{ active }"
                      :value="item"
                      as="template"
                    >
                      <li
                        class="flex cursor-pointer select-none items-center gap-3 rounded-xl px-3 py-2"
                        :class="active ? 'bg-primary-50 text-primary-700' : ''"
                      >
                        <GlobeAltIcon
                          v-if="item.kind === 'site'"
                          class="size-5 shrink-0"
                          :class="active ? 'text-primary-600' : 'text-slate-400'"
                          aria-hidden="true"
                        />
                        <StarIcon
                          v-else-if="group.key === 'quick'"
                          class="size-5 shrink-0"
                          :class="active ? 'text-amber-500' : 'text-amber-400'"
                          aria-hidden="true"
                        />
                        <component
                          :is="item.icon"
                          v-else-if="item.kind === 'link' && item.icon"
                          class="size-5 shrink-0"
                          :class="active ? 'text-primary-600' : 'text-slate-400'"
                          aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1 truncate font-medium">{{ item.label }}</span>
                        <span
                          v-if="item.hint"
                          class="hidden max-w-[45%] truncate text-xs text-slate-400 sm:block"
                          >{{ item.hint }}</span
                        >
                        <CheckIcon
                          v-if="item.kind === 'site' && item.value === selectedSiteHash"
                          class="size-4 shrink-0 text-primary-600"
                          aria-hidden="true"
                        />
                        <template v-if="item.kind === 'link' && item.external">
                          <ArrowTopRightOnSquareIcon
                            class="size-4 shrink-0 text-slate-400"
                            aria-hidden="true"
                          />
                          <span class="sr-only">(otevře se v novém okně)</span>
                        </template>
                      </li>
                    </ComboboxOption>
                  </ul>
                </li>
              </ComboboxOptions>

              <p
                v-else
                class="border-t border-slate-100 px-6 py-10 text-center text-sm text-slate-500"
              >
                Nic nenalezeno pro „{{ query }}“.
              </p>

              <div
                class="flex items-center gap-4 border-t border-slate-100 bg-slate-50 px-4 py-2.5 text-xs text-slate-500"
              >
                <span><kbd class="font-sans font-semibold">↑↓</kbd> pohyb</span>
                <span><kbd class="font-sans font-semibold">Enter</kbd> otevřít</span>
                <span><kbd class="font-sans font-semibold">Esc</kbd> zavřít</span>
              </div>
            </Combobox>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
