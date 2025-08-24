<script setup lang="ts">
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/24/outline';

defineProps({
  links: {
    type: Array,
    required: true,
    default: [] as [],
  },
});
</script>

<template>
  <LayoutContainer>
    <nav class="flex items-center" aria-label="Breadcrumb">
      <ol role="list" class="flex items-center space-x-2">
        <li>
          <NuxtLink
            to="/"
            class="flex items-center text-gray-600 transition-colors duration-1000 hover:text-brand"
          >
            <HomeIcon class="size-3 shrink-0 lg:mb-1 lg:size-4" aria-hidden="true" />
            <span class="sr-only">Home</span>
          </NuxtLink>
        </li>
        <li v-for="(link, index) in links" :key="index">
          <div class="flex items-center">
            <ChevronRightIcon class="size-3 shrink-0 text-gray-400 lg:size-4" aria-hidden="true" />
            <NuxtLink
              :to="link.link"
              class="ml-2 rounded-full text-xs transition-colors duration-1000 lg:text-sm"
              :class="
                link.current
                  ? 'bg-red-50 font-semibold text-brand'
                  : 'text-gray-600 hover:bg-gray-100'
              "
              :aria-current="link.current ? 'page' : undefined"
            >
              {{ link.name }}
            </NuxtLink>
          </div>
        </li>
      </ol>
    </nav>
  </LayoutContainer>
</template>
