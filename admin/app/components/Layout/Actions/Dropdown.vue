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
          class="inline-flex items-center rounded-xl bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-colors hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
          aria-label="Odkazy"
        >
          <span class="hidden lg:block">Odkazy</span>
          <ChevronDownIcon
            class="size-5 text-indigo-600 hover:text-indigo-500 lg:-mr-1 lg:ml-2"
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
          class="absolute right-0 z-10 mt-2 w-56 origin-top-right divide-y divide-slate-100 rounded-2xl bg-white p-1.5 shadow-lg ring-1 ring-slate-200 focus:outline-none"
        >
          <div class="space-y-1">
            <MenuItem v-for="(link, key) in links" :key="key" v-slot="{ active }">
              <NuxtLink
                :to="{ name: link.to }"
                :class="[
                  active ? 'bg-slate-50 text-indigo-600' : 'text-slate-700',
                  'group flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium transition-colors',
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
