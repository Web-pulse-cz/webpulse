<script setup lang="ts">
import { ref, onMounted, inject, watch, type Ref } from 'vue';
import { LockClosedIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  widgetKey: string;
  title: string;
  icon: unknown;
  endpoint: string;
  link: string;
  color: string;
}>();

const { formatScore, scoringLabel } = useLeague();

const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

interface LeagueRow {
  user_id: number;
  name: string;
  total: number;
  rank: number;
}

interface LeagueOverview {
  id: number;
  name: string;
  scoring_type: string;
  visibility: string;
  participants_count: number;
  top: LeagueRow[];
  my_total: number;
  my_rank: number | null;
}

const loading = ref(false);
const leagues = ref<LeagueOverview[]>([]);

function rankClass(rank: number): string {
  if (rank === 1) return 'bg-amber-400 text-white';
  if (rank === 2) return 'bg-slate-400 text-white';
  if (rank === 3) return 'bg-orange-400 text-white';
  return 'bg-slate-100 text-slate-500';
}

async function loadLeagues() {
  if (!selectedSiteHash.value) return;
  loading.value = true;
  const client = useSanctumClient();
  await client<LeagueOverview[]>(props.endpoint, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      leagues.value = r ?? [];
    })
    .catch(() => {
      leagues.value = [];
    })
    .finally(() => {
      loading.value = false;
    });
}

onMounted(loadLeagues);
watch(selectedSiteHash, () => loadLeagues());
</script>

<template>
  <DashboardWidgetBaseCard
    :title="title"
    :icon="icon"
    :color="color"
    :count="leagues.length"
    :link="link"
  >
    <div v-if="loading" class="py-10 text-center text-sm text-slate-400">Načítání...</div>
    <div v-else-if="!leagues.length" class="py-10 text-center text-sm text-slate-400">
      Zatím nejste v žádné lize.
    </div>
    <div v-else class="space-y-3">
      <NuxtLink
        v-for="league in leagues"
        :key="league.id"
        :to="`/ligy/${league.id}`"
        class="block rounded-xl p-3 ring-1 ring-slate-200 transition hover:shadow-md hover:ring-indigo-300"
      >
        <div class="mb-2 flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="truncate text-sm font-semibold text-slate-900">{{ league.name }}</div>
            <div class="flex items-center gap-1 text-[11px] text-slate-500">
              <component
                :is="league.visibility === 'invite' ? LockClosedIcon : GlobeAltIcon"
                class="size-3"
              />
              {{ league.visibility === 'invite' ? 'Na pozvání' : 'Pro všechny' }}
              · {{ scoringLabel(league.scoring_type) }}
            </div>
          </div>
          <div class="shrink-0 text-right">
            <div class="text-[10px] uppercase tracking-wider text-slate-400">Já</div>
            <div class="text-sm font-bold tabular-nums text-slate-900">
              {{ formatScore(league.my_total, league.scoring_type) }}
            </div>
            <div v-if="league.my_rank" class="text-[10px] text-slate-500">
              {{ league.my_rank }}. z {{ league.participants_count }}
            </div>
          </div>
        </div>

        <ol v-if="league.top?.some((r) => r.total > 0)" class="space-y-1">
          <li v-for="row in league.top" :key="row.user_id" class="flex items-center gap-2 text-xs">
            <span
              class="flex size-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
              :class="rankClass(row.rank)"
            >
              {{ row.rank }}
            </span>
            <span class="min-w-0 flex-1 truncate text-slate-700">{{ row.name }}</span>
            <span class="font-semibold tabular-nums text-slate-900">
              {{ formatScore(row.total, league.scoring_type) }}
            </span>
          </li>
        </ol>
        <p v-else class="text-xs text-slate-400">Zatím nikdo nic nezaznamenal.</p>
      </NuxtLink>
    </div>
  </DashboardWidgetBaseCard>
</template>
