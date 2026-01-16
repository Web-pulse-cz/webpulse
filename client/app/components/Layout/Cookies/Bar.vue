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
      class="fixed bottom-0 left-0 right-8 z-10 w-full max-w-full rounded border border-primary bg-beige p-6 text-primary shadow-lg md:bottom-4 md:left-4 md:max-w-md"
    >
      <BasePropsParagraph class="mb-4" size="base" color="black">
        {{ t('cookies.description') }}
      </BasePropsParagraph>
      <div class="flex gap-x-4">
        <BaseButton size="lg" @click="acceptAllCookies">
          {{ t('cookies.acceptAll') }}
        </BaseButton>
        <BaseButton size="lg" @click="showSettings = true">
          {{ t('cookies.settings') }}
        </BaseButton>
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
      class="fixed bottom-0 left-0 right-8 z-10 flex h-12 w-12 cursor-pointer items-center justify-center rounded border border-primary bg-beige p-2 md:bottom-4 md:left-4"
    >
      <CookieIcon class="h-6 w-6 cursor-pointer fill-primary" @click="showBar = true" />
    </div>
  </div>
</template>
