<script setup lang="ts">
import { ref, computed, onMounted, inject, watch, type Ref } from 'vue';
import { TrophyIcon } from '@heroicons/vue/24/outline';
import { useFormat } from '~/composables/useFormat';

const props = withDefaults(
  defineProps<{
    widgetKey: string;
    title: string;
    icon: unknown;
    color: string;
    link: string;
    endpoint?: string;
    permissionSlug?: string;
  }>(),
  {
    endpoint: '/api/admin/discgolf/game/matrix',
    permissionSlug: '',
  },
);

const { formatDate } = useFormat();

interface MatrixPlayer {
  player_id: number;
  player_name: string;
  handicap: number | null;
  total_throws: number | null;
  net_score: number | null;
  relative_to_par: number | null;
}

interface MatrixRow {
  id: number;
  played_at: string;
  course_name: string;
  layout_name: string;
  par: number;
  note: string | null;
  winner: {
    player_id: number;
    player_name: string;
    net_score: number;
    relative_to_par: number;
  } | null;
  players: MatrixPlayer[];
}

const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

const rows = ref<MatrixRow[]>([]);
const loading = ref(false);
const error = ref(false);

const total = computed(() => rows.value.length);

// Per-game placement: rank players by net_score ascending (lowest wins),
// ties share the same place (standard competition ranking).
const placements = computed(() => {
  const byRow = new Map<number, Map<number, number>>();
  rows.value.forEach((row) => {
    const scored = row.players
      .filter((p) => p.net_score !== null && p.net_score !== undefined)
      .slice()
      .sort((a, b) => (a.net_score as number) - (b.net_score as number));
    const placeMap = new Map<number, number>();
    let place = 0;
    let prevScore: number | null = null;
    scored.forEach((p, idx) => {
      if (prevScore === null || p.net_score !== prevScore) {
        place = idx + 1;
        prevScore = p.net_score;
      }
      placeMap.set(p.player_id, place);
    });
    byRow.set(row.id, placeMap);
  });
  return byRow;
});

function placeOf(rowId: number, playerId: number): number | null {
  return placements.value.get(rowId)?.get(playerId) ?? null;
}

// Background tint per placement (gold / silver / bronze).
const placeBg: Record<number, string> = {
  1: 'bg-amber-100',
  2: 'bg-slate-200/70',
  3: 'bg-orange-100',
};

function placeBgClass(rowId: number, playerId: number): string {
  const place = placeOf(rowId, playerId);
  return (place && placeBg[place]) || '';
}

// Union of all players present across all rows, preserving first-seen order.
const players = computed(() => {
  const map = new Map<number, string>();
  rows.value.forEach((row) => {
    row.players.forEach((player) => {
      if (!map.has(player.player_id)) {
        map.set(player.player_id, player.player_name);
      }
    });
  });
  return Array.from(map.entries()).map(([player_id, player_name]) => ({ player_id, player_name }));
});

function playerCell(row: MatrixRow, playerId: number) {
  const found = row.players.find((p) => p.player_id === playerId);
  if (!found) {
    return {
      present: false,
      handicap: null as number | null,
      total_throws: null as number | null,
      net_score: null as number | null,
      relative_to_par: null as number | null,
    };
  }
  return {
    present: true,
    handicap: found.handicap,
    total_throws: found.total_throws,
    net_score: found.net_score,
    relative_to_par: found.relative_to_par,
  };
}

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

async function loadRows() {
  if (!selectedSiteHash.value) return;
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();

  try {
    const response: any = await client(props.endpoint, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
    });
    rows.value = response?.data ?? [];
  } catch (_) {
    error.value = true;
    rows.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadRows);
watch(selectedSiteHash, () => loadRows());
</script>

<template>
  <DashboardWidgetBaseCard :title="title" :icon="icon" :color="color" :count="total" :link="link">
    <div class="-mx-2 overflow-x-auto" @click.stop>
      <table class="min-w-full border-separate border-spacing-0 text-sm">
        <thead>
          <tr class="bg-slate-50">
            <th
              rowspan="2"
              class="sticky left-0 z-10 rounded-tl-lg border-b border-slate-200 bg-slate-50 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Datum
            </th>
            <th
              rowspan="2"
              class="border-b border-slate-200 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Hřiště
            </th>
            <th
              rowspan="2"
              class="border-b border-slate-200 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Layout
            </th>
            <th
              rowspan="2"
              class="border-b border-slate-200 px-3 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Par
            </th>
            <th
              rowspan="2"
              class="border-b border-slate-200 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Vítěz
            </th>
            <th
              rowspan="2"
              class="border-b border-slate-200 px-3 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"
            >
              Poznámka
            </th>
            <th
              v-for="player in players"
              :key="player.player_id"
              colspan="3"
              class="border-b border-l border-slate-200 px-3 py-2 text-center text-xs font-bold text-slate-700"
            >
              {{ player.player_name }}
            </th>
          </tr>
          <tr class="bg-slate-50">
            <template v-for="player in players" :key="`sub-${player.player_id}`">
              <th
                class="border-b border-l border-slate-200 px-2.5 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400"
              >
                Par
              </th>
              <th
                class="border-b border-slate-200 px-2.5 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400"
              >
                Hendikep
              </th>
              <th
                class="border-b border-slate-200 px-2.5 py-2 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400"
              >
                Celkem
              </th>
            </template>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="row in rows"
            v-if="!loading && !error && rows.length"
            :key="row.id"
            class="transition-colors hover:bg-slate-50/50"
          >
            <td
              class="sticky left-0 z-10 whitespace-nowrap bg-white px-3 py-2.5 font-medium tabular-nums text-slate-900"
            >
              {{ formatDate(row.played_at) }}
            </td>
            <td class="whitespace-nowrap px-3 py-2.5 text-slate-700">{{ row.course_name }}</td>
            <td class="whitespace-nowrap px-3 py-2.5 text-slate-500">{{ row.layout_name }}</td>
            <td class="whitespace-nowrap px-3 py-2.5 text-center tabular-nums text-slate-700">
              {{ row.par }}
            </td>
            <td class="whitespace-nowrap px-3 py-2.5">
              <span
                v-if="row.winner"
                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700"
              >
                <TrophyIcon class="size-3.5" />
                {{ row.winner.player_name }}
              </span>
              <span v-else class="text-slate-300">-</span>
            </td>
            <td class="max-w-[180px] truncate px-3 py-2.5 text-xs text-slate-400">
              {{ row.note || '-' }}
            </td>

            <template v-for="player in players" :key="`cell-${row.id}-${player.player_id}`">
              <template v-if="playerCell(row, player.player_id).present">
                <td
                  class="whitespace-nowrap border-l border-slate-100 px-2.5 py-2.5 text-center font-bold tabular-nums"
                  :class="[
                    relativeClass(playerCell(row, player.player_id).relative_to_par),
                    placeBgClass(row.id, player.player_id),
                  ]"
                >
                  {{ playerCell(row, player.player_id).net_score
                  }}<span class="ml-1 text-[10px] font-semibold"
                    >({{ relativeLabel(playerCell(row, player.player_id).relative_to_par) }})</span
                  >
                </td>
                <td
                  class="whitespace-nowrap px-2.5 py-2.5 text-center tabular-nums text-slate-500"
                  :class="placeBgClass(row.id, player.player_id)"
                >
                  {{ playerCell(row, player.player_id).handicap ?? '-' }}
                </td>
                <td
                  class="whitespace-nowrap px-2.5 py-2.5 text-center tabular-nums text-slate-700"
                  :class="placeBgClass(row.id, player.player_id)"
                >
                  {{ playerCell(row, player.player_id).total_throws ?? '-' }}
                </td>
              </template>
              <template v-else>
                <td
                  class="border-l border-slate-100 bg-slate-50/40 px-2.5 py-2.5 text-center text-slate-300"
                >
                  –
                </td>
                <td class="bg-slate-50/40 px-2.5 py-2.5 text-center text-slate-300">–</td>
                <td class="bg-slate-50/40 px-2.5 py-2.5 text-center text-slate-300">–</td>
              </template>
            </template>
          </tr>

          <tr v-else-if="!loading && error">
            <td
              :colspan="6 + players.length * 3"
              class="whitespace-nowrap py-10 text-center text-sm text-slate-500"
            >
              Záznamy se nepodařilo načíst.
            </td>
          </tr>
          <tr v-else-if="!loading && !error && rows.length === 0">
            <td
              :colspan="6 + players.length * 3"
              class="whitespace-nowrap py-10 text-center text-sm text-slate-500"
            >
              Zatím nemáte žádné dokončené hry.
            </td>
          </tr>
          <tr v-else-if="loading">
            <td
              :colspan="6 + players.length * 3"
              class="whitespace-nowrap py-10 text-center text-sm text-slate-500"
            >
              <div class="flex items-center justify-center gap-x-2">
                <svg
                  class="h-5 w-5 animate-spin text-indigo-600"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span>Záznamy se načítají...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </DashboardWidgetBaseCard>
</template>
