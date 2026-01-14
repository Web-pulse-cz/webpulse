<script setup>
import { ref } from 'vue';
import {
  Dialog,
  DialogPanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
} from '@headlessui/vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import { ChevronDownIcon, PhoneIcon, PlayCircleIcon } from '@heroicons/vue/20/solid';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();

const localePath = useLocalePath();

const { locale, locales, t } = useI18n();
const switchLocalePath = useSwitchLocalePath();

const mobileMenuOpen = ref(false);
const isOpen = ref(false);
const topMenu = ref({});
</script>

<template>
  <BaseModalContactForm :open="isOpen" @close="isOpen = false" />
  <header class="fixed z-10 w-full bg-chppGray backdrop-blur-md">
    <nav
      class="mx-auto flex max-w-[1880px] items-center justify-between px-8 py-6 lg:px-8 lg:py-0 2xl:px-72"
      aria-label="Global"
    >
      <div class="flex lg:flex-1">
        <a class="-m-1.5 p-1.5">
          <span class="sr-only">CHPP s. r. o.</span>
          <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'">
            <img class="h-14 w-auto" src="~/../public/static/img/LOGO-CHPP.svg" alt="logo" />
          </NuxtLink>
        </a>
      </div>
      <div class="flex lg:hidden">
        <button
          type="button"
          class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-primary"
          @click="mobileMenuOpen = true"
        >
          <span class="sr-only">Open main menu</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>
      </div>
      <PopoverGroup class="hidden lg:flex lg:gap-x-3">
        <div
          v-if="
            settingStore.topMenu &&
            settingStore.topMenu['value'] &&
            settingStore.topMenu['value']['groups']
          "
          class="hidden justify-center text-center lg:flex lg:gap-x-3"
        >
          <NuxtLink
            v-for="(group, index) in settingStore.topMenu['value']['groups']"
            :key="index"
            :to="
              localePath({ name: group.link !== '' && group.link !== null ? group.link : 'index' })
            "
            class="duration-30 px-6 py-6 text-base font-semibold text-primary transition-colors hover:text-brand"
          >
            {{ group.name }}
          </NuxtLink>
        </div>

        <div class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-end lg:gap-x-3">
          <NuxtLink
            :to="
              localePath({
                name: 'info-id-slug',
                params: { id: 1, slug: 'zasady-zpracovani-osobnich-udaju' },
              })
            "
            class="duration-30 px-6 py-6 text-base font-semibold text-brand transition-colors hover:text-brand"
            >{{ t('general.aboutUs') }}</NuxtLink
          >

          <BaseButton variant="primary" size="sm" @click="isOpen = true">{{
            t('contactForm.button')
          }}</BaseButton>

          <Popover class="hover:text-dark relative py-6 pl-6">
            <PopoverButton class="flex items-center gap-x-1 text-sm/6 text-primary">
              <img
                :src="'/static/img/flags/' + locale + '.svg'"
                class="h-6 w-6"
                alt="Locale Flag"
              />
              <ChevronDownIcon class="size-5 flex-none text-primary" aria-hidden="true" />
            </PopoverButton>

            <transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 translate-y-1"
            >
              <PopoverPanel
                class="absolute left-1 top-full z-10 mt-[1px] flex flex-col rounded p-2 shadow-lg ring-1 ring-zinc-700 backdrop-blur-md"
              >
                <NuxtLink
                  v-for="locale in locales"
                  :key="locale.code"
                  class="block rounded-lg px-2.5 py-2 text-sm/6 text-primary hover:bg-brand"
                  :to="switchLocalePath(locale.code)"
                >
                  <img
                    :src="'/static/img/flags/' + locale.code + '.svg'"
                    class="h-8 w-8"
                    alt="Locale Flag"
                  />
                </NuxtLink>
              </PopoverPanel>
            </transition>
          </Popover>
        </div>
      </PopoverGroup>
    </nav>
    <Dialog class="lg:hidden" :open="mobileMenuOpen" @close="mobileMenuOpen = false">
      <div class="fixed inset-0 z-10" />
      <DialogPanel
        class="fixed inset-y-0 right-0 z-10 w-full overflow-y-auto px-6 py-6 backdrop-blur-md sm:max-w-sm sm:ring-1 sm:ring-gray-900/10"
      >
        <div class="flex items-center justify-between">
          <NuxtLink :to="locale !== 'cs' ? `/${locale}` : '/'" class="-m-1.5 p-1.5">
            <span class="sr-only">Your Company</span>
            <img class="h-8 w-auto" src="~/../public/static/img/LOGO-CHPP.svg" alt="" />
          </NuxtLink>
          <button
            type="button"
            class="-m-2.5 rounded-md p-2.5 text-primary"
            @click="mobileMenuOpen = false"
          >
            <span class="sr-only">Close menu</span>
            <XMarkIcon class="size-6" aria-hidden="true" />
          </button>
        </div>
        <div class="mt-6 flow-root">
          <div class="-my-6 divide-y divide-gray-500/10">
            <div
              v-if="
                settingStore.topMenu &&
                settingStore.topMenu['value'] &&
                settingStore.topMenu['value']['groups']
              "
              class="space-y-2 py-6"
            >
              <NuxtLink
                v-for="(group, index) in settingStore.topMenu['value']['groups']"
                :key="index"
                :to="
                  localePath({
                    name: group.link !== '' && group.link !== null ? group.link : 'index',
                  })
                "
                class="-mx-3 block rounded-lg px-3 py-2 text-base font-medium text-primary hover:text-brand"
              >
                {{ group.name }}
              </NuxtLink>
              <NuxtLink
                :to="
                  localePath({
                    name: 'info-id-slug',
                    params: { id: 1, slug: 'zasady-zpracovani-osobnich-udaju' },
                  })
                "
                class="-mx-3 block rounded-lg px-3 py-2 text-base font-medium text-primary hover:text-brand"
                >{{ t('general.aboutUs') }}</NuxtLink
              >
            </div>
            <div class="flex justify-between py-6">
              <NuxtLink
                v-for="locale in locales"
                :key="locale.code"
                class="rounded-lg px-2.5 py-2 pr-3 text-sm/7 text-primary hover:text-brand"
                :to="switchLocalePath(locale.code)"
              >
                <img
                  :src="'/static/img/flags/' + locale.code + '.svg'"
                  class="h-8 w-8"
                  alt="Locale Flag"
                />
              </NuxtLink>
            </div>
          </div>
        </div>
      </DialogPanel>
    </Dialog>
  </header>
</template>
