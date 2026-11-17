<script setup lang="ts">
import { MenuItems } from '@headlessui/vue';

// Shared header dropdown panel (Headless UI <Menu> child): transition + styled <MenuItems>.
withDefaults(
  defineProps<{
    /** Tailwind width class of the panel. */
    width?: string;
  }>(),
  { width: 'w-56' },
);
</script>

<template>
  <transition
    enter-active-class="transition ease-out duration-150"
    enter-from-class="transform opacity-0 scale-95 translate-y-1"
    enter-to-class="transform opacity-100 scale-100 translate-y-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="transform opacity-100 scale-100 translate-y-0"
    leave-to-class="transform opacity-0 scale-95 translate-y-1"
  >
    <MenuItems
      class="absolute right-0 z-10 mt-3 origin-top-right rounded-2xl bg-white p-1.5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 focus:outline-none"
      :class="width"
    >
      <slot />
    </MenuItems>
  </transition>
</template>
