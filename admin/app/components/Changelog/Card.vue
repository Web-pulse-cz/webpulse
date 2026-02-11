<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  changelog: {
    type: Object,
    required: true,
  },
});

// Definujeme barvy pro levou linku podle priority
const priorityColorClass = computed(() => {
  switch (props.changelog.priority) {
    case 'high': return 'bg-red-500';
    case 'medium': return 'bg-yellow-500';
    case 'low': return 'bg-green-500';
    default: return 'bg-slate-300';
  }
});

const badgeConfig = computed(() => {
  switch (props.changelog.type) {
    case 'feature': return { color: 'blue', text: 'Nová funkce' };
    case 'bugfix': return { color: 'red', text: 'Oprava chyby' };
    case 'design': return { color: 'emerald', text: 'Design' };
    default: return { color: 'gray', text: 'Ostatní' };
  }
});
</script>

<template>
  <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-hover hover:shadow-md">
    <div :class="['absolute inset-y-0 left-0 w-1', priorityColorClass]"></div>

    <div class="p-5 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
          <h3 class="text-lg font-semibold text-slate-900 leading-tight">
            {{ changelog.title }}
          </h3>
          <p v-if="changelog.subtitle" class="mt-1 text-sm text-slate-500">
            {{ changelog.subtitle }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
            v{{ changelog.version }}
          </span>
          <PropsBadge :color="badgeConfig.color">
            {{ badgeConfig.text }}
          </PropsBadge>
        </div>
      </div>

      <div class="h-px bg-slate-100 w-full mb-4"></div>

      <div
          class="prose prose-sm prose-slate max-w-none text-slate-600 leading-relaxed custom-list-style"
          v-html="changelog.description"
      ></div>
    </div>
  </div>
</template>
