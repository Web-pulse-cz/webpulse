<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue';

import {
  BoltIcon,
  MagnifyingGlassIcon,
  TrashIcon,
  ClipboardDocumentIcon,
  CheckIcon,
  XMarkIcon,
  ArrowDownTrayIcon,
  Cog6ToothIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import { ChevronDownIcon, ChevronUpIcon, StarIcon } from '@heroicons/vue/24/solid';
import { useFormat } from '~/composables/useFormat';
import { useTablePreferences } from '~/composables/useTablePreferences';

const { $toast } = useNuxtApp();
const { formatNumber, formatCurrency, formatDate, formatDateTime, formatSeconds } = useFormat();

const user = useSanctumUser();
const permissions = usePermissions();

const route = useRoute();
const router = useRouter();
const showDeleteDialog = ref(false);
const deleteDialogItem = ref(null);
const showPreferences = ref(false);
const preferencesRef = ref<HTMLElement | null>(null);

const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const props = defineProps({
  items: {
    type: Object,
    required: true,
    default: {} as {},
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
});

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
  console.log(item[key]);
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
    router.push(`${route.fullPath}/${itemId}${action.hash}`);
  } else if (action.path) {
    router.push(`${action.path}/${itemId}`);
  } else {
    router.push(`${route.fullPath}/${itemId}`);
  }
}

const printText = computed(() => (item, column) => {
  const value = column.key.split('.').reduce((obj, key) => obj?.[key], item);

  return value ?? '-';
});

const emit = defineEmits([
  'delete-item',
  'update-sort',
  'update-page',
  'update-per-page',
  'open-dialog',
  'download',
  'replicate',
]);

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

function handleClickOutside(event: MouseEvent): void {
  if (!preferencesRef.value) return;
  if (!preferencesRef.value.contains(event.target as Node)) {
    showPreferences.value = false;
  }
}

watch(showPreferences, (open) => {
  if (open) {
    document.addEventListener('mousedown', handleClickOutside);
  } else {
    document.removeEventListener('mousedown', handleClickOutside);
  }
});

onMounted(async () => {
  if (!props.slug) return;
  await preferences.load();
  if (preferences.perPage.value !== 25) {
    emit('update-per-page', preferences.perPage.value);
  }
});
</script>

<template>
  <div :class="compact ? 'w-full' : 'w-full px-4 sm:px-0'">
    <div
      v-if="slug"
      ref="preferencesRef"
      class="relative mb-2 mt-2 flex items-center justify-end"
    >
      <button
        type="button"
        class="flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1.5 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-slate-200 transition-colors hover:bg-slate-50 hover:text-slate-700"
        title="Nastavení tabulky"
        @click="showPreferences = !showPreferences"
      >
        <Cog6ToothIcon class="size-4" />
        <span>Sloupce a stránkování</span>
      </button>

      <div
        v-if="showPreferences"
        class="absolute right-0 top-full z-30 mt-2 w-72 rounded-2xl bg-white p-4 shadow-lg ring-1 ring-slate-200"
      >
        <div class="mb-3 flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">
            Viditelné sloupce
          </span>
          <button
            type="button"
            class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 transition-colors hover:text-indigo-600"
            title="Resetovat na výchozí"
            @click="resetPreferences"
          >
            <ArrowPathIcon class="size-3" />
            Reset
          </button>
        </div>

        <ul class="mb-4 max-h-64 space-y-1 overflow-y-auto pr-1">
          <li
            v-for="column in (columns as any[])"
            :key="column.key"
            class="flex items-center justify-between rounded-lg px-2 py-1.5 hover:bg-slate-50"
          >
            <label
              :for="`pref-col-${column.key}`"
              class="flex flex-1 cursor-pointer items-center gap-2 text-sm text-slate-700"
              :class="FORCED_COLUMN_KEYS.has(column.key) ? 'cursor-not-allowed text-slate-400' : ''"
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
          <span class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">
            Záznamů na stránku
          </span>
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="size in PER_PAGE_OPTIONS"
              :key="size"
              type="button"
              class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors"
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
                ? 'overflow-hidden'
                : 'overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200'
            "
          >
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th
                    v-for="(column, key) in visibleColumns"
                    :key="key"
                    scope="col"
                    :class="[
                      column.hidden ? 'hidden md:table-cell' : '',
                      column.sortable ? 'cursor-pointer transition-colors hover:bg-slate-100' : '',
                      column.width ? `w-[${column.width}px]` : '',
                      'py-3.5 pl-4 pr-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 sm:pl-6',
                    ]"
                    @click="column.sortable ? $emit('update-sort', column.key) : null"
                  >
                    <div class="group flex items-center">
                      <span>{{ column.name }}</span>
                      <span
                        v-if="column.sortable"
                        class="ml-2 flex-none rounded text-slate-400 group-hover:bg-slate-200"
                      >
                        <ChevronDownIcon
                          v-if="query && query.orderBy === column.key && query.orderWay === 'asc'"
                          class="size-4 text-indigo-600"
                        />
                        <ChevronUpIcon
                          v-else-if="
                            query && query.orderBy === column.key && query.orderWay === 'desc'
                          "
                          class="size-4 text-indigo-600"
                        />
                        <ChevronUpDownIcon
                          v-else-if="!query || query.orderBy !== column.key"
                          class="size-4 text-slate-400 opacity-0 transition-opacity group-hover:opacity-100"
                        />
                      </span>
                    </div>
                  </th>
                  <th scope="col" class="relative w-[150px] py-3.5 pl-3 pr-4 sm:pr-6" />
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 bg-white">
                <tr
                  v-for="(item, key) in items.data"
                  v-if="!loading && !error && items.data && items.data.length"
                  :key="key"
                  class="transition-colors hover:bg-slate-50/50"
                >
                  <td
                    v-for="(column, index) in visibleColumns"
                    :key="index"
                    :class="[
                      column.hidden ? 'hidden md:table-cell' : '',
                      column.width ? `w-[${column.width}px]` : '',
                      'whitespace-nowrap py-3.5 pl-4 pr-3 text-sm font-medium text-slate-600 sm:pl-6',
                    ]"
                  >
                    <span v-if="column.type === 'text'" class="text-slate-900">
                      {{ printText(item, column) }}
                    </span>
                    <span v-else-if="column.type === 'number'" class="tabular-nums text-slate-900">
                      {{ formatNumber(printText(item, column), column.decimals ?? 0) }}
                    </span>
                    <span v-else-if="column.type === 'currency'" class="tabular-nums text-slate-900">
                      {{ formatCurrency(printText(item, column), column.currency ?? 'Kč', column.decimals ?? 2) }}
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
                      {{ enums[column.key][item[column.key]] }}
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
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                      :class="
                        column.map[printText(item, column)]?.class || 'bg-slate-100 text-slate-600'
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
                          class="text-slate-400 transition-colors hover:text-indigo-600"
                          title="Upravit"
                          @click="redirect(item.id, action)"
                        >
                          <MagnifyingGlassIcon class="size-5" />
                        </button>

                        <button
                          v-if="action.type === 'download'"
                          type="button"
                          class="text-slate-400 transition-colors hover:text-emerald-600"
                          title="Stáhnout"
                          @click="emit('download', item.id)"
                        >
                          <ArrowDownTrayIcon class="size-5" />
                        </button>

                        <button
                          v-if="action.type === 'copy'"
                          type="button"
                          class="text-slate-400 transition-colors hover:text-indigo-600"
                          title="Kopírovat"
                          @click="copyToClipboard(item, action.key)"
                        >
                          <ClipboardDocumentIcon class="size-5" />
                        </button>

                        <button
                          v-if="action.type === 'replicate'"
                          type="button"
                          class="text-slate-400 transition-colors hover:text-indigo-600"
                          title="Duplikovat"
                          @click="emit('replicate', item.id)"
                        >
                          <ClipboardDocumentIcon class="size-5" />
                        </button>

                        <button
                          v-if="
                            (action.type === 'edit-dialog' &&
                              canEditOrDeleteBySite(slug) &&
                              canEdit(slug)) ||
                            (action.type === 'edit-dialog' && slug === '')
                          "
                          type="button"
                          class="text-slate-400 transition-colors hover:text-amber-500"
                          title="Rychlá úprava"
                          @click="emit('open-dialog', item)"
                        >
                          <BoltIcon class="size-5" />
                        </button>

                        <button
                          v-if="
                            (action.type === 'delete' &&
                              canEditOrDeleteBySite(slug) &&
                              canDelete(slug)) ||
                            (action.type === 'delete' && slug === '')
                          "
                          type="button"
                          class="text-slate-400 transition-colors hover:text-red-500"
                          title="Smazat"
                          @click="
                            showDeleteDialog = true;
                            deleteDialogItem = item;
                          "
                        >
                          <TrashIcon class="size-5" />
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>

                <tr v-else-if="!loading && error">
                  <td
                    :colspan="visibleColumns.length + 1"
                    class="whitespace-nowrap py-12 text-center text-sm text-slate-500"
                  >
                    {{ `${plural} se nepodařilo načíst.` }}
                  </td>
                </tr>
                <tr v-else-if="!loading && !error && items.data && items.data.length === 0">
                  <td
                    :colspan="visibleColumns.length + 1"
                    class="whitespace-nowrap py-12 text-center text-sm text-slate-500"
                  >
                    {{ `Zatím nemáte žádné ${plural.toLowerCase()}.` }}
                  </td>
                </tr>
                <tr v-else-if="loading">
                  <td
                    :colspan="visibleColumns.length + 1"
                    class="whitespace-nowrap py-12 text-center text-sm text-slate-500"
                  >
                    <div class="flex items-center justify-center gap-x-2">
                      <svg
                        class="h-5 w-5 animate-spin text-indigo-600"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                      </svg>
                      <span>{{ `${plural} se načítají...` }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="border-t border-slate-200 bg-slate-50">
              <BasePagination
                v-if="!loading && !error && items.data && items.data.length && query"
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
          @delete-item="
            emit('delete-item', deleteDialogItem.id);
            showDeleteDialog = false;
          "
        />
      </div>
    </div>
  </div>
</template>
