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
    class="group relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/40"
  >
    <div
      :class="[
        'absolute inset-y-0 left-0 w-1.5 transition-colors duration-300',
        priorityColorClass,
      ]"
    />

    <div class="p-6">
      <div class="grid grid-cols-12 items-start gap-4">
        <div
          class="col-span-12 flex shrink-0 items-center gap-2.5 lg:col-span-full lg:mb-1 xl:col-span-4 xl:mb-0 xl:flex-row xl:justify-start"
        >
          <span
            class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-white ring-1 ring-inset ring-slate-900"
          >
            v{{ changelog.version }}
          </span>
          <PropsBadge :color="badgeConfig.color" class="text-[10px] uppercase">
            {{ badgeConfig.text }}
          </PropsBadge>
        </div>

        <div class="col-span-12 xl:col-span-8">
          <h3
            class="text-lg font-bold tracking-tight text-slate-900 transition-colors group-hover:text-indigo-600"
          >
            {{ changelog.title }}
          </h3>
          <p v-if="changelog.subtitle" class="mt-1 text-sm font-medium text-slate-500">
            {{ changelog.subtitle }}
          </p>

          <div v-if="changelog.description" class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div
              class="prose prose-sm prose-slate prose-headings:text-slate-900 prose-a:font-medium prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:text-indigo-500 hover:prose-a:underline max-w-none text-slate-600"
              v-html="changelog.description"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
