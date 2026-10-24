<script setup lang="ts">
import { inject, ref, computed, watch } from 'vue';
import { CalendarDaysIcon, TrophyIcon, ClockIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const { formatScore, scoringLabel, dayLabel } = useLeague();
const { canEdit } = usePermissions();

const route = useRoute();
const router = useRouter();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const pageTitle = ref('Detail ligy');
const breadcrumbs = ref([
  { name: 'Ligy', link: '/ligy', current: false },
  { name: pageTitle.value, link: '/ligy/' + route.params.id, current: true },
]);

const loading = ref(false);
const saving = ref(false);

const league = ref<any>(null);
const days = ref<{ date: string; total: number; entries: Record<number, number> }[]>([]);
const leaderboard = ref<any[]>([]);
const recent = ref<any[]>([]);
const me = ref<number | null>(null);
const selectedDate = ref('');

const selectedDay = computed(() => days.value.find((d) => d.date === selectedDate.value));

const groups = computed(() => {
  if (!league.value) return [];
  const result = league.value.sections
    .filter((s: any) => s.activities?.length)
    .map((s: any) => ({ key: `s-${s.id}`, name: s.name, activities: s.activities }));
  if (league.value.activities?.length) {
    result.push({
      key: 'none',
      name: result.length ? 'Ostatní' : null,
      activities: league.value.activities,
    });
  }
  return result;
});

function countFor(activityId: number): number {
  return selectedDay.value?.entries?.[activityId] ?? 0;
}

// Rotating accent per section so neighbouring sections are easy to tell apart
const sectionAccents = [
  'bg-indigo-600',
  'bg-amber-500',
  'bg-sky-600',
  'bg-rose-600',
  'bg-teal-600',
  'bg-violet-600',
];

/** Done activities and day subtotal of a section for the selected day. */
function groupStats(group: { activities: any[] }) {
  let done = 0;
  let total = 0;
  for (const activity of group.activities) {
    const count = countFor(activity.id);
    if (count > 0) done++;
    if (activity.counts_to_league) total += activity.value * count;
  }
  return { done, total };
}

function applyBoard(response: any) {
  league.value = response.league;
  days.value = response.days;
  leaderboard.value = response.leaderboard;
  recent.value = response.recent;
  me.value = response.me;

  if (!days.value.some((d) => d.date === selectedDate.value)) {
    selectedDate.value = days.value[days.value.length - 1]?.date ?? '';
  }

  pageTitle.value = league.value.name;
  breadcrumbs.value.pop();
  breadcrumbs.value.push({
    name: pageTitle.value,
    link: '/ligy/' + route.params.id,
    current: true,
  });
}

async function loadBoard() {
  loading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/league/board/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(applyBoard)
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst ligu.',
        severity: 'error',
      });
      router.push('/ligy');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function record(activityId: number, count: number) {
  if (!selectedDay.value || count < 0) return;

  // Optimistic update — the response replaces the whole board state
  const day = selectedDay.value;
  const previous = day.entries[activityId] ?? 0;
  day.entries = { ...day.entries, [activityId]: count };

  saving.value = true;
  const client = useSanctumClient();

  await client(`/api/admin/league/board/${route.params.id}/record`, {
    method: 'POST',
    body: JSON.stringify({ league_activity_id: activityId, date: day.date, count }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(applyBoard)
    .catch((err: any) => {
      day.entries = { ...day.entries, [activityId]: previous };
      const data = err?.data || err?.response?._data || {};
      const message = Object.values(data).flat()[0];
      $toast.show({
        summary: 'Chyba',
        detail: typeof message === 'string' ? message : 'Nepodařilo se uložit záznam.',
        severity: 'error',
      });
    })
    .finally(() => {
      saving.value = false;
    });
}

function formatRecentDate(date: string): string {
  const label = dayLabel(date);
  return label.isToday ? 'dnes' : `${label.weekday} ${label.day}. ${label.month}`;
}

watch(selectedSiteHash, () => loadBoard());

useHead({ title: pageTitle });
onMounted(() => loadBoard());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="leagues" />

    <div v-if="loading && !league" class="py-12 text-center text-sm text-slate-500">
      Načítám ligu…
    </div>

    <template v-else-if="league">
      <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
          {{ scoringLabel(league.scoring_type) }}
        </span>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
          {{ league.visibility === 'invite' ? 'Na pozvání' : 'Pro všechny' }}
        </span>
        <NuxtLink
          v-if="canEdit('leagues')"
          :to="`/ligy/nastaveni/${league.id}`"
          class="ml-auto flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-indigo-600"
        >
          <Cog6ToothIcon class="size-4" />
          Nastavení ligy
        </NuxtLink>
      </div>

      <LayoutContainer v-if="league.description">
        <details class="group">
          <summary
            class="cursor-pointer select-none text-sm font-semibold text-slate-800 hover:text-indigo-600"
          >
            Popis a pravidla ligy
          </summary>
          <div class="league-rules mt-4 text-sm text-slate-600" v-html="league.description" />
        </details>
      </LayoutContainer>

      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-6 lg:col-span-7 xl:col-span-8">
          <LayoutContainer>
            <div class="mb-5 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <CalendarDaysIcon class="size-5" />
              </div>
              <div>
                <LayoutTitle class="!mb-0">Moje aktivity</LayoutTitle>
                <p class="text-xs text-slate-500">Zaznamenat lze nejvýše týden zpět.</p>
              </div>
            </div>

            <LeagueDayCards
              v-model="selectedDate"
              :days="days"
              :scoring-type="league.scoring_type"
              class="mb-6"
            />

            <div v-if="!groups.length" class="py-6 text-center text-sm text-slate-400">
              Liga zatím nemá žádné aktivity.
            </div>

            <div class="space-y-4">
              <section
                v-for="(group, index) in groups"
                :key="group.key"
                :class="
                  group.name ? 'overflow-hidden rounded-xl bg-slate-50 ring-1 ring-slate-200' : ''
                "
              >
                <header
                  v-if="group.name"
                  class="flex items-center justify-between gap-3 px-4 py-3 text-white"
                  :class="sectionAccents[index % sectionAccents.length]"
                >
                  <h3 class="truncate text-base font-bold">{{ group.name }}</h3>
                  <div
                    class="flex shrink-0 items-center gap-1.5 text-xs font-semibold tabular-nums"
                  >
                    <span
                      class="rounded-full bg-white/20 px-2 py-0.5"
                      title="Zaznamenáno / aktivit"
                    >
                      {{ groupStats(group).done }}/{{ group.activities.length }}
                    </span>
                    <span
                      v-if="groupStats(group).total !== 0"
                      class="rounded-full bg-white px-2 py-0.5"
                      :class="groupStats(group).total > 0 ? 'text-emerald-700' : 'text-red-700'"
                    >
                      {{ groupStats(group).total > 0 ? '+' : ''
                      }}{{ formatScore(groupStats(group).total, league.scoring_type) }}
                    </span>
                  </div>
                </header>
                <div class="grid grid-cols-1 gap-2 xl:grid-cols-2" :class="{ 'p-3': group.name }">
                  <LeagueActivityTile
                    v-for="activity in group.activities"
                    :key="activity.id"
                    :activity="activity"
                    :count="countFor(activity.id)"
                    :scoring-type="league.scoring_type"
                    :disabled="saving"
                    @change="(count) => record(activity.id, count)"
                  />
                </div>
              </section>
            </div>
          </LayoutContainer>
        </div>

        <aside
          class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-5 lg:-m-1 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:p-1 xl:col-span-4"
        >
          <LayoutContainer>
            <div class="mb-5 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-500"
              >
                <TrophyIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Žebříček</LayoutTitle>
            </div>
            <LeagueLeaderboard :rows="leaderboard" :scoring-type="league.scoring_type" :me="me" />
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-4 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500"
              >
                <ClockIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Poslední záznamy</LayoutTitle>
            </div>
            <div v-if="!recent.length" class="py-4 text-center text-sm text-slate-400">
              Zatím žádné záznamy.
            </div>
            <ul v-else class="divide-y divide-slate-100">
              <li v-for="entry in recent" :key="entry.id" class="flex items-center gap-3 py-2">
                <div class="min-w-0 flex-1">
                  <div class="truncate text-sm text-slate-800">
                    <strong>{{ entry.user_name }}</strong> · {{ entry.activity_name }}
                    <span v-if="entry.count > 1" class="text-slate-500">{{ entry.count }}×</span>
                  </div>
                  <div class="text-xs text-slate-400">{{ formatRecentDate(entry.date) }}</div>
                </div>
                <span
                  class="text-xs font-semibold tabular-nums"
                  :class="entry.counts_to_league ? 'text-emerald-600' : 'text-slate-400'"
                >
                  +{{ formatScore(entry.value, league.scoring_type) }}
                </span>
              </li>
            </ul>
          </LayoutContainer>
        </aside>
      </div>
    </template>
  </div>
</template>

<style scoped>
.league-rules :deep(h1),
.league-rules :deep(h2),
.league-rules :deep(h3) {
  margin: 1rem 0 0.5rem;
  font-weight: 600;
  color: #0f172a;
}
.league-rules :deep(h1) {
  font-size: 1.25rem;
}
.league-rules :deep(h2) {
  font-size: 1.125rem;
}
.league-rules :deep(p) {
  margin-bottom: 0.5rem;
}
.league-rules :deep(ul),
.league-rules :deep(ol) {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}
.league-rules :deep(ul) {
  list-style: disc;
}
.league-rules :deep(ol) {
  list-style: decimal;
}
.league-rules :deep(a) {
  color: #4f46e5;
  text-decoration: underline;
}
.league-rules :deep(blockquote) {
  border-left: 3px solid #e2e8f0;
  padding-left: 0.75rem;
  color: #64748b;
}
</style>
