<script setup lang="ts">
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';

import {
  BoltIcon,
  MagnifyingGlassIcon,
  TrashIcon,
  ClipboardDocumentIcon,
  DocumentDuplicateIcon,
  CheckIcon,
  XMarkIcon,
  ArrowDownTrayIcon,
  Cog6ToothIcon,
  ArrowPathIcon,
  InboxIcon,
  FunnelIcon,
  PlusIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import {
  ChevronDownIcon,
  ChevronUpIcon,
  ChevronUpDownIcon,
  StarIcon,
} from '@heroicons/vue/24/solid';
import { useFormat } from '~/composables/useFormat';
import { useTablePreferences } from '~/composables/useTablePreferences';

const { $toast } = useNuxtApp();
const { formatNumber, formatCurrency, formatDate, formatDateTime, formatSeconds } = useFormat();

const permissions = usePermissions();

const route = useRoute();
const router = useRouter();
const instance = getCurrentInstance();

const props = defineProps({
  items: {
    type: Object,
    required: true,
    default: () => ({}) as Record<string, any>,
  },
  columns: {
    type: Array,
    required: true,
    default: [] as [],
  },
  enums: {
    type: Object,
    required: false,
    default: null,
  },
  actions: {
    type: Array,
    required: false,
    default: [] as [],
  },
  loading: {
    type: Boolean,
    required: false,
    default: false as boolean,
  },
  error: {
    type: Boolean,
    required: false,
    default: false as boolean,
  },
  singular: {
    type: String,
    required: false,
    default: '' as string,
  },
  plural: {
    type: String,
    required: false,
    default: '' as string,
  },
  query: {
    type: Object,
    required: false,
    default: null,
  },
  slug: {
    type: String,
    required: false,
    default: '' as string,
  },
  compact: {
    type: Boolean,
    required: false,
    default: false as boolean,
  },
  // Prázdný stav: odkaz + popisek CTA tlačítka (např. '/kontakty/pridat', 'Přidat kontakt')
  addLink: {
    type: String,
    required: false,
    default: '',
  },
  addLabel: {
    type: String,
    required: false,
    default: '',
  },
  // Výsledky jsou filtrované → prázdný stav nabídne zrušení filtrů
  filtered: {
    type: Boolean,
    required: false,
    default: false,
  },
  // Hromadný výběr řádků (checkboxy + hromadné smazání)
  selectable: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits([
  'delete-item',
  'update-sort',
  'update-page',
  'update-per-page',
  'open-dialog',
  'download',
  'replicate',
  'retry',
  'clear-filters',
  'bulk-delete',
]);

function hasListener(name: string): boolean {
  return !!instance?.vnode.props?.[name];
}

function canEdit(slug: string) {
  return permissions.canEdit(slug);
}

function canEditOrDeleteBySite(slug: string) {
  return permissions.moduleBelongsToSite(slug);
}

function canDelete(slug: string) {
  return permissions.canDelete(slug);
}

async function copyToClipboard(item, key) {
  await navigator.clipboard
    .writeText(item[key])
    .then(() => {
      $toast.show({
        summary: 'Kopírováno',
        detail: 'Zpráva byla úspěšně zkopírována do schránky.',
        severity: 'success',
      });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se zkopírovat zprávu do schránky.',
        severity: 'error',
      });
    });
}

function redirect(itemId: number, action: object) {
  if (action.path && action.hash) {
    router.push(`${action.path}/${itemId}${action.hash}`);
  } else if (action.hash) {
    router.push(`${route.path}/${itemId}${action.hash}`);
  } else if (action.path) {
    router.push(`${action.path}/${itemId}`);
  } else {
    router.push(`${route.path}/${itemId}`);
  }
}

const printText = computed(() => (item, column) => {
  const value = column.key.split('.').reduce((obj, key) => obj?.[key], item);

  return value ?? '-';
});

/* ---------- Stavy tabulky ---------- */

interface TableRow {
  id: number | string;
  name?: string;
  [key: string]: unknown;
}

interface TableColumn {
  key: string;
  name?: string;
  sortable?: boolean;
  hidden?: boolean;
  width?: number | string;
  [key: string]: unknown;
}

interface TableAction {
  type: string;
  [key: string]: unknown;
}

const rows = computed<TableRow[]>(() =>
  Array.isArray(props.items?.data) ? (props.items.data as TableRow[]) : [],
);
const hasRows = computed(() => rows.value.length > 0);
const showSkeleton = computed(() => props.loading && !hasRows.value);
const showRows = computed(() => !props.error && hasRows.value);
const showError = computed(() => !props.loading && props.error);
const showEmpty = computed(
  () => !props.loading && !props.error && Array.isArray(props.items?.data) && !hasRows.value,
);
const skeletonCount = computed(() => rows.value.length || 5);
const skeletonWidths = ['w-3/4', 'w-1/2', 'w-2/3', 'w-5/6', 'w-1/3'];

const totalColspan = computed(() => visibleColumns.value.length + 1 + (props.selectable ? 1 : 0));

/* ---------- Řazení ---------- */

function sortState(column: TableColumn): 'asc' | 'desc' | null {
  if (!props.query || props.query.orderBy !== column.key) return null;
  return props.query.orderWay === 'desc' ? 'desc' : 'asc';
}

function ariaSort(column: TableColumn): string | undefined {
  if (!column.sortable) return undefined;
  const state = sortState(column);
  if (state === 'asc') return 'ascending';
  if (state === 'desc') return 'descending';
  return 'none';
}

function columnStyle(column: TableColumn) {
  if (!column.width) return undefined;
  const width = typeof column.width === 'number' ? `${column.width}px` : String(column.width);
  return { width: /^\d+$/.test(width) ? `${width}px` : width };
}

// Na malých obrazovkách je první viditelný sloupec přilepený vlevo
function stickyClass(index: number, header = false): string {
  if (index !== 0) return '';
  const left = props.selectable ? 'max-sm:left-12' : 'max-sm:left-0';
  const bg = header ? 'max-sm:bg-slate-50' : 'max-sm:bg-white';
  return `max-sm:sticky max-sm:z-10 ${left} ${bg}`;
}

/* ---------- Preference (sloupce + stránkování) ---------- */

const PER_PAGE_OPTIONS = [10, 25, 50, 100, 250];
const FORCED_COLUMN_KEYS = new Set(['id']);

const preferences = useTablePreferences(props.slug, { defaultPerPage: 25 });

const visibleColumns = computed(() => {
  return (props.columns as any[]).filter((column) => {
    if (FORCED_COLUMN_KEYS.has(column.key)) return true;
    return preferences.isColumnVisible(column.key);
  });
});

function toggleColumn(key: string): void {
  if (FORCED_COLUMN_KEYS.has(key)) return;
  const allKeys = (props.columns as any[]).map((c) => c.key);
  const current = preferences.visibleColumns.value ?? allKeys;
  const next = current.includes(key) ? current.filter((k) => k !== key) : [...current, key];
  preferences.setVisibleColumns(next);
}

function selectPerPage(value: number): void {
  if (preferences.perPage.value === value) return;
  preferences.setPerPage(value);
  emit('update-per-page', value);
}

async function resetPreferences(): Promise<void> {
  await preferences.reset();
  emit('update-per-page', preferences.perPage.value);
}

function isColumnChecked(key: string): boolean {
  if (FORCED_COLUMN_KEYS.has(key)) return true;
  if (!preferences.visibleColumns.value) return true;
  return preferences.visibleColumns.value.includes(key);
}

/* ---------- Hromadný výběr ---------- */

const selectedIds = ref<(number | string)[]>([]);

const pageIds = computed(() => rows.value.map((item) => item.id));
const allPageSelected = computed(
  () => pageIds.value.length > 0 && pageIds.value.every((id) => selectedIds.value.includes(id)),
);
const somePageSelected = computed(
  () => !allPageSelected.value && pageIds.value.some((id) => selectedIds.value.includes(id)),
);

function isSelected(id: number | string): boolean {
  return selectedIds.value.includes(id);
}

function toggleSelected(id: number | string): void {
  selectedIds.value = isSelected(id)
    ? selectedIds.value.filter((value) => value !== id)
    : [...selectedIds.value, id];
}

function toggleSelectAll(): void {
  if (allPageSelected.value) {
    selectedIds.value = selectedIds.value.filter((id) => !pageIds.value.includes(id));
  } else {
    selectedIds.value = Array.from(new Set([...selectedIds.value, ...pageIds.value]));
  }
}

function clearSelection(): void {
  selectedIds.value = [];
}

const canBulkDelete = computed(
  () =>
    (props.actions as TableAction[]).some((action) => action.type === 'delete') &&
    (props.slug === '' || (canEditOrDeleteBySite(props.slug) && canDelete(props.slug))),
);

function bulkLabel(count: number): string {
  if (count === 1) return '1 vybraná položka';
  if (count >= 2 && count <= 4) return `${count} vybrané položky`;
  return `${count} vybraných položek`;
}

/* ---------- Mazání ---------- */

const showDeleteDialog = ref(false);
const deleteDialogItem = ref<{ id: number | string | null; name?: string } | null>(null);
const deleteMode = ref<'single' | 'bulk'>('single');
const deletePending = ref(false);
const deleteMessage = ref('');
let deleteFallbackTimer: ReturnType<typeof setTimeout> | null = null;

function clearDeleteTimer(): void {
  if (deleteFallbackTimer) {
    clearTimeout(deleteFallbackTimer);
    deleteFallbackTimer = null;
  }
}

function openDeleteDialog(item: TableRow): void {
  deleteMode.value = 'single';
  deleteMessage.value = '';
  deleteDialogItem.value = item;
  showDeleteDialog.value = true;
}

function openBulkDeleteDialog(): void {
  if (!selectedIds.value.length) return;
  deleteMode.value = 'bulk';
  deleteDialogItem.value = { id: null, name: bulkLabel(selectedIds.value.length) };
  deleteMessage.value = `Opravdu si přejete smazat ${bulkLabel(selectedIds.value.length)}? Tato akce je nevratná.`;
  showDeleteDialog.value = true;
}

function finishDelete(): void {
  clearDeleteTimer();
  deletePending.value = false;
  showDeleteDialog.value = false;
}

function confirmDelete(): void {
  if (deletePending.value) return;
  deletePending.value = true;
  if (deleteMode.value === 'bulk') {
    emit('bulk-delete', [...selectedIds.value]);
  } else {
    emit('delete-item', deleteDialogItem.value?.id);
  }
  clearDeleteTimer();
  deleteFallbackTimer = setTimeout(finishDelete, 10000);
}

// Stránka během mazání nastaví loading=true; po návratu na false dialog zavřeme
watch(
  () => props.loading,
  (now, prev) => {
    if (deletePending.value && prev && !now) finishDelete();
  },
);

// Při změně dat zrušit výběr (a případně dokončit mazání)
watch(
  () => props.items?.data,
  () => {
    clearSelection();
    if (deletePending.value && !props.loading) finishDelete();
  },
);

watch(showDeleteDialog, (open) => {
  if (!open) {
    clearDeleteTimer();
    deletePending.value = false;
  }
});

onMounted(async () => {
  if (!props.slug) return;
  await preferences.load();
  if (preferences.perPage.value !== 25) {
    emit('update-per-page', preferences.perPage.value);
  }
});

onBeforeUnmount(clearDeleteTimer);

const actionButtonClass =
  'rounded-md p-0.5 text-slate-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1';
</script>

<template>
  <div :class="compact ? 'w-full' : 'w-full px-4 sm:px-0'">
    <div v-if="slug" class="relative mt-2 flex items-center justify-end">
      <Popover v-slot="{ open }" class="relative">
        <PopoverButton
          :class="[
            open ? 'bg-slate-50 text-slate-700' : 'bg-white text-slate-500',
            'flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium shadow-sm ring-1 ring-slate-200 transition-colors hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
          ]"
          title="Nastavení tabulky"
        >
          <Cog6ToothIcon class="size-4" aria-hidden="true" />
          <span>Sloupce a stránkování</span>
        </PopoverButton>

        <transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <PopoverPanel
            focus
            class="absolute right-0 top-full z-30 mt-2 w-72 origin-top-right rounded-2xl bg-white p-4 shadow-lg ring-1 ring-slate-200 focus:outline-none"
          >
            <div class="mb-3 flex items-center justify-between">
              <span class="label-caps text-slate-500">Viditelné sloupce</span>
              <button
                type="button"
                class="label-caps flex items-center gap-1 rounded text-slate-400 transition-colors hover:text-indigo-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                title="Resetovat na výchozí"
                @click="resetPreferences"
              >
                <ArrowPathIcon class="size-3" aria-hidden="true" />
                Reset
              </button>
            </div>

            <ul class="mb-4 max-h-64 space-y-1 overflow-y-auto pr-1">
              <li
                v-for="column in columns as any[]"
                :key="column.key"
                class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-slate-50"
              >
                <label
                  :for="`pref-col-${column.key}`"
                  class="flex flex-1 cursor-pointer items-center gap-2 text-sm text-slate-700"
                  :class="
                    FORCED_COLUMN_KEYS.has(column.key) ? 'cursor-not-allowed text-slate-400' : ''
                  "
                >
                  <input
                    :id="`pref-col-${column.key}`"
                    type="checkbox"
                    :checked="isColumnChecked(column.key)"
                    :disabled="FORCED_COLUMN_KEYS.has(column.key)"
                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                    @change="toggleColumn(column.key)"
                  />
                  <span>{{ column.name }}</span>
                </label>
              </li>
            </ul>

            <div>
              <span class="label-caps mb-2 block text-slate-500">Záznamů na stránku</span>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="size in PER_PAGE_OPTIONS"
                  :key="size"
                  type="button"
                  :aria-pressed="preferences.perPage.value === size ? 'true' : 'false'"
                  class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                  :class="
                    preferences.perPage.value === size
                      ? 'bg-indigo-600 text-white shadow-sm'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  "
                  @click="selectPerPage(size)"
                >
                  {{ size }}
                </button>
              </div>
            </div>
          </PopoverPanel>
        </transition>
      </Popover>
    </div>

    <div
      v-if="selectable && selectedIds.length"
      class="mt-2 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-indigo-50 px-4 py-2.5 ring-1 ring-indigo-100"
      role="region"
      aria-label="Hromadné akce"
    >
      <span class="text-sm font-semibold text-indigo-900" aria-live="polite">
        {{ bulkLabel(selectedIds.length) }}
      </span>
      <div class="flex items-center gap-2">
        <BaseButton size="sm" variant="secondary" @click="clearSelection">Zrušit výběr</BaseButton>
        <BaseButton v-if="canBulkDelete" size="sm" variant="danger" @click="openBulkDeleteDialog">
          <TrashIcon class="mr-1.5 size-4" aria-hidden="true" />
          Smazat vybrané
        </BaseButton>
      </div>
    </div>

    <div class="flow-root">
      <div :class="compact ? 'overflow-x-auto' : '-mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8'">
        <div
          :class="
            compact
              ? 'inline-block min-w-full align-middle'
              : 'inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8'
          "
        >
          <div
            :class="
              compact
                ? 'overflow-clip'
                : 'overflow-clip rounded-2xl bg-white shadow-sm ring-1 ring-slate-200'
            "
          >
            <table
              class="min-w-full divide-y divide-slate-200"
              :aria-busy="loading ? 'true' : 'false'"
            >
              <thead class="bg-slate-50">
                <tr>
                  <th
                    v-if="selectable"
                    scope="col"
                    class="w-12 py-3.5 pl-4 pr-0 max-sm:sticky max-sm:left-0 max-sm:z-10 max-sm:bg-slate-50 sm:pl-6"
                  >
                    <input
                      type="checkbox"
                      :checked="allPageSelected"
                      :indeterminate="somePageSelected"
                      :disabled="!hasRows"
                      aria-label="Vybrat vše na stránce"
                      class="size-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                      @change="toggleSelectAll"
                    />
                  </th>
                  <th
                    v-for="(column, key) in visibleColumns"
                    :key="key"
                    scope="col"
                    :aria-sort="ariaSort(column)"
                    :style="columnStyle(column)"
                    :class="[
                      column.hidden ? 'hidden md:table-cell' : '',
                      stickyClass(key, true),
                      column.sortable ? 'p-0' : 'py-3.5 pl-4 pr-3 sm:pl-6',
                      'label-caps text-left text-slate-500',
                    ]"
                  >
                    <button
                      v-if="column.sortable"
                      type="button"
                      class="group flex w-full items-center py-3.5 pl-4 pr-3 text-left uppercase transition-colors hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500 sm:pl-6"
                      @click="$emit('update-sort', column.key)"
                    >
                      <span>{{ column.name }}</span>
                      <span class="ml-2 flex-none rounded text-slate-400 group-hover:bg-slate-200">
                        <ChevronUpIcon
                          v-if="sortState(column) === 'asc'"
                          class="size-4 text-indigo-600"
                          aria-hidden="true"
                        />
                        <ChevronDownIcon
                          v-else-if="sortState(column) === 'desc'"
                          class="size-4 text-indigo-600"
                          aria-hidden="true"
                        />
                        <ChevronUpDownIcon
                          v-else
                          class="size-4 text-slate-400 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                          aria-hidden="true"
                        />
                      </span>
                    </button>
                    <span v-else>{{ column.name }}</span>
                  </th>
                  <th scope="col" class="relative w-[150px] py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Akce</span>
                  </th>
                </tr>
              </thead>

              <tbody
                :class="[
                  loading && hasRows ? 'pointer-events-none opacity-50' : '',
                  'divide-y divide-slate-100 bg-white transition-opacity',
                ]"
              >
                <template v-if="showRows">
                  <tr
                    v-for="(item, key) in rows"
                    :key="item.id ?? key"
                    :class="[
                      selectable && isSelected(item.id) ? 'bg-indigo-50/40' : '',
                      'transition-colors hover:bg-slate-50/50',
                    ]"
                  >
                    <td
                      v-if="selectable"
                      class="w-12 py-3.5 pl-4 pr-0 max-sm:sticky max-sm:left-0 max-sm:z-10 max-sm:bg-white sm:pl-6"
                    >
                      <input
                        type="checkbox"
                        :checked="isSelected(item.id)"
                        :aria-label="`Vybrat položku ${item.name ?? item.id}`"
                        class="size-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        @change="toggleSelected(item.id)"
                      />
                    </td>
                    <td
                      v-for="(column, index) in visibleColumns"
                      :key="index"
                      :style="columnStyle(column)"
                      :class="[
                        column.hidden ? 'hidden md:table-cell' : '',
                        stickyClass(index),
                        'whitespace-nowrap py-3.5 pl-4 pr-3 text-sm font-medium text-slate-600 sm:pl-6',
                      ]"
                    >
                      <span v-if="column.type === 'text'" class="text-slate-900">
                        {{ printText(item, column) }}
                      </span>
                      <span
                        v-else-if="column.type === 'number'"
                        class="tabular-nums text-slate-900"
                      >
                        {{ formatNumber(printText(item, column), column.decimals ?? 0) }}
                      </span>
                      <span
                        v-else-if="column.type === 'currency'"
                        class="tabular-nums text-slate-900"
                      >
                        {{
                          formatCurrency(
                            printText(item, column),
                            column.currency ?? 'Kč',
                            column.decimals ?? 2,
                          )
                        }}
                      </span>
                      <span v-else-if="column.type === 'percent'" class="tabular-nums">
                        {{ formatNumber(item[column.key], column.decimals ?? 0) }} %
                      </span>
                      <PropsBadge
                        v-else-if="column.type === 'badge'"
                        :color="
                          column.colorKey
                            ? column.colorKey.split('.').reduce((obj, key) => obj?.[key], item)
                            : null
                        "
                      >
                        {{ printText(item, column) }}
                      </PropsBadge>
                      <span v-else-if="column.type === 'enum'">
                        {{ enums?.[column.key]?.[item[column.key]] ?? '-' }}
                      </span>
                      <span v-else-if="column.type === 'date'" class="tabular-nums">
                        {{ formatDate(printText(item, column)) }}
                      </span>
                      <span v-else-if="column.type === 'datetime'" class="tabular-nums">
                        {{ formatDateTime(printText(item, column)) }}
                      </span>

                      <span
                        v-else-if="column.type === 'seconds'"
                        class="font-mono tabular-nums text-slate-900"
                      >
                        {{ formatSeconds(printText(item, column)) }}
                      </span>

                      <img
                        v-if="column.type === 'image'"
                        class="size-12 rounded-lg bg-slate-50 object-cover shadow-sm ring-1 ring-slate-200"
                        :src="`/content/images/${column.path}/${item[column.key]}`"
                        alt=""
                      />

                      <span v-else-if="column.type === 'stars'" class="flex items-center gap-x-1">
                        <StarIcon
                          v-for="star in 5"
                          :key="star"
                          :class="`size-4 ${star <= item[column.key] ? 'text-amber-400' : 'text-slate-200'}`"
                        />
                      </span>

                      <span
                        v-else-if="column.type === 'mapped' && column.map"
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="
                          column.map[printText(item, column)]?.class ||
                          'bg-slate-100 text-slate-600'
                        "
                      >
                        {{ column.map[printText(item, column)]?.label || printText(item, column) }}
                      </span>

                      <span v-else-if="column.type === 'status'" class="flex items-center">
                        <CheckIcon v-if="item[column.key]" class="size-5 text-emerald-500" />
                        <XMarkIcon v-else class="size-5 text-red-500" />
                      </span>

                      <NuxtLink
                        v-else-if="column.type === 'link'"
                        :to="item[column.key]"
                        :target="column.target"
                        class="text-indigo-600 transition-colors hover:text-indigo-500 hover:underline"
                      >
                        {{ item[column.key] }}
                      </NuxtLink>
                      <a
                        v-else-if="column.type === 'external_link'"
                        :href="item[column.key]"
                        target="_blank"
                        class="text-indigo-600 transition-colors hover:text-indigo-500 hover:underline"
                      >
                        {{ item[column.key] }}
                      </a>
                    </td>

                    <td
                      class="relative w-[150px] whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
                      @click.stop
                    >
                      <div class="flex items-center justify-end gap-x-3">
                        <template v-for="(action, index) in actions" :key="index">
                          <button
                            v-if="
                              (action.type === 'edit' &&
                                canEditOrDeleteBySite(slug) &&
                                canEdit(slug)) ||
                              (action.type === 'edit' && slug === '')
                            "
                            type="button"
                            :class="[actionButtonClass, 'hover:text-indigo-600']"
                            title="Zobrazit detail"
                            aria-label="Zobrazit detail"
                            @click="redirect(item.id, action)"
                          >
                            <MagnifyingGlassIcon class="size-5" aria-hidden="true" />
                          </button>

                          <button
                            v-if="action.type === 'download'"
                            type="button"
                            :class="[actionButtonClass, 'hover:text-emerald-600']"
                            title="Stáhnout"
                            aria-label="Stáhnout"
                            @click="emit('download', item.id)"
                          >
                            <ArrowDownTrayIcon class="size-5" aria-hidden="true" />
                          </button>

                          <button
                            v-if="action.type === 'copy'"
                            type="button"
                            :class="[actionButtonClass, 'hover:text-indigo-600']"
                            title="Kopírovat"
                            aria-label="Kopírovat do schránky"
                            @click="copyToClipboard(item, action.key)"
                          >
                            <ClipboardDocumentIcon class="size-5" aria-hidden="true" />
                          </button>

                          <button
                            v-if="action.type === 'replicate'"
                            type="button"
                            :class="[actionButtonClass, 'hover:text-indigo-600']"
                            title="Duplikovat"
                            aria-label="Duplikovat"
                            @click="emit('replicate', item.id)"
                          >
                            <DocumentDuplicateIcon class="size-5" aria-hidden="true" />
                          </button>

                          <button
                            v-if="
                              (action.type === 'edit-dialog' &&
                                canEditOrDeleteBySite(slug) &&
                                canEdit(slug)) ||
                              (action.type === 'edit-dialog' && slug === '')
                            "
                            type="button"
                            :class="[actionButtonClass, 'hover:text-amber-500']"
                            title="Rychlá úprava"
                            aria-label="Rychlá úprava"
                            @click="emit('open-dialog', item)"
                          >
                            <BoltIcon class="size-5" aria-hidden="true" />
                          </button>

                          <button
                            v-if="
                              (action.type === 'delete' &&
                                canEditOrDeleteBySite(slug) &&
                                canDelete(slug)) ||
                              (action.type === 'delete' && slug === '')
                            "
                            type="button"
                            :class="[actionButtonClass, 'hover:text-red-500']"
                            title="Smazat"
                            aria-label="Smazat"
                            @click="openDeleteDialog(item)"
                          >
                            <TrashIcon class="size-5" aria-hidden="true" />
                          </button>
                        </template>
                      </div>
                    </td>
                  </tr>
                </template>

                <template v-else-if="showSkeleton">
                  <tr v-for="n in skeletonCount" :key="`skeleton-${n}`" aria-hidden="true">
                    <td v-if="selectable" class="w-12 py-4 pl-4 pr-0 sm:pl-6">
                      <div class="size-4 animate-pulse rounded bg-slate-100" />
                    </td>
                    <td
                      v-for="(column, index) in visibleColumns"
                      :key="index"
                      :class="[
                        column.hidden ? 'hidden md:table-cell' : '',
                        'py-4 pl-4 pr-3 sm:pl-6',
                      ]"
                    >
                      <div
                        :class="[
                          skeletonWidths[(n + index) % skeletonWidths.length],
                          'h-4 min-w-12 animate-pulse rounded-md bg-slate-100',
                        ]"
                      />
                    </td>
                    <td class="py-4 pl-3 pr-4 sm:pr-6">
                      <div class="ml-auto h-4 w-16 animate-pulse rounded-md bg-slate-100" />
                    </td>
                  </tr>
                  <tr class="sr-only">
                    <td :colspan="totalColspan">{{ `${plural} se načítají...` }}</td>
                  </tr>
                </template>

                <tr v-else-if="showError">
                  <td :colspan="totalColspan" class="px-4 py-12 text-center text-sm text-slate-500">
                    <div class="flex flex-col items-center gap-3">
                      <div class="flex size-12 items-center justify-center rounded-full bg-red-50">
                        <ExclamationTriangleIcon class="size-6 text-red-500" aria-hidden="true" />
                      </div>
                      <p>{{ `${plural} se nepodařilo načíst.` }}</p>
                      <BaseButton
                        v-if="hasListener('onRetry')"
                        size="sm"
                        variant="secondary"
                        @click="emit('retry')"
                      >
                        <ArrowPathIcon class="mr-1.5 size-4" aria-hidden="true" />
                        Zkusit znovu
                      </BaseButton>
                    </div>
                  </td>
                </tr>

                <tr v-else-if="showEmpty">
                  <td :colspan="totalColspan" class="px-4 py-12 text-center text-sm text-slate-500">
                    <div class="flex flex-col items-center gap-3">
                      <div
                        class="flex size-12 items-center justify-center rounded-full bg-slate-100"
                      >
                        <FunnelIcon
                          v-if="filtered"
                          class="size-6 text-slate-400"
                          aria-hidden="true"
                        />
                        <InboxIcon v-else class="size-6 text-slate-400" aria-hidden="true" />
                      </div>
                      <template v-if="filtered">
                        <p>Žádné výsledky pro zadaný filtr</p>
                        <BaseButton
                          v-if="hasListener('onClearFilters')"
                          size="sm"
                          variant="secondary"
                          @click="emit('clear-filters')"
                        >
                          <XMarkIcon class="mr-1.5 size-4" aria-hidden="true" />
                          Zrušit filtry
                        </BaseButton>
                      </template>
                      <template v-else>
                        <p>{{ `Zatím nemáte žádné ${plural.toLowerCase()}.` }}</p>
                        <NuxtLink
                          v-if="addLink"
                          :to="addLink"
                          class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                          <PlusIcon class="mr-1.5 size-4" aria-hidden="true" />
                          {{ addLabel || 'Přidat' }}
                        </NuxtLink>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="border-t border-slate-200 bg-slate-50">
              <BasePagination
                v-if="!error && hasRows && query"
                :page="items.currentPage"
                :per-page="items.perPage"
                :total="items.total"
                :last-page="items.lastPage"
                @update-page="emit('update-page', $event)"
              />
            </div>
          </div>
        </div>

        <BaseDialogDelete
          v-model:show="showDeleteDialog"
          v-model:item="deleteDialogItem"
          :loading="deletePending"
          :title="deleteMode === 'bulk' ? 'Smazat vybrané položky' : 'Smazat položku'"
          :message="deleteMessage"
          @delete-item="confirmDelete"
        />
      </div>
    </div>
  </div>
</template>
