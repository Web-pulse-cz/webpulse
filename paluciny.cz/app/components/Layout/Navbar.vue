<script setup>
import { ref } from 'vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();

const localePath = useLocalePath();

const { locale } = useI18n();

const isOpen = ref(false);
const mobileMenuOpen = ref(false);
</script>

<template>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  <header
    class="fixed top-0 z-50 flex w-full items-center justify-between border-b border-cream-dark bg-cream/95 px-6 py-3 backdrop-blur-sm lg:px-20"
  >
    <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'" class="flex items-center gap-2.5">
      <div class="flex size-9 items-center justify-center rounded-full bg-forest shadow-sm">
        <span class="material-symbols-outlined text-[20px] text-white">forest</span>
      </div>
      <span class="font-display text-xl font-semibold italic tracking-tight text-earth"
        >Palu&#269;iny</span
      >
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
        <template v-for="(group, index) in settingStore.topMenu['value']['groups']" :key="index">
          <NuxtLink
            v-if="index < settingStore.topMenu['value']['groups'].length - 1"
            :to="
              localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
            "
            class="text-sm font-medium text-earth transition-colors hover:text-forest"
          >
            {{ group.name }}
          </NuxtLink>
          <NuxtLink
            v-else
            :to="
              localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
            "
            class="rounded-full bg-forest px-6 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-forest-dark"
          >
            {{ group.name }}
          </NuxtLink>
        </template>
      </div>

      <!-- Mobile menu button -->
      <button
        class="inline-flex items-center justify-center rounded-lg p-2 text-earth hover:bg-cream-dark md:hidden"
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
      class="fixed left-0 right-0 top-[57px] z-40 border-b border-cream-dark bg-cream px-6 py-4 shadow-lg md:hidden"
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
          class="rounded-lg px-3 py-2 text-sm font-medium text-earth transition-colors hover:bg-cream-dark hover:text-forest"
          @click="mobileMenuOpen = false"
        >
          {{ group.name }}
        </NuxtLink>
      </div>
    </div>
  </transition>
</template>
