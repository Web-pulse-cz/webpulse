<script setup>
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
</script>

<template>
  <footer class="border-t-8 border-primary bg-deep-blue px-6 pb-12 pt-24 text-white lg:px-20">
    <div class="mx-auto max-w-[1400px]">
      <div class="mb-20 grid grid-cols-1 gap-16 md:grid-cols-4">
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
        <div>
          <h3
            class="mb-8 font-display text-2xl font-black uppercase italic tracking-widest text-primary"
          >
            Discover
          </h3>
          <ul class="space-y-4 text-lg font-bold">
            <li><a class="transition-colors hover:text-turquoise" href="#">Latest Posts</a></li>
            <li><a class="transition-colors hover:text-turquoise" href="#">Featured Authors</a></li>
            <li><a class="transition-colors hover:text-turquoise" href="#">Categories</a></li>
            <li><a class="transition-colors hover:text-turquoise" href="#">Search</a></li>
          </ul>
        </div>
        <div>
          <h3
            class="mb-8 font-display text-2xl font-black uppercase italic tracking-widest text-turquoise"
          >
            Company
          </h3>
          <ul class="space-y-4 text-lg font-bold">
            <li><a class="transition-colors hover:text-primary" href="#">About Us</a></li>
            <li><a class="transition-colors hover:text-primary" href="#">Careers</a></li>
            <li><a class="transition-colors hover:text-primary" href="#">Contact</a></li>
            <li><a class="transition-colors hover:text-primary" href="#">Privacy Policy</a></li>
          </ul>
        </div>
        <div>
          <h3
            class="mb-8 font-display text-2xl font-black uppercase italic tracking-widest text-sunny"
          >
            Connect
          </h3>
          <div class="mb-8 flex gap-6">
            <a
              class="flex size-12 items-center justify-center rounded-full border-2 border-white/20 transition-all hover:border-primary hover:bg-primary"
              href="#"
            >
              <span class="sr-only">Twitter</span>
              <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"
                ></path>
              </svg>
            </a>
            <a
              class="flex size-12 items-center justify-center rounded-full border-2 border-white/20 transition-all hover:border-turquoise hover:bg-turquoise"
              href="#"
            >
              <span class="sr-only">GitHub</span>
              <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                  clip-rule="evenodd"
                  d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                  fill-rule="evenodd"
                ></path>
              </svg>
            </a>
          </div>
          <div class="rounded-2xl border-2 border-white/10 bg-white/10 p-6">
            <p class="mb-2 text-sm font-black italic text-sunny">Stay in the loop</p>
            <div class="flex gap-2">
              <input
                class="w-full rounded-lg border-none bg-white/10 text-white placeholder:text-white/40 focus:ring-2 focus:ring-turquoise"
                placeholder="Email"
                type="text"
              />
              <button class="rounded-lg bg-turquoise px-4 font-black text-deep-blue">Go</button>
            </div>
          </div>
        </div>
      </div>
      <div
        class="flex flex-col items-center justify-between gap-8 border-t-2 border-white/10 pt-12 md:flex-row"
      >
        <p class="text-sm font-bold italic text-white/50">
          © 2024 Blog App. Stay Wild. Keep Reading.
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
