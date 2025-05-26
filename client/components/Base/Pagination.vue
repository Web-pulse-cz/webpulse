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
    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
  >
    <div class="flex flex-1 justify-between sm:hidden">
      <span
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 lg:px-4 lg:py-2 lg:text-sm"
        @click="page !== 1 ? emit('update-page', Number(page - 1)) : null"
        >Předchozí</span
      >
      <span
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 lg:px-4 lg:py-2 lg:text-sm"
        @click="page !== lastPage ? emit('update-page', Number(page + 1)) : null"
        >Následující</span
      >
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-grayLight text-sm">
          Zobrazeno
          {{ ' ' }}
          <span class="font-medium">{{ (page - 1) * perPage + 1 }}</span>
          {{ ' ' }}
          ─
          {{ ' ' }}
          <span class="font-medium">{{ Math.min(page * perPage, total) }}</span>
          {{ ' ' }}
          z
          {{ ' ' }}
          <span class="font-medium">{{ total }}</span>
          {{ ' ' }}
          výsledků
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <span
            :class="[
              page === 1 ? 'cursor-not-allowed' : 'cursor-pointer',
              'relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
            ]"
          >
            <span class="sr-only">Předchozí</span>
            <ChevronLeftIcon
              class="size-5"
              aria-hidden="true"
              @click="page !== 1 ? emit('update-page', Number(page - 1)) : null"
            />
          </span>
          <!-- Current: "z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600", Default: "text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0" -->
          <span
            v-for="(generatedPage, key) in generatedPages"
            :key="key"
            :class="[
              generatedPage === page
                ? 'bg-primaryCustom text-white'
                : 'text-grayDark ring-1 ring-inset ring-gray-300',
              'relative z-10 inline-flex cursor-pointer items-center px-4 py-2 text-sm font-semibold',
            ]"
            @click="
              generatedPage != '...' && generatedPage !== page
                ? emit('update-page', Number(generatedPage))
                : null
            "
            >{{ generatedPage }}</span
          >
          <span
            :class="[
              page === lastPage ? 'cursor-not-allowed' : 'cursor-pointer',
              'relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
            ]"
          >
            <span class="sr-only">Následující</span>
            <ChevronRightIcon
              class="size-5"
              aria-hidden="true"
              @click="page !== lastPage ? emit('update-page', Number(page + 1)) : null"
            />
          </span>
        </nav>
      </div>
    </div>
  </div>
</template>
