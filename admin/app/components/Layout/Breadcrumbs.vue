<script setup lang="ts">
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/24/outline';

defineProps({
  pages: {
    type: Array,
    required: true,
    default: [] as [],
  },
});
</script>

<template>
  <nav
    class="scrollbar-hide flex overflow-x-auto px-1 py-2 sm:overflow-visible"
    aria-label="Drobečková navigace"
  >
    <ol role="list" class="flex min-w-0 items-center space-x-3">
      <li class="shrink-0">
        <div class="flex items-center">
          <NuxtLink
            to="/"
            class="rounded text-slate-400 transition-colors duration-200 hover:text-indigo-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
          >
            <HomeIcon class="size-4 shrink-0" aria-hidden="true" />
            <span class="sr-only">Domů</span>
          </NuxtLink>
        </div>
      </li>

      <li v-for="(page, index) in pages" :key="index" class="min-w-0 shrink-0 sm:shrink">
        <div class="flex min-w-0 items-center">
          <ChevronRightIcon class="size-4 shrink-0 text-slate-300" aria-hidden="true" />

          <NuxtLink
            :to="page.link"
            :title="page.name"
            :class="[
              page.current
                ? 'pointer-events-none font-semibold text-slate-900'
                : 'text-slate-500 hover:text-indigo-600',
              'ml-3 block max-w-[12rem] truncate rounded text-xs font-medium transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sm:max-w-[16rem] sm:text-sm',
            ]"
            :aria-current="page.current ? 'page' : undefined"
          >
            {{ page.name }}
          </NuxtLink>
        </div>
      </li>
    </ol>
  </nav>
</template>
