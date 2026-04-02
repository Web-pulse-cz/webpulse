<script setup lang="ts">
defineProps({
  tabs: {
    type: Array,
    required: true,
    default: [] as [],
  },
});
</script>

<template>
  <div class="relative border-b border-slate-200">
    <nav class="scrollbar-hide -mb-px flex space-x-8 overflow-x-auto pb-px" aria-label="Tabs">
      <NuxtLink
        v-for="(tab, index) in tabs"
        :key="index"
        :to="tab.link"
        :class="[
          tab.current
            ? 'border-indigo-600 text-indigo-600'
            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700',
          'group relative flex shrink-0 items-center border-b-2 px-1 py-4 text-sm font-semibold transition-all duration-200 focus:outline-none',
        ]"
      >
        <span>{{ tab.name }}</span>

        <span
          v-if="tab.current"
          class="absolute inset-x-0 bottom-0 h-0.5 rounded-t-full bg-indigo-600"
          aria-hidden="true"
        />
      </NuxtLink>
    </nav>
  </div>
</template>

<style>
/* Pomocná třída pro skrytí scrollbaru, aby nerušil čistý design */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
