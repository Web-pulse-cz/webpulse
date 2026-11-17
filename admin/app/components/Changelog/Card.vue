<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  changelog: {
    type: Object,
    required: true,
  },
});

const priorityConfig = computed(() => {
  switch (props.changelog.priority) {
    case 'high':
      return { dot: 'bg-rose-500', text: 'Vysoká priorita' };
    case 'medium':
      return { dot: 'bg-amber-400', text: 'Normální priorita' };
    case 'low':
      return { dot: 'bg-emerald-500', text: 'Nízká priorita' };
    default:
      return { dot: 'bg-slate-300', text: '' };
  }
});

const typeConfig = computed(() => {
  switch (props.changelog.type) {
    case 'feature':
      return { classes: 'bg-indigo-50 text-indigo-600', text: 'Nová funkce' };
    case 'bugfix':
      return { classes: 'bg-rose-50 text-rose-600', text: 'Oprava chyby' };
    case 'design':
      return { classes: 'bg-emerald-50 text-emerald-600', text: 'Design' };
    default:
      return { classes: 'bg-slate-100 text-slate-600', text: 'Ostatní' };
  }
});
</script>

<template>
  <div
    class="rounded-xl p-3 ring-1 ring-slate-200 transition hover:shadow-md hover:ring-indigo-300"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <div class="flex items-center gap-2">
          <span
            class="size-2 shrink-0 rounded-full"
            :class="priorityConfig.dot"
            :title="priorityConfig.text"
          />
          <div class="truncate text-sm font-semibold text-slate-900">{{ changelog.title }}</div>
        </div>
        <div v-if="changelog.subtitle" class="mt-0.5 text-[11px] text-slate-500">
          {{ changelog.subtitle }}
        </div>
      </div>
      <div class="flex shrink-0 items-center gap-1.5">
        <span
          class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
          :class="typeConfig.classes"
        >
          {{ typeConfig.text }}
        </span>
        <span
          class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold tabular-nums text-slate-600"
        >
          v{{ changelog.version }}
        </span>
      </div>
    </div>

    <div
      v-if="changelog.description"
      v-safe-html="changelog.description"
      class="prose prose-sm prose-slate prose-p:my-1 prose-a:font-medium prose-a:text-indigo-600 prose-ul:my-1 prose-li:my-0 mt-2 max-w-none border-t border-slate-100 pt-2 text-xs text-slate-600"
    />
  </div>
</template>
