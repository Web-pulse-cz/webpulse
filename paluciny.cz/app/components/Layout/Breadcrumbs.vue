<script setup lang="ts">
import { ChevronRightIcon, HomeIcon } from '@heroicons/vue/24/outline';

const localePath = useLocalePath();

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
            :to="localePath('/')"
            class="flex items-center text-earth-light transition-colors duration-300 hover:text-forest"
          >
            <HomeIcon class="size-3 shrink-0 lg:mb-1 lg:size-4" aria-hidden="true" />
            <span class="sr-only">Home</span>
          </NuxtLink>
        </li>
        <li v-for="(link, index) in links" :key="index">
          <div class="flex items-center">
            <ChevronRightIcon
              class="size-3 shrink-0 text-earth-light lg:size-4"
              aria-hidden="true"
            />
            <NuxtLink
              :to="link.link"
              class="ml-2 rounded-full text-xs transition-colors duration-300 lg:text-sm"
              :class="
                link.current
                  ? 'bg-forest/10 font-semibold text-forest'
                  : 'text-earth-light hover:text-forest'
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
