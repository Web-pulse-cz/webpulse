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
  <nav class="flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-2">
      <li>
        <div>
          <NuxtLink to="/admin/public" class="text-gray-400 hover:text-grayCustom">
            <HomeIcon class="size-3 shrink-0 lg:size-4" aria-hidden="true" />
            <span class="sr-only">Home</span>
          </NuxtLink>
        </div>
      </li>
      <li v-for="page in pages" :key="page.name">
        <div class="flex items-center">
          <ChevronRightIcon class="size-3 shrink-0 text-gray-400 lg:size-4" aria-hidden="true" />
          <NuxtLink
            :to="page.link"
            class="ml-2 text-xs font-medium text-grayCustom hover:text-gray-700 lg:text-sm"
            :aria-current="page.current ? 'page' : undefined"
            >{{ page.name }}</NuxtLink
          >
        </div>
      </li>
    </ol>
  </nav>
</template>
