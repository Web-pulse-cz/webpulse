<script setup lang="ts">
import { useSettingStore } from '~/../stores/settingStore.js';

const localePath = useLocalePath();
const settingStore = useSettingStore();
defineProps({
  variant: {
    type: String,
    default: 'newsletter',
  },
});
const { locale } = useI18n();
const toast = useToast();

const email = ref('');

const _handleSubmit = () => {
  const api = useApi();
  useAsyncData('newsletter', () =>
    api.global
      .newsletter(email.value, locale.value)
      .then(() => {
        toast.success({
          title: 'Success!',
          message: 'Your action was completed successfully.',
          position: 'topRight',
        });
      })
      .catch(() => {
        toast.error({ title: 'Error!', message: 'Something went wrong.', position: 'topRight' });
      }),
  );
};
</script>

<template>
  <footer class="bg-slate-900 px-6 pb-12 pt-20 text-white lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-3">
        <div class="col-span-1 md:col-span-1">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-primary">
              <span class="material-symbols-outlined text-[24px] text-white">language</span>
            </div>
            <span class="text-xl font-semibold tracking-tight">Site</span>
          </div>
          <p class="text-sm leading-relaxed text-slate-400">
            A modern content management platform. Build and manage your digital presence with ease.
          </p>
        </div>
        <template
          v-if="
            settingStore.bottomMenu &&
            settingStore.bottomMenu['value'] &&
            settingStore.bottomMenu['value']['groups']
          "
        >
          <div v-for="(group, index) in settingStore.bottomMenu['value']['groups']" :key="index">
            <h3 class="mb-6 text-sm font-semibold uppercase tracking-wider text-slate-300">
              {{ group.name }}
            </h3>
            <ul v-if="group.submenu && group.submenu.length" class="space-y-3">
              <li v-for="(link, key) in group.submenu" :key="key">
                <NuxtLink
                  :to="
                    localePath({
                      name: link.link !== '' && link.link !== null ? link.link : 'index',
                    })
                  "
                  class="text-sm text-slate-400 transition-colors hover:text-white"
                >
                  {{ link.name }}
                </NuxtLink>
              </li>
            </ul>
          </div>
        </template>
      </div>
      <div
        class="flex flex-col items-center justify-between gap-6 border-t border-slate-800 pt-8 md:flex-row"
      >
        <p class="text-sm text-slate-500">
          &copy; {{ new Date().getFullYear() }} All rights reserved.
        </p>
        <div class="flex gap-6 text-sm text-slate-500">
          <a class="transition-colors hover:text-white" href="#">Privacy</a>
          <a class="transition-colors hover:text-white" href="#">Terms</a>
          <a class="transition-colors hover:text-white" href="#">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>
