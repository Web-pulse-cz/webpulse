<script setup lang="ts">
import { HomeIcon } from '@heroicons/vue/24/outline';
</script>

<template>
  <div class="mx-auto max-w-7xl p-4 sm:px-6 lg:px-8"><slot /></div>
  <NuxtLink
    to="/"
    class="fixed bottom-0 left-0 mb-4 ml-4 flex items-center gap-2 rounded-full lg:rounded-2xl border bg-[#f9fafb] p-3 text-sm transition hover:shadow"
    ><HomeIcon class="size-4" /><span class="hidden lg:block">Domů</span></NuxtLink
  >
</template>
