<script setup lang="ts">
import { ref } from 'vue';
import CookieIcon from '~/../public/static/img/icon/cookie.svg';

const { t } = useI18n();

const showBar = ref(true);
const showSettings = ref(false);
const showIcon = ref(false);

const cookies = ref({
  technical: true,
  marketing: false,
  analytics: false,
  advertisement: false,
});

function acceptCookie(
  string: 'technical' | 'marketing' | 'analytics' | 'advertisement' | 'showCookieBar',
) {
  useCookie(string, {
    maxAge: 60 * 60 * 24 * 365, // 1 year
    secure: true,
    sameSite: 'lax',
  }).value = string !== 'showCookieBar';
}

function acceptAllCookies() {
  showBar.value = false;
  showSettings.value = false;
  cookies.value.technical = true;
  cookies.value.marketing = true;
  cookies.value.analytics = true;
  cookies.value.advertisement = true;

  acceptCookie('showCookieBar');
  acceptCookie('technical');
  acceptCookie('marketing');
  acceptCookie('analytics');
  acceptCookie('advertisement');
}

function acceptSelectedCookies() {
  showBar.value = false;
  showSettings.value = false;
  acceptCookie('showCookieBar');
  acceptCookie('technical');

  if (cookies.value.marketing) {
    acceptCookie('marketing');
  } else {
    useCookie('marketing').value = false;
  }
  if (cookies.value.analytics) {
    acceptCookie('analytics');
  } else {
    useCookie('analytics').value = false;
  }
  if (cookies.value.advertisement) {
    acceptCookie('advertisement');
  } else {
    useCookie('advertisement').value = false;
  }
}

watch(
  () => showBar.value,
  (newValue) => {
    if (newValue) {
      showIcon.value = false;
    } else {
      showIcon.value = true;
    }
  },
);
onMounted(() => {
  const showCookieBar = useCookie('showCookieBar').value;

  if (!showCookieBar || typeof showCookieBar !== 'undefined') {
    showBar.value = false;
    showIcon.value = true;
  } else {
    showBar.value = true;
    showIcon.value = false;
  }

  cookies.value.technical = true;
  cookies.value.marketing = useCookie('marketing').value ?? false;
  cookies.value.analytics = useCookie('analytics').value ?? false;
  cookies.value.advertisement = useCookie('advertisement').value ?? false;
});
</script>

<template>
  <div>
    <div
      v-if="showBar"
      class="fixed bottom-4 left-4 right-4 z-[60] max-w-sm rounded-2xl border border-cream-dark bg-cream p-6 shadow-lg md:bottom-8 md:left-8 md:max-w-md"
    >
      <div class="mb-4 flex items-center gap-3">
        <CookieIcon class="size-8 fill-forest" />
        <h3 class="text-lg font-semibold text-earth">Cookies</h3>
      </div>

      <p class="mb-6 text-sm text-earth-light">
        {{ t('cookies.description') }}
      </p>

      <div class="flex flex-col gap-3 sm:flex-row">
        <button
          class="flex-1 rounded-full bg-forest px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-forest-dark"
          @click="acceptAllCookies"
        >
          {{ t('cookies.acceptAll') }}
        </button>

        <button
          class="flex-1 rounded-full bg-cream-dark px-4 py-2.5 text-sm font-medium text-earth transition-colors hover:bg-bark-light/20"
          @click="showSettings = true"
        >
          {{ t('cookies.settings') }}
        </button>
      </div>

      <LayoutCookiesDialog
        v-model:open="showSettings"
        :cookies="cookies"
        @accept-all="acceptAllCookies"
        @accept-selected="acceptSelectedCookies"
      />
    </div>

    <div
      v-if="showIcon"
      class="group fixed bottom-4 left-4 z-[50] flex size-12 cursor-pointer items-center justify-center rounded-full bg-forest shadow-lg transition-transform hover:scale-110 md:bottom-8 md:left-8"
      @click="showBar = true"
    >
      <CookieIcon class="size-6 fill-white transition-transform group-hover:-rotate-12" />
    </div>
  </div>
</template>
