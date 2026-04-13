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
  <nav class="flex px-1 py-2" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-3">
      <li>
        <div class="flex items-center">
          <NuxtLink
            to="/"
            class="text-slate-400 transition-colors duration-200 hover:text-indigo-600"
          >
            <HomeIcon class="size-4 shrink-0" aria-hidden="true" />
            <span class="sr-only">Domů</span>
          </NuxtLink>
        </div>
      </li>

      <li v-for="page in pages" :key="page.name">
        <div class="flex items-center">
          <ChevronRightIcon class="size-4 shrink-0 text-slate-300" aria-hidden="true" />

          <NuxtLink
            :to="page.link"
            :class="[
              page.current
                ? 'pointer-events-none font-semibold text-slate-900'
                : 'text-slate-500 hover:text-indigo-600',
              'ml-3 text-xs font-medium transition-colors duration-200 sm:text-sm',
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
