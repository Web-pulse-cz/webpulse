<script setup lang="ts">
import { computed, inject, onMounted, ref, watch } from 'vue';
import _ from 'lodash';
import {
  BookOpenIcon,
  CheckCircleIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  TrashIcon,
  ArrowPathIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

interface Book {
  id: number;
  name: string;
  author: string | null;
  keywords: string[];
  is_read: boolean;
  image: string | null;
  tasks_total: number;
  tasks_done: number;
}

interface Paginated<T> {
  data: T[];
  total: number;
  perPage: number;
  currentPage: number;
  lastPage: number;
}

const { $toast } = useNuxtApp();
const { canEdit, canDelete } = usePermissions();
const pageTitle = ref('Knihy');

const loading = ref(false);
const error = ref(false);

const breadcrumbs = ref([
  { name: 'Osobní růst', link: '/osobni-rust/knihy', current: false },
  { name: pageTitle.value, link: '/osobni-rust/knihy', current: true },
]);

const readFilters = [
  { value: '', label: 'Vše' },
  { value: 'read', label: 'Přečtené' },
  { value: 'unread', label: 'Nepřečtené' },
];

const searchString = ref(inject('searchString', ''));
const tableQuery = useListQuery(
  {
    search: null as string | null,
    read: '' as string,
    paginate: 24 as number,
    page: 1 as number,
    orderBy: 'created_at' as string,
    orderWay: 'desc' as string,
  },
  { searchString },
);

const result = ref<Paginated<Book> | null>(null);
const books = computed(() => result.value?.data ?? []);
const isFiltered = computed(() => !!tableQuery.value.search || !!tableQuery.value.read);

async function loadItems() {
  error.value = false;
  loading.value = true;
  const client = useSanctumClient();

  await client<Paginated<Book>>('/api/admin/personal-growth/book', {
    method: 'GET',
    query: tableQuery.value,
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      result.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst knihy. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

const deleteDialog = ref({ show: false, item: null as Book | null, loading: false });

function askDelete(book: Book) {
  deleteDialog.value = { show: true, item: book, loading: false };
}

async function deleteItem(id: number) {
  deleteDialog.value.loading = true;
  const client = useSanctumClient();

  await client('/api/admin/personal-growth/book/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Smazáno', severity: 'success' });
      deleteDialog.value.show = false;
      if (books.value.length === 1 && tableQuery.value.page > 1) tableQuery.value.page--;
      loadItems();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat knihu.', severity: 'error' });
    })
    .finally(() => {
      deleteDialog.value.loading = false;
    });
}

function setReadFilter(value: string) {
  if (tableQuery.value.read === value) return;
  tableQuery.value.read = value;
  tableQuery.value.page = 1;
  loadItems();
}

function searchKeyword(keyword: string) {
  searchString.value = keyword;
}

function clearFilters() {
  searchString.value = '';
  setReadFilter('');
}

function updatePage(page: number) {
  tableQuery.value.page = page;
  loadItems();
}

const debouncedLoadItems = _.debounce(loadItems, 400);
watch(searchString, () => {
  if ((tableQuery.value.search ?? '') === (searchString.value ?? '')) return;
  tableQuery.value.search = searchString.value;
  tableQuery.value.page = 1;
  debouncedLoadItems();
});

function imageUrl(image: string) {
  return `/content/images/book/medium/${image}`;
}

useHead({ title: pageTitle });

onMounted(() => {
  loadItems();
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
      :actions="[{ type: 'add', text: 'Přidat knihu' }]"
      slug="books"
    />

    <!-- Filtry -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div
        class="inline-flex rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200"
        role="radiogroup"
        aria-label="Filtr podle stavu čtení"
      >
        <button
          v-for="filter in readFilters"
          :key="filter.value"
          type="button"
          role="radio"
          :aria-checked="tableQuery.read === filter.value"
          class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
          :class="
            tableQuery.read === filter.value
              ? 'bg-slate-900 text-white'
              : 'text-slate-600 hover:bg-slate-100'
          "
          @click="setReadFilter(filter.value)"
        >
          {{ filter.label }}
        </button>
      </div>
      <div class="flex items-center gap-3 text-sm text-slate-500">
        <span v-if="tableQuery.search" class="inline-flex items-center gap-1">
          <MagnifyingGlassIcon class="size-4" />
          „{{ tableQuery.search }}“
        </span>
        <button
          v-if="isFiltered"
          type="button"
          class="font-medium text-indigo-600 hover:text-indigo-800"
          @click="clearFilters"
        >
          Zrušit filtry
        </button>
        <span v-if="result">{{ result.total }} knih</span>
      </div>
    </div>

    <!-- Načítání -->
    <div
      v-if="loading && !result"
      class="grid animate-pulse grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
      aria-busy="true"
    >
      <div
        v-for="n in 6"
        :key="n"
        class="flex gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200"
      >
        <div class="h-36 w-24 shrink-0 rounded-lg bg-slate-200" />
        <div class="flex-1 space-y-3 py-1">
          <div class="h-4 w-3/4 rounded bg-slate-200" />
          <div class="h-3 w-1/2 rounded bg-slate-100" />
          <div class="h-5 w-2/3 rounded bg-slate-100" />
        </div>
      </div>
    </div>

    <!-- Chyba -->
    <div
      v-else-if="error && !books.length"
      class="flex flex-col items-center gap-3 rounded-2xl bg-white p-12 text-center ring-1 ring-slate-200"
    >
      <p class="text-sm text-slate-600">Knihy se nepodařilo načíst.</p>
      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700"
        @click="loadItems"
      >
        <ArrowPathIcon class="size-4" /> Zkusit znovu
      </button>
    </div>

    <!-- Prázdný stav -->
    <div
      v-else-if="!books.length"
      class="flex flex-col items-center gap-3 rounded-2xl bg-white p-12 text-center ring-1 ring-slate-200"
    >
      <BookOpenIcon class="size-10 text-slate-300" />
      <template v-if="isFiltered">
        <p class="text-sm text-slate-600">Žádná kniha neodpovídá filtru.</p>
        <button
          type="button"
          class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
          @click="clearFilters"
        >
          Zrušit filtry
        </button>
      </template>
      <template v-else>
        <p class="text-sm text-slate-600">Zatím tu nemáte žádnou knihu.</p>
        <NuxtLink
          v-if="canEdit('books')"
          to="/osobni-rust/knihy/pridat"
          class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
          <PlusIcon class="size-4" /> Přidat knihu
        </NuxtLink>
      </template>
    </div>

    <!-- Dlaždice -->
    <template v-else>
      <ul
        role="list"
        class="grid grid-cols-1 gap-6 transition-opacity sm:grid-cols-2 lg:grid-cols-3"
        :class="{ 'opacity-60': loading }"
      >
        <li
          v-for="book in books"
          :key="book.id"
          class="flex gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200"
        >
          <div
            class="flex h-36 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200"
          >
            <img
              v-if="book.image"
              :src="imageUrl(book.image)"
              :alt="book.name"
              class="size-full object-cover"
              loading="lazy"
            />
            <BookOpenIcon v-else class="size-8 text-slate-300" />
          </div>

          <div class="flex min-w-0 flex-1 flex-col">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <h3 class="line-clamp-2 font-semibold text-slate-900">{{ book.name }}</h3>
                <p v-if="book.author" class="mt-0.5 truncate text-sm text-slate-500">
                  {{ book.author }}
                </p>
              </div>
              <span
                class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="
                  book.is_read
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                    : 'bg-slate-50 text-slate-500 ring-slate-200'
                "
              >
                <CheckCircleIcon v-if="book.is_read" class="size-3.5" />
                {{ book.is_read ? 'Přečteno' : 'Nepřečteno' }}
              </span>
            </div>

            <div v-if="book.keywords.length" class="mt-3 flex flex-wrap gap-1.5">
              <button
                v-for="keyword in book.keywords"
                :key="keyword"
                type="button"
                class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100"
                :title="`Hledat „${keyword}“`"
                @click="searchKeyword(keyword)"
              >
                {{ keyword }}
              </button>
            </div>

            <div class="mt-auto flex items-center justify-between gap-2 pt-3">
              <div
                v-if="book.tasks_total"
                class="flex items-center gap-1.5 text-xs font-medium"
                :aria-label="`Úkoly: ${book.tasks_done} splněno, ${book.tasks_total - book.tasks_done} nesplněno`"
              >
                <span
                  class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-1.5 py-0.5 text-emerald-700 ring-1 ring-inset ring-emerald-200"
                  title="Splněné úkoly"
                >
                  <CheckCircleIcon class="size-3.5" />
                  {{ book.tasks_done }}
                </span>
                <span
                  class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 ring-1 ring-inset"
                  :class="
                    book.tasks_total - book.tasks_done
                      ? 'bg-amber-50 text-amber-700 ring-amber-200'
                      : 'bg-slate-50 text-slate-400 ring-slate-200'
                  "
                  title="Nesplněné úkoly"
                >
                  <ClockIcon class="size-3.5" />
                  {{ book.tasks_total - book.tasks_done }}
                </span>
              </div>
              <span v-else class="text-xs text-slate-400">Bez úkolů</span>
              <div class="flex gap-1">
                <NuxtLink
                  :to="`/osobni-rust/knihy/${book.id}`"
                  class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                  :aria-label="`Detail knihy ${book.name}`"
                >
                  <MagnifyingGlassIcon class="size-5" />
                </NuxtLink>
                <button
                  v-if="canDelete('books')"
                  type="button"
                  class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600"
                  :aria-label="`Smazat knihu ${book.name}`"
                  @click="askDelete(book)"
                >
                  <TrashIcon class="size-5" />
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>

      <BasePagination
        v-if="result && result.lastPage > 1"
        :page="result.currentPage"
        :per-page="result.perPage"
        :last-page="result.lastPage"
        :total="result.total"
        @update-page="updatePage"
      />
    </template>

    <BaseDialogDelete
      v-model:show="deleteDialog.show"
      v-model:item="deleteDialog.item"
      :loading="deleteDialog.loading"
      title="Smazat knihu"
      @delete-item="deleteItem"
    />
  </div>
</template>
