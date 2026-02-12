<script setup lang="ts">
import { computed, provide } from 'vue';
import { useLoadingStore } from '~/../stores/loading';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();

const { t, locale } = useI18n({
  useScope: 'global',
});
const loadingStore = useLoadingStore();
const loading = computed(() => loadingStore.isLoading);

provide('loading', loading);

watch(locale, (newLocale) => {
  settingStore.fetchSettings(newLocale);
});

onMounted(() => {
  settingStore.fetchSettings(locale.value);
  setInterval(() => {
    settingStore.fetchSettings(locale.value);
  }, 60000);
});
</script>

<template>
  <div>
    <LayoutNavbar />
    <main>
      <NuxtPage />
    </main>
    <LayoutContact />
    <LayoutFooter />
    <LayoutCookiesBar />

    <!-- Globální loader -->
    <div
      v-if="loading"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div class="h-12 w-12 animate-spin rounded-full border-4 border-white border-t-transparent" />
    </div>
  </div>
</template>
