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
        class="fixed bottom-4 left-4 right-4 z-[60] max-w-sm md:left-8 md:bottom-8 md:max-w-md bg-white border-4 border-deep-blue rounded-3xl p-6 shadow-[8px_8px_0px_0px_rgba(26,83,92,1)]"
    >
      <div class="flex items-center gap-3 mb-4">
        <CookieIcon class="size-8 fill-primary" />
        <h3 class="font-display font-black text-2xl text-deep-blue italic">Cookies!</h3>
      </div>

      <p class="text-deep-blue/80 font-medium text-sm mb-6">
        {{ t('cookies.description') }}
      </p>

      <div class="flex flex-col sm:flex-row gap-3">
        <button
            @click="acceptAllCookies"
            class="flex-1 bg-primary text-white font-black px-4 py-3 rounded-xl border-2 border-deep-blue shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all uppercase tracking-widest text-xs"
        >
          {{ t('cookies.acceptAll') }}
        </button>

        <button
            @click="showSettings = true"
            class="flex-1 bg-sunny text-deep-blue font-black px-4 py-3 rounded-xl border-2 border-deep-blue shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all uppercase tracking-widest text-xs"
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
        @click="showBar = true"
        class="fixed bottom-4 left-4 z-[50] md:bottom-8 md:left-8 bg-turquoise flex size-14 cursor-pointer items-center justify-center rounded-[30%_70%_70%_30%/30%_30%_70%_70%] border-4 border-deep-blue shadow-[4px_4px_0px_0px_rgba(26,83,92,1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all group"
    >
      <CookieIcon class="size-7 fill-deep-blue group-hover:-rotate-12 transition-transform" />
    </div>
  </div>
</template>
