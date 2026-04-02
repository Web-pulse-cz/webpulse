<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  changelog: {
    type: Object,
    required: true,
  },
});

// Sladěno s naším novým design systémem (amber místo yellow, emerald místo green)
const priorityColorClass = computed(() => {
  switch (props.changelog.priority) {
    case 'high':
      return 'bg-red-500';
    case 'medium':
      return 'bg-amber-400';
    case 'low':
      return 'bg-emerald-500';
    default:
      return 'bg-slate-300';
  }
});

// Sladěno s naším novým design systémem (indigo místo blue, slate místo gray)
const badgeConfig = computed(() => {
  switch (props.changelog.type) {
    case 'feature':
      return { color: 'indigo', text: 'Nová funkce' };
    case 'bugfix':
      return { color: 'red', text: 'Oprava chyby' };
    case 'design':
      return { color: 'emerald', text: 'Design' };
    default:
      return { color: 'slate', text: 'Ostatní' };
  }
});
</script>

<template>
  <div
    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50"
  >
    <div
      :class="[
        'absolute inset-y-0 left-0 w-1.5 transition-colors duration-300',
        priorityColorClass,
      ]"
    ></div>

    <div class="p-6 sm:p-8">
      <div class="mb-5 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3
            class="text-xl font-bold tracking-tight text-slate-900 transition-colors duration-200 group-hover:text-indigo-600"
          >
            {{ changelog.title }}
          </h3>
          <p v-if="changelog.subtitle" class="mt-1.5 text-sm font-medium text-slate-500">
            {{ changelog.subtitle }}
          </p>
        </div>

        <div class="flex shrink-0 items-center gap-3">
          <span
            class="inline-flex items-center rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-200/50"
          >
            v{{ changelog.version }}
          </span>
          <PropsBadge :color="badgeConfig.color">
            {{ badgeConfig.text }}
          </PropsBadge>
        </div>
      </div>

      <div class="mb-6 h-px w-full bg-slate-100"></div>

      <div
        class="prose prose-sm sm:prose-base prose-slate prose-headings:text-slate-900 prose-a:font-medium prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:text-indigo-500 hover:prose-a:underline max-w-none text-slate-600"
        v-html="changelog.description"
      ></div>
    </div>
  </div>
</template>
