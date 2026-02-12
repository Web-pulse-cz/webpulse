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
    case 'high':
      return 'bg-red-500';
    case 'medium':
      return 'bg-yellow-500';
    case 'low':
      return 'bg-green-500';
    default:
      return 'bg-slate-300';
  }
});

const badgeConfig = computed(() => {
  switch (props.changelog.type) {
    case 'feature':
      return { color: 'blue', text: 'Nová funkce' };
    case 'bugfix':
      return { color: 'red', text: 'Oprava chyby' };
    case 'design':
      return { color: 'emerald', text: 'Design' };
    default:
      return { color: 'gray', text: 'Ostatní' };
  }
});
</script>

<template>
  <div
    class="transition-hover relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md"
  >
    <div :class="['absolute inset-y-0 left-0 w-1', priorityColorClass]"></div>

    <div class="p-5 sm:p-6">
      <div class="mb-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-semibold leading-tight text-slate-900">
            {{ changelog.title }}
          </h3>
          <p v-if="changelog.subtitle" class="mt-1 text-sm text-slate-500">
            {{ changelog.subtitle }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-400">
            v{{ changelog.version }}
          </span>
          <PropsBadge :color="badgeConfig.color">
            {{ badgeConfig.text }}
          </PropsBadge>
        </div>
      </div>

      <div class="mb-4 h-px w-full bg-slate-100"></div>

      <div
        class="prose prose-sm prose-slate custom-list-style max-w-none leading-relaxed text-slate-600"
        v-html="changelog.description"
      ></div>
    </div>
  </div>
</template>
