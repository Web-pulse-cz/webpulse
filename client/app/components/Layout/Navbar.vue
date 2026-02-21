<script setup>
import { ref } from 'vue';
import {
  Menu,
  MenuButton,
  MenuItems,
  MenuItem
} from '@headlessui/vue';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();

const localePath = useLocalePath();

const { locale, locales, t, setLocale } = useI18n();

const isOpen = ref(false);
</script>

<template>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  <header
    class="fixed top-0 z-50 flex w-full items-center justify-between border-b-4 border-turquoise bg-white px-6 py-4 lg:px-20"
  >
    <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'" class="flex items-center gap-2">
      <div
        class="flex size-10 rotate-12 items-center justify-center rounded-full bg-primary shadow-lg"
      >
        <span class="material-symbols-outlined text-[24px] text-white">auto_stories</span>
      </div>
      <h2 class="font-display text-3xl font-black italic tracking-tighter text-deep-blue">Blog.</h2>
    </NuxtLink>
    <div class="flex flex-1 items-center justify-end gap-8">
      <div
        v-if="
          settingStore.topMenu &&
          settingStore.topMenu['value'] &&
          settingStore.topMenu['value']['groups']
        "
        class="hidden items-center gap-8 md:flex"
      >
        <NuxtLink
          v-for="(group, index) in settingStore.topMenu['value']['groups']"
          :key="index"
          :to="
            localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
          "
          class="text-sm font-black uppercase tracking-widest text-deep-blue transition-colors hover:text-primary"
        >
          {{ group.name }}
        </NuxtLink>
      </div>
      <Menu as="div" class="relative inline-block text-left">
        <MenuButton
          class="flex h-10 items-center gap-1 rounded-xl border-2 border-deep-blue bg-sunny px-4 text-xs font-black text-deep-blue shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] transition-all hover:translate-x-1 hover:translate-y-1 hover:shadow-none focus:outline-none"
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
            class="absolute right-0 mt-3 w-40 origin-top-right overflow-hidden rounded-2xl border-4 border-deep-blue bg-white shadow-[8px_8px_0px_0px_rgba(26,83,92,1)] focus:outline-none"
          >
            <div class="flex flex-col">
              <MenuItem v-for="(lang, index) in locales" :key="index" v-slot="{ active }">
                <span
                  @click="setLocale(lang.code)"
                  :class="[
                    active ? 'bg-primary text-white' : 'text-deep-blue',
                    lang === locale ? 'bg-turquoise/20' : '',
                    'block border-b-2 border-deep-blue px-4 py-3 text-sm font-black uppercase tracking-widest transition-colors last:border-b-0',
                  ]"
                >
                  {{ lang.code }}
                </span>
              </MenuItem>
            </div>
          </MenuItems>
        </transition>
      </Menu>
    </div>
  </header>
</template>
