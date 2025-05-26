<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';

defineProps({
  links: {
    type: Array,
    required: true,
    default: [] as [],
  },
});
</script>

<template>
  <div class="ml-4 text-right">
    <Menu as="div" class="relative inline-block text-left">
      <div>
        <MenuButton
          class="inline-flex rounded-md bg-white px-3.5 py-2.5 text-sm text-grayDark shadow-sm ring-1 ring-inset ring-grayLight hover:bg-gray-50 focus-visible:outline-grayLight"
        >
          <span class="hidden lg:block">Odkazy</span>
          <ChevronDownIcon
            class="h-5 w-5 text-primaryCustom hover:text-primaryLight lg:-mr-1 lg:ml-2"
            aria-hidden="true"
          />
        </MenuButton>
      </div>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <MenuItems
          class="absolute right-0 z-10 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
        >
          <div class="px-1 py-1">
            <MenuItem v-for="(link, key) in links" :key="key" v-slot="{ active }">
              <NuxtLink
                :to="{ name: link.to }"
                :class="[
                  active ? 'bg-gray-100 text-grayDark' : 'text-grayDark',
                  'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                ]"
              >
                {{ link.name }}
              </NuxtLink>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>
