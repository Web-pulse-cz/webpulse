<script setup lang="ts">
import { defineComponent, h } from 'vue';
import { useSettingStore } from '~/../stores/settingStore.js';
import { useGlobalApi } from '~/../api/global.js';

const localePath = useLocalePath();

const settingStore = useSettingStore();
const props = defineProps({
  variant: {
    type: String,
    default: 'newsletter',
  },
});
const { t, locale } = useI18n();
const toast = useToast();

const navigation = {
  social: [
    {
      name: 'Linkedin',
      href: 'https://cz.linkedin.com/in/martin-hanzl-618784173',
      icon: defineComponent({
        render: () =>
          h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', {
              'fill-rule': 'evenodd',
              d: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
              'clip-rule': 'evenodd',
            }),
          ]),
      }),
    },
  ],
};
const email = ref('');

const handleSubmit = () => {
  const api = useApi();
  const {
    data: newsletterData,
    pending: newsletterPending,
    error: newsletterError,
  } = useAsyncData('newsletter', () =>
    api.global
      .newsletter(email.value, locale.value)
      .then((response) => {
        toast.success({
          title: 'Success!',
          message: 'Your action was completed successfully.',
          position: 'topRight',
        });
      })
      .catch((error) => {
        toast.error({ title: 'Error!', message: 'Something went wrong.', position: 'topRight' });
      }),
  );
};

const textClass = computed(() => (index: number) => {
  switch (index) {
    case 0:
      return 'text-primary';
    case 1:
      return 'text-turquoise';
  }
});
</script>

<template>
  <footer class="border-t-8 border-primary bg-deep-blue px-6 pb-12 pt-24 text-white lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <div class="mb-20 grid grid-cols-1 gap-16 md:grid-cols-3">
        <div class="col-span-1 md:col-span-1">
          <div class="mb-8 flex items-center gap-4">
            <div
              class="flex size-12 -rotate-12 items-center justify-center rounded-blob bg-primary"
            >
              <span class="material-symbols-outlined text-[32px]">auto_stories</span>
            </div>
            <h2 class="font-display text-4xl font-black italic">Blog.</h2>
          </div>
          <p class="text-lg font-medium leading-relaxed text-white/70">
            A place for thoughtful writing and meaningful ideas. Join our community of curious
            minds.
          </p>
        </div>
        <div
          v-for="(group, index) in settingStore.bottomMenu['value']['groups']"
          v-if="
            settingStore.bottomMenu &&
            settingStore.bottomMenu['value'] &&
            settingStore.bottomMenu['value']['groups']
          "
          :key="index"
        >
          <h3
            :class="
              'mb-8 font-display text-2xl font-black uppercase italic tracking-widest ' +
              textClass(index)
            "
          >
            {{ group.name }}
          </h3>
          <ul v-if="group.submenu && group.submenu.length" class="space-y-4 text-lg font-bold">
            <li v-for="(link, key) in group.submenu" :key="key">
              <NuxtLink
                :to="
                  localePath({ name: link.link !== '' && link.link !== null ? link.link : 'index' })
                "
                class="transition-colors hover:text-turquoise"
                href="#"
                >{{ link.name }}</NuxtLink
              >
            </li>
          </ul>
        </div>
      </div>
      <div
        class="flex flex-col items-center justify-between gap-8 border-t-2 border-white/10 pt-12 md:flex-row"
      >
        <p class="text-sm font-bold italic text-white/50">
          © 2026 Blog App. Stay Wild. Keep Reading.
        </p>
        <div class="flex gap-8 text-sm font-black uppercase tracking-widest text-white/70">
          <a class="transition-colors hover:text-primary" href="#">Privacy</a>
          <a class="transition-colors hover:text-primary" href="#">Terms</a>
          <a class="transition-colors hover:text-primary" href="#">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>
