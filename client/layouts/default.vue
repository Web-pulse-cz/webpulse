<script setup lang="ts">
import { useLoadingStore } from '~/stores/loading';
import { computed, provide } from 'vue';

const loadingStore = useLoadingStore();
const loading = computed(() => loadingStore.isLoading);

provide('loading', loading);
</script>

<template>
  <div>
    <LayoutNavbar />
    <main>
      <NuxtPage />
    </main>
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
