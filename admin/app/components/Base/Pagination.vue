<script setup lang="ts">
import { computed } from 'vue';
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

const generatedPages = computed(() => {
  const pages: (number | string)[] = [];
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

  return pages;
});
const emit = defineEmits(['update-page']);

function goTo(target: number) {
  if (target < 1 || target > props.lastPage || target === props.page) return;
  emit('update-page', Number(target));
}
</script>

<template>
  <div
    class="flex items-center justify-between border-t border-slate-200 bg-white px-4 py-4 sm:px-6"
  >
    <nav class="flex flex-1 justify-between sm:hidden" aria-label="Stránkování">
      <button
        type="button"
        :disabled="page <= 1"
        aria-label="Předchozí stránka"
        class="relative inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white disabled:active:scale-100"
        @click="goTo(page - 1)"
      >
        Předchozí
      </button>
      <span class="self-center text-sm text-slate-500">{{ page }} / {{ lastPage }}</span>
      <button
        type="button"
        :disabled="page >= lastPage"
        aria-label="Následující stránka"
        class="relative inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white disabled:active:scale-100"
        @click="goTo(page + 1)"
      >
        Následující
      </button>
    </nav>

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

      <nav class="isolate inline-flex -space-x-px rounded-xl shadow-sm" aria-label="Stránkování">
        <button
          type="button"
          :disabled="page <= 1"
          aria-label="Předchozí stránka"
          class="relative inline-flex items-center rounded-l-xl px-2.5 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 focus:z-20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
          @click="goTo(page - 1)"
        >
          <ChevronLeftIcon class="size-5" aria-hidden="true" />
        </button>

        <template v-for="(generatedPage, key) in generatedPages" :key="key">
          <span
            v-if="generatedPage === '...'"
            class="relative inline-flex cursor-default items-center px-4 py-2 text-sm font-semibold text-slate-400 ring-1 ring-inset ring-slate-300"
            aria-hidden="true"
          >
            …
          </span>
          <button
            v-else
            type="button"
            :aria-label="`Stránka ${generatedPage}`"
            :aria-current="generatedPage === page ? 'page' : undefined"
            :class="[
              generatedPage === page
                ? 'z-10 bg-indigo-600 text-white ring-1 ring-inset ring-indigo-600'
                : 'text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
              'relative inline-flex items-center px-4 py-2 text-sm font-semibold transition-colors focus:z-20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1',
            ]"
            @click="goTo(Number(generatedPage))"
          >
            {{ generatedPage }}
          </button>
        </template>

        <button
          type="button"
          :disabled="page >= lastPage"
          aria-label="Následující stránka"
          class="relative inline-flex items-center rounded-r-xl px-2.5 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 focus:z-20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
          @click="goTo(page + 1)"
        >
          <ChevronRightIcon class="size-5" aria-hidden="true" />
        </button>
      </nav>
    </div>
  </div>
</template>
