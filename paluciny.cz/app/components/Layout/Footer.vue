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
  <footer class="relative overflow-hidden bg-forest-dark px-6 pb-12 pt-20 text-white lg:px-20">
    <!-- Decorative leaf -->
    <svg
      class="pointer-events-none absolute right-0 top-0 h-64 w-64 -translate-y-8 translate-x-16 opacity-[0.07]"
      viewBox="0 0 200 200"
      fill="none"
    >
      <path
        d="M100 10C100 10 30 60 30 120C30 160 60 190 100 190C140 190 170 160 170 120C170 60 100 10 100 10Z"
        fill="currentColor"
      />
      <path d="M100 10V190" stroke="currentColor" stroke-width="2" opacity="0.5" />
      <path
        d="M100 60C80 80 50 100 40 130"
        stroke="currentColor"
        stroke-width="1.5"
        opacity="0.3"
      />
      <path
        d="M100 60C120 80 150 100 160 130"
        stroke="currentColor"
        stroke-width="1.5"
        opacity="0.3"
      />
    </svg>

    <div class="relative mx-auto max-w-[1400px]">
      <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-3">
        <div class="col-span-1 md:col-span-1">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-full bg-white/10">
              <span class="material-symbols-outlined text-[24px] text-leaf">forest</span>
            </div>
            <span class="font-display text-xl font-semibold italic tracking-tight text-white"
              >Palu&#269;iny</span
            >
          </div>
          <p class="text-sm leading-relaxed text-cream/70">
            Nezapomenuteln&eacute; z&aacute;&#382;itky v p&#345;&iacute;rod&#283; pro d&#283;ti i
            dosp&#283;l&eacute;. Dobrodru&#382;stv&iacute;, hry a radost uprost&#345;ed les&#367;.
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
            <h3 class="mb-6 text-sm font-semibold uppercase tracking-wider text-cream/50">
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
                  class="text-sm text-cream/70 transition-colors hover:text-white"
                >
                  {{ link.name }}
                </NuxtLink>
              </li>
            </ul>
          </div>
        </template>
      </div>
      <div
        class="flex flex-col items-center justify-between gap-6 border-t border-white/10 pt-8 md:flex-row"
      >
        <p class="text-sm text-cream/50">
          &copy; {{ new Date().getFullYear() }} Palu&#269;iny. V&scaron;echna pr&aacute;va
          vyhrazena.
        </p>
        <div class="flex gap-6 text-sm text-cream/50">
          <a class="transition-colors hover:text-white" href="#">Ochrana soukrom&iacute;</a>
          <a class="transition-colors hover:text-white" href="#">Podm&iacute;nky</a>
          <a class="transition-colors hover:text-white" href="#">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>
