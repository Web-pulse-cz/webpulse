<script setup lang="ts">
import { TrophyIcon } from '@heroicons/vue/24/outline';

interface StandingRow {
  player_id: number;
  player_name: string;
  games_played: number;
  total_relative_to_par: number;
  average_relative_to_par: number | null;
}

defineProps({
  rows: {
    type: Array as () => StandingRow[],
    required: false,
    default: () => [],
  },
  loading: {
    type: Boolean,
    required: false,
    default: false,
  },
  error: {
    type: Boolean,
    required: false,
    default: false,
  },
});

function relativeLabel(relative: number | null): string {
  if (relative === null || relative === undefined) return '-';
  if (relative === 0) return 'E';
  return relative > 0 ? `+${relative}` : `${relative}`;
}

function relativeClass(relative: number | null): string {
  if (relative === null || relative === undefined) return 'text-slate-400';
  if (relative < 0) return 'text-emerald-600';
  if (relative > 0) return 'text-rose-600';
  return 'text-slate-600';
}

const placeBg: Record<number, string> = {
  1: 'bg-amber-100',
  2: 'bg-slate-200/70',
  3: 'bg-orange-100',
};

const placeIcon: Record<number, string> = {
  1: 'text-amber-500',
  2: 'text-slate-400',
  3: 'text-orange-500',
};
</script>

<template>
  <LayoutContainer>
    <div class="mb-6 flex items-center gap-3">
      <div class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
        <TrophyIcon class="size-5" />
      </div>
      <LayoutTitle class="!mb-0">Gellerův pohár</LayoutTitle>
    </div>

    <div v-if="loading" class="py-10 text-center text-sm text-slate-400">Načítám…</div>
    <div v-else-if="error" class="py-10 text-center text-sm text-rose-500">
      Nepodařilo se načíst pořadí.
    </div>
    <div v-else-if="!rows.length" class="py-10 text-center text-sm text-slate-400">
      Zatím žádné hry počítající se do poháru.
    </div>

    <div v-else class="overflow-hidden rounded-xl ring-1 ring-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Hráč</th>
            <th class="px-4 py-3 text-center">Odehráno her</th>
            <th class="px-4 py-3 text-center">Součet +/- par</th>
            <th class="px-4 py-3 text-center">Průměr na hru</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          <tr
            v-for="(row, index) in rows"
            :key="row.player_id"
            :class="placeBg[index + 1] || ''"
            class="hover:brightness-95"
          >
            <td class="px-4 py-3 font-bold tabular-nums" :class="placeIcon[index + 1] || 'text-slate-400'">
              {{ index + 1 }}
            </td>
            <td class="px-4 py-3 font-medium text-slate-900">{{ row.player_name }}</td>
            <td class="px-4 py-3 text-center tabular-nums text-slate-500">
              {{ row.games_played }}
            </td>
            <td
              class="px-4 py-3 text-center font-bold tabular-nums"
              :class="relativeClass(row.total_relative_to_par)"
            >
              {{ relativeLabel(row.total_relative_to_par) }}
            </td>
            <td
              class="px-4 py-3 text-center tabular-nums"
              :class="relativeClass(row.average_relative_to_par)"
            >
              {{ relativeLabel(row.average_relative_to_par) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </LayoutContainer>
</template>
