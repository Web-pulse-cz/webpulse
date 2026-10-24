<script setup lang="ts">
import { computed } from 'vue';
import { CheckIcon, MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  activity: {
    id: number;
    name: string;
    value: number;
    max_per_day: number | null;
    counts_to_league: boolean;
  };
  count: number;
  scoringType: string;
  disabled?: boolean;
}>();

const emit = defineEmits<{ change: [count: number] }>();

const { formatScore } = useLeague();

const isSingle = computed(() => props.activity.max_per_day === 1);

const valueLabel = computed(() => {
  const value = Number(props.activity.value) || 0;
  return (value > 0 ? '+' : '') + formatScore(value, props.scoringType);
});

const valueClass = computed(() => {
  if (!props.activity.counts_to_league) return 'bg-slate-100 text-slate-400 ring-slate-200';
  if (props.activity.value < 0) return 'bg-red-50 text-red-700 ring-red-200';
  return 'bg-indigo-50 text-indigo-700 ring-indigo-200';
});

const canIncrease = computed(
  () => props.activity.max_per_day === null || props.count < props.activity.max_per_day,
);
</script>

<template>
  <div
    class="flex items-center justify-between gap-3 rounded-xl p-3 ring-1 transition"
    :class="count > 0 ? 'bg-emerald-50 ring-emerald-200' : 'bg-white ring-slate-200'"
  >
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <span class="truncate text-sm font-semibold text-slate-800">{{ activity.name }}</span>
        <span
          class="shrink-0 rounded-md px-1.5 py-0.5 text-xs font-bold tabular-nums ring-1 ring-inset"
          :class="valueClass"
        >
          {{ valueLabel }}
        </span>
      </div>
      <div v-if="activity.description" class="line-clamp-2 text-xs text-slate-500">
        {{ activity.description }}
      </div>
      <div
        v-if="!isSingle || !activity.counts_to_league"
        class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500"
      >
        <span v-if="!isSingle" class="text-slate-400">
          {{ activity.max_per_day ? `max ${activity.max_per_day}× denně` : 'vícekrát denně' }}
        </span>
        <span
          v-if="!activity.counts_to_league"
          class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500"
        >
          nezapočítává se
        </span>
      </div>
    </div>

    <button
      v-if="isSingle"
      type="button"
      :disabled="disabled"
      class="flex size-9 shrink-0 items-center justify-center rounded-full ring-1 transition disabled:opacity-50"
      :class="
        count > 0
          ? 'bg-emerald-500 text-white ring-emerald-500 hover:bg-emerald-600'
          : 'bg-white text-slate-300 ring-slate-300 hover:text-emerald-500 hover:ring-emerald-400'
      "
      :title="count > 0 ? 'Zrušit záznam' : 'Zaznamenat'"
      @click="emit('change', count > 0 ? 0 : 1)"
    >
      <CheckIcon class="size-5" />
    </button>

    <div v-else class="flex shrink-0 items-center gap-1.5">
      <button
        type="button"
        :disabled="disabled || count === 0"
        class="flex size-8 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-300 transition hover:text-red-500 disabled:opacity-40"
        title="Odebrat"
        @click="emit('change', count - 1)"
      >
        <MinusIcon class="size-4" />
      </button>
      <span class="w-7 text-center text-sm font-bold tabular-nums text-slate-800">{{ count }}</span>
      <button
        type="button"
        :disabled="disabled || !canIncrease"
        class="flex size-8 items-center justify-center rounded-full bg-indigo-600 text-white transition hover:bg-indigo-500 disabled:opacity-40"
        title="Přidat"
        @click="emit('change', count + 1)"
      >
        <PlusIcon class="size-4" />
      </button>
    </div>
  </div>
</template>
