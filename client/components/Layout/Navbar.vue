<script setup>
import { ref } from "vue";

import {
  Dialog,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
} from "@headlessui/vue";
import {
  ArrowPathIcon,
  Bars3Icon,
  ChartPieIcon,
  CursorArrowRaysIcon,
  FingerPrintIcon,
  SquaresPlusIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";
import {
  ChevronDownIcon,
  PhoneIcon,
  PlayCircleIcon,
} from "@heroicons/vue/20/solid";

const { locale, locales, t } = useI18n();
const switchLocalePath = useSwitchLocalePath();

const company = [
  { name: "About us", href: "#" },
  { name: "Careers", href: "#" },
  { name: "Support", href: "#" },
  { name: "Press", href: "#" },
  { name: "Blog", href: "#" },
];

const mobileMenuOpen = ref(false);
</script>

<template>
  <header class="fixed backdrop-blur-md w-full z-10">
    <nav
      class="mx-auto flex max-w-xxl items-center justify-between px-8 lg:px-24 2xl:px-48 py-6 lg:py-0"
      aria-label="Global"
    >
      <div class="flex lg:flex-1">
        <a href="#" class="-m-1.5 p-1.5">
          <span class="sr-only">Your Company</span>
          <img
            class="h-8 w-auto"
            src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
            alt=""
          />
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
      <PopoverGroup class="hidden lg:flex lg:gap-x-2">
        <a
          href="#"
          class="text-base font-semibold text-primary px-6 py-6 hover:bg-primary hover:text-light"
          >{{ t("navbar.home") }}</a
        >
        <a
          href="#"
          class="text-base font-semibold text-primary px-6 py-6 hover:bg-primary hover:text-light"
          >{{ t("navbar.about") }}</a
        >
        <a
          href="#"
          class="text-base font-semibold text-primary px-6 py-6 hover:bg-primary hover:text-light"
          >{{ t("navbar.portfolio") }}</a
        >
        <a
          href="#"
          class="text-base font-semibold text-primary px-6 py-6 hover:bg-primary hover:text-light"
          >{{ t("navbar.services") }}</a
        >
        <a
          href="#"
          class="text-base font-semibold text-primary px-6 py-6 hover:bg-primary hover:text-light"
          >{{ t("navbar.contact") }}</a
        >
        <Popover class="relative px-6 py-6 hover:bg-primary hover:text-dark">
          <PopoverButton
            class="flex items-center gap-x-1 text-sm/6 text-primary"
          >
            <img
              :src="'/static/img/flags/' + locale + '.svg'"
              class="w-6 h-6"
              alt="Locale Flag"
            />
            <ChevronDownIcon
              class="size-5 flex-none text-primary"
              aria-hidden="true"
            />
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
              class="absolute -left-8 top-full z-10 w-56 mt-[1px] rounded backdrop-blur-md p-2 shadow-lg ring-1 ring-zinc-700 flex"
            >
              <NuxtLink
                v-for="locale in locales"
                :key="locale.code"
                class="block rounded-lg px-2.5 py-2 text-sm/6 text-primary hover:bg-primary"
                :to="switchLocalePath(locale.code)"
              >
                <img
                  :src="'/static/img/flags/' + locale.code + '.svg'"
                  class="w-8 h-8"
                  alt="Locale Flag"
                />
              </NuxtLink>
            </PopoverPanel>
          </transition>
        </Popover>
      </PopoverGroup>
    </nav>
    <Dialog
      class="lg:hidden"
      :open="mobileMenuOpen"
      @close="mobileMenuOpen = false"
    >
      <div class="fixed inset-0 z-10" />
      <DialogPanel
        class="fixed inset-y-0 right-0 z-10 w-full overflow-y-auto backdrop-blur-md px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10"
      >
        <div class="flex items-center justify-between">
          <a href="#" class="-m-1.5 p-1.5">
            <span class="sr-only">Your Company</span>
            <img
              class="h-8 w-auto"
              src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
              alt=""
            />
          </a>
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
            <div class="space-y-2 py-6">
              <a
                href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base text-primary hover:bg-primary hover:text-light font-medium"
                >{{ t("navbar.home") }}</a
              >
              <a
                href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base text-primary hover:bg-primary hover:text-light font-medium"
                >{{ t("navbar.about") }}</a
              >
              <a
                href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base text-primary hover:bg-primary hover:text-light font-medium"
                >{{ t("navbar.portfolio") }}</a
              >
              <a
                href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base text-primary hover:bg-primary hover:text-light font-medium"
                >{{ t("navbar.services") }}</a
              >
              <a
                href="#"
                class="-mx-3 block rounded-lg px-3 py-2 text-base text-primary hover:bg-primary hover:text-light font-medium"
                >{{ t("navbar.technologies") }}</a
              >
            </div>
            <div class="py-6 flex justify-between">
              <NuxtLink
                v-for="locale in locales"
                :key="locale.code"
                class="rounded-lg py-2 px-2.5 pr-3 text-sm/7 text-primary hover:bg-primary hover:text-light"
                :to="switchLocalePath(locale.code)"
              >
                <img
                  :src="'/static/img/flags/' + locale.code + '.svg'"
                  class="w-8 h-8"
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
