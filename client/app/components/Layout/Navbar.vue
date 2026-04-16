<script setup>
import { ref } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();

const localePath = useLocalePath();

const { locale, locales, setLocale } = useI18n();

const isOpen = ref(false);
const mobileMenuOpen = ref(false);
</script>

<template>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  <header
    class="fixed top-0 z-50 flex w-full items-center justify-between border-b border-slate-200 bg-white/80 px-6 py-3 backdrop-blur-sm lg:px-20"
  >
    <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'" class="flex items-center gap-2.5">
      <div class="flex size-9 items-center justify-center rounded-lg bg-primary shadow-sm">
        <span class="material-symbols-outlined text-[20px] text-white">language</span>
      </div>
      <span class="text-xl font-semibold tracking-tight text-slate-900">Site</span>
    </NuxtLink>

    <div class="flex flex-1 items-center justify-end gap-6">
      <div
        v-if="
          settingStore.topMenu &&
          settingStore.topMenu['value'] &&
          settingStore.topMenu['value']['groups']
        "
        class="hidden items-center gap-6 md:flex"
      >
        <NuxtLink
          v-for="(group, index) in settingStore.topMenu['value']['groups']"
          :key="index"
          :to="
            localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
          "
          class="text-sm font-medium text-slate-700 transition-colors hover:text-primary"
        >
          {{ group.name }}
        </NuxtLink>
      </div>

      <Menu as="div" class="relative inline-block text-left">
        <MenuButton
          class="flex h-9 items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/20"
        >
          <span class="uppercase">{{ locale }}</span>
          <span class="material-symbols-outlined text-[16px]">expand_more</span>
        </MenuButton>

        <transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="transform scale-95 opacity-0"
          enter-to-class="transform scale-100 opacity-100"
          leave-active-class="transition duration-75 ease-in"
          leave-from-class="transform scale-100 opacity-100"
          leave-to-class="transform scale-95 opacity-0"
        >
          <MenuItems
            class="absolute right-0 mt-2 w-36 origin-top-right overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg focus:outline-none"
          >
            <div class="flex flex-col">
              <MenuItem v-for="(lang, index) in locales" :key="index" v-slot="{ active }">
                <span
                  :class="[
                    active ? 'bg-primary text-white' : 'text-slate-700',
                    lang === locale ? 'bg-primary/5' : '',
                    'block cursor-pointer px-4 py-2.5 text-sm font-medium uppercase tracking-wide transition-colors',
                  ]"
                  @click="setLocale(lang.code)"
                >
                  {{ lang.code }}
                </span>
              </MenuItem>
            </div>
          </MenuItems>
        </transition>
      </Menu>

      <!-- Mobile menu button -->
      <button
        class="inline-flex items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100 md:hidden"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        <Bars3Icon v-if="!mobileMenuOpen" class="size-6" />
        <XMarkIcon v-else class="size-6" />
      </button>
    </div>
  </header>

  <!-- Mobile menu -->
  <transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="mobileMenuOpen"
      class="fixed left-0 right-0 top-[57px] z-40 border-b border-slate-200 bg-white px-6 py-4 shadow-lg md:hidden"
    >
      <div
        v-if="
          settingStore.topMenu &&
          settingStore.topMenu['value'] &&
          settingStore.topMenu['value']['groups']
        "
        class="flex flex-col gap-2"
      >
        <NuxtLink
          v-for="(group, index) in settingStore.topMenu['value']['groups']"
          :key="index"
          :to="
            localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
          "
          class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 hover:text-primary"
          @click="mobileMenuOpen = false"
        >
          {{ group.name }}
        </NuxtLink>
      </div>
    </div>
  </transition>
</template>
