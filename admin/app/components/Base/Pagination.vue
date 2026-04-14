<script setup lang="ts">
import { ref } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
  page: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    required: true,
  },
  lastPage: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
});

const generatedPages = ref([]);
function generatePages() {
  const pages = [];
  const { page, lastPage } = props;

  if (lastPage <= 6) {
    for (let i = 1; i <= lastPage; i++) {
      pages.push(i);
    }
  } else {
    pages.push(1);
    if (page > 3) {
      pages.push('...');
    }
    for (let i = Math.max(2, page - 1); i <= Math.min(lastPage - 1, page + 1); i++) {
      pages.push(i);
    }
    if (page < lastPage - 2) {
      pages.push('...');
    }
    pages.push(lastPage);
  }

  generatedPages.value = pages;
}
const emit = defineEmits(['update-page']);
generatePages();
</script>

<template>
  <div
    class="flex items-center justify-between border-t border-slate-200 bg-white px-4 py-4 sm:px-6"
  >
    <div class="flex flex-1 justify-between sm:hidden">
      <span
        :class="[
          page === 1
            ? 'cursor-not-allowed opacity-50'
            : 'cursor-pointer hover:bg-slate-50 active:scale-95',
          'relative inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200',
        ]"
        @click="page !== 1 ? emit('update-page', Number(page - 1)) : null"
      >
        Předchozí
      </span>
      <span
        :class="[
          page === lastPage
            ? 'cursor-not-allowed opacity-50'
            : 'cursor-pointer hover:bg-slate-50 active:scale-95',
          'relative ml-3 inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200',
        ]"
        @click="page !== lastPage ? emit('update-page', Number(page + 1)) : null"
      >
        Následující
      </span>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-slate-500">
          Zobrazeno
          {{ ' ' }}
          <span class="font-semibold text-slate-900">{{ (page - 1) * perPage + 1 }}</span>
          {{ ' ' }}
          ─
          {{ ' ' }}
          <span class="font-semibold text-slate-900">{{ Math.min(page * perPage, total) }}</span>
          {{ ' ' }}
          z
          {{ ' ' }}
          <span class="font-semibold text-slate-900">{{ total }}</span>
          {{ ' ' }}
          výsledků
        </p>
      </div>

      <div>
        <nav class="isolate inline-flex -space-x-px rounded-xl shadow-sm" aria-label="Pagination">
          <span
            :class="[
              page === 1 ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-slate-50',
              'relative inline-flex items-center rounded-l-xl px-2.5 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 transition-colors focus:z-20',
            ]"
            @click="page !== 1 ? emit('update-page', Number(page - 1)) : null"
          >
            <span class="sr-only">Předchozí</span>
            <ChevronLeftIcon class="size-5" aria-hidden="true" />
          </span>

          <span
            v-for="(generatedPage, key) in generatedPages"
            :key="key"
            :class="[
              generatedPage === page
                ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                : 'text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
              generatedPage === '...' ? 'cursor-default' : 'cursor-pointer',
              'relative inline-flex items-center px-4 py-2 text-sm font-semibold transition-colors',
            ]"
            @click="
              generatedPage != '...' && generatedPage !== page
                ? emit('update-page', Number(generatedPage))
                : null
            "
          >
            {{ generatedPage }}
          </span>

          <span
            :class="[
              page === lastPage
                ? 'cursor-not-allowed opacity-50'
                : 'cursor-pointer hover:bg-slate-50',
              'relative inline-flex items-center rounded-r-xl px-2.5 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 transition-colors focus:z-20',
            ]"
            @click="page !== lastPage ? emit('update-page', Number(page + 1)) : null"
          >
            <span class="sr-only">Následující</span>
            <ChevronRightIcon class="size-5" aria-hidden="true" />
          </span>
        </nav>
      </div>
    </div>
  </div>
</template>
