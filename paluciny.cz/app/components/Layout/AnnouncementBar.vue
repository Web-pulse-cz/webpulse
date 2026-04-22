<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { useSettingStore } from '~/../stores/settingStore';

const settingStore = useSettingStore();
const localePath = useLocalePath();

const STORAGE_KEY = 'paluciny.announcementBar.dismissed';

const dismissedHash = ref(null);

const bar = computed(() => settingStore.bar);

const isActive = computed(() => bar.value?.active && bar.value?.value?.text);

const contentHash = computed(() => {
  if (!bar.value?.value) return null;
  const { text, buttonText, buttonLink, backgroundColor, textColor } = bar.value.value;
  return btoa(
    encodeURIComponent(
      [text, buttonText, buttonLink, backgroundColor, textColor].filter(Boolean).join('|'),
    ),
  );
});

const isVisible = computed(
  () => isActive.value && contentHash.value && contentHash.value !== dismissedHash.value,
);

function dismiss() {
  if (!contentHash.value) return;
  dismissedHash.value = contentHash.value;
  if (import.meta.client) {
    localStorage.setItem(STORAGE_KEY, contentHash.value);
  }
}

onMounted(() => {
  if (import.meta.client) {
    dismissedHash.value = localStorage.getItem(STORAGE_KEY);
  }
});

watch(contentHash, (newHash, oldHash) => {
  if (newHash && oldHash && newHash !== oldHash && import.meta.client) {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored && stored !== newHash) {
      dismissedHash.value = null;
      localStorage.removeItem(STORAGE_KEY);
    }
  }
});

const barStyle = computed(() => ({
  backgroundColor: bar.value?.value?.backgroundColor || '#1e293b',
  color: bar.value?.value?.textColor || '#ffffff',
}));
</script>

<template>
  <transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="-translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="-translate-y-full opacity-0"
  >
    <div
      v-if="isVisible"
      class="relative z-40 w-full px-4 py-2.5 text-center text-sm font-medium shadow-sm sm:px-6 lg:px-20"
      :style="barStyle"
    >
      <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 pr-8">
        <span>{{ bar.value.text }}</span>
        <NuxtLink
          v-if="bar.value.buttonText && bar.value.buttonLink"
          :to="localePath(bar.value.buttonLink)"
          class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-white/30"
        >
          {{ bar.value.buttonText }}
        </NuxtLink>
      </div>
      <button
        type="button"
        class="absolute right-3 top-1/2 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-full transition hover:bg-white/20"
        aria-label="Zavřít oznámení"
        @click="dismiss"
      >
        <XMarkIcon class="size-4" />
      </button>
    </div>
  </transition>
</template>