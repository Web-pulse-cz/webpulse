<script setup lang="ts">
useHead({
  title: 'Odměna FED 2025',
});
</script>

<template>
  <div class="py-8">
    <BaseHeading type="h1"> 1000 KM1 loading...</BaseHeading>
    <BaseCountdown class="mb-6" />
    <div class="mt-6" aria-hidden="true">
      <div class="overflow-hidden rounded-full bg-white/10 ring-1 ring-sky-600/10">
        <div
          class="inline-flex h-7 items-center justify-center rounded-full bg-sky-500"
          style="width: 26.9%"
        >
          <span class="text-xs">0 z 1000</span>
        </div>
      </div>
      <div class="mt-6 hidden grid-cols-2 text-sm font-medium text-gray-400 ring-sky-600 sm:grid">
        <div class="text-sky-400">
          <p>Builder letní akce</p>
        </div>
        <div class="text-right font-bold text-sky-950">
          <p>Free enterprise day 1. 11. 2025</p>
        </div>
      </div>
    </div>
    <BaseCarousel class="mt-16" />
  </div>
</template>
