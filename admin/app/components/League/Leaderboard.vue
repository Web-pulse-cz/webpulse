<script setup lang="ts">
defineProps<{
  rows: {
    user_id: number;
    name: string;
    total: number;
    week_total: number;
    active_days: number;
    rank: number;
  }[];
  scoringType: string;
  me?: number | null;
}>();

const { formatScore } = useLeague();

function rankClass(rank: number): string {
  if (rank === 1) return 'bg-amber-400 text-white';
  if (rank === 2) return 'bg-slate-400 text-white';
  if (rank === 3) return 'bg-orange-400 text-white';
  return 'bg-slate-100 text-slate-500';
}

function initials(name: string): string {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((p) => p[0])
    .join('')
    .toUpperCase();
}
</script>

<template>
  <div>
    <div v-if="!rows.length" class="py-6 text-center text-sm text-slate-400">
      Zatím nikdo nic nezaznamenal.
    </div>
    <ol v-else class="space-y-2">
      <li
        v-for="row in rows"
        :key="row.user_id"
        class="flex items-center gap-3 rounded-xl px-3 py-2.5 ring-1"
        :class="row.user_id === me ? 'bg-indigo-50 ring-indigo-200' : 'bg-white ring-slate-200'"
      >
        <span
          class="flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
          :class="rankClass(row.rank)"
        >
          {{ row.rank }}
        </span>
        <span
          class="flex size-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700"
        >
          {{ initials(row.name) }}
        </span>
        <div class="min-w-0 flex-1">
          <div class="truncate text-sm font-semibold text-slate-800">
            {{ row.name }}
            <span v-if="row.user_id === me" class="text-xs font-normal text-indigo-600">(já)</span>
          </div>
          <div class="text-xs text-slate-500">
            7 dní: {{ formatScore(row.week_total, scoringType) }} · aktivních dní:
            {{ row.active_days }}
          </div>
        </div>
        <span class="text-sm font-bold tabular-nums text-slate-900">
          {{ formatScore(row.total, scoringType) }}
        </span>
      </li>
    </ol>
  </div>
</template>
