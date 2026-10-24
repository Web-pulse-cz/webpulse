<script setup lang="ts">
const selected = defineModel<string>({ required: true });

defineProps<{
  days: { date: string; total: number }[];
  scoringType: string;
}>();

const { formatScore, dayLabel } = useLeague();
</script>

<template>
  <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
    <button
      v-for="day in days"
      :key="day.date"
      type="button"
      class="flex flex-col items-center rounded-2xl px-2 py-3 text-center ring-1 transition"
      :class="
        selected === day.date
          ? 'bg-indigo-600 text-white shadow-md ring-indigo-600'
          : 'bg-white text-slate-700 ring-slate-200 hover:bg-indigo-50 hover:ring-indigo-200'
      "
      @click="selected = day.date"
    >
      <span
        class="text-[11px] font-semibold uppercase tracking-wide"
        :class="selected === day.date ? 'text-indigo-100' : 'text-slate-400'"
      >
        {{ dayLabel(day.date).isToday ? 'Dnes' : dayLabel(day.date).weekday }}
      </span>
      <span class="text-2xl font-bold leading-tight">{{ dayLabel(day.date).day }}</span>
      <span
        class="text-[11px]"
        :class="selected === day.date ? 'text-indigo-100' : 'text-slate-400'"
      >
        {{ dayLabel(day.date).month }}
      </span>
      <span
        class="mt-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold tabular-nums"
        :class="
          selected === day.date
            ? 'bg-white/20 text-white'
            : day.total > 0
              ? 'bg-emerald-100 text-emerald-700'
              : day.total < 0
                ? 'bg-red-100 text-red-700'
                : 'bg-slate-100 text-slate-400'
        "
      >
        {{ day.total !== 0 ? formatScore(day.total, scoringType) : '—' }}
      </span>
    </button>
  </div>
</template>
