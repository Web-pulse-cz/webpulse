<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import _ from 'lodash';
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  FlagIcon,
  BoltIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline';
import LiveHoleTab from '~/components/DiscGolf/LiveHoleTab.vue';
import GameSummary from '~/components/DiscGolf/GameSummary.vue';

const { $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const gameId = computed(() => route.params.id);

const loading = ref(false);
const error = ref(false);
const saving = ref(false);
const game = ref<any>(null);

const activeHoleIndex = ref(0);
const editMode = ref(route.query.edit === '1');
const retroMode = ref(false);
const retroTotals = ref<Record<number, number | null>>({});
const retroHandicaps = ref<Record<number, number>>({});

/** localScores keyed `${gamePlayerId}:${gameHoleId}` → throws | null */
const localScores = ref<Record<string, number | null>>({});

const pageTitle = ref('Živá hra');
const breadcrumbs = ref([
  { name: 'Hry', link: '/discgolf/hry', current: false },
  { name: pageTitle.value, link: route.fullPath, current: true },
]);

function headers() {
  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Site-Hash': selectedSiteHash.value,
  };
}

const isCompleted = computed(() => game.value?.status === 'completed');
// Read-only summary only when completed AND not explicitly editing.
const showReadOnly = computed(() => isCompleted.value && !editMode.value);
const holes = computed(() => game.value?.holes ?? []);
const gamePlayers = computed(() => game.value?.players ?? []);
const activeHole = computed(() => holes.value[activeHoleIndex.value] ?? null);

function scoreKey(gpId: number, ghId: number) {
  return `${gpId}:${ghId}`;
}

function liveTotal(gpId: number) {
  return holes.value.reduce((sum: number, h: any) => {
    const v = localScores.value[scoreKey(gpId, h.id)];
    return sum + (typeof v === 'number' ? v : 0);
  }, 0);
}
/** Sum of par over holes this player already has a score on (for a live +/- par). */
function playedPar(gpId: number) {
  return holes.value.reduce((sum: number, h: any) => {
    const v = localScores.value[scoreKey(gpId, h.id)];
    return sum + (typeof v === 'number' ? Number(h.par) || 0 : 0);
  }, 0);
}
function liveNet(gp: any) {
  // Handicap is a signed correction added to throws: net = throws + handicap.
  return liveTotal(gp.id) + (Number(gp.handicap) || 0);
}
function liveRelative(gp: any) {
  // +/- par against the par of holes played SO FAR, plus the handicap correction.
  return liveTotal(gp.id) - playedPar(gp.id) + (Number(gp.handicap) || 0);
}

const activeRows = computed(() => {
  if (!activeHole.value) return [];
  return gamePlayers.value.map((gp: any) => ({
    game_player_id: gp.id,
    player_name: gp.player_name,
    handicap: Number(gp.handicap) || 0,
    throws: localScores.value[scoreKey(gp.id, activeHole.value.id)] ?? null,
    liveTotal: liveTotal(gp.id),
    liveNet: liveNet(gp),
    liveRelative: liveRelative(gp),
  }));
});

async function loadGame() {
  loading.value = true;
  error.value = false;
  const client = useSanctumClient();
  await client(`/api/admin/discgolf/game/${gameId.value}`, {
    method: 'GET',
    headers: headers(),
  })
    .then((res: any) => {
      game.value = res;
      // hydrate local scores
      const map: Record<string, number | null> = {};
      (res.players ?? []).forEach((gp: any) => {
        retroHandicaps.value[gp.id] = Number(gp.handicap) || 0;
        retroTotals.value[gp.id] = gp.total_throws ?? null;
        (gp.scores ?? []).forEach((s: any) => {
          map[scoreKey(gp.id, s.game_hole_id)] = s.throws;
        });
      });
      localScores.value = map;
      pageTitle.value = `${res.course_name || res.custom_course_name || 'Hra'} — ${res.layout_name || res.custom_layout_name || ''}`;
      breadcrumbs.value[1].name = pageTitle.value;
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst hru.', severity: 'error' });
      router.push('/discgolf/hry');
    })
    .finally(() => {
      loading.value = false;
    });
}

/* ---------------------------------------------------- autosave (debounced) */
const pending = ref<
  Record<string, { game_player_id: number; game_hole_id: number; throws: number }>
>({});

const flushScores = _.debounce(async () => {
  const batch = Object.values(pending.value);
  if (!batch.length) return;
  pending.value = {};
  saving.value = true;
  const client = useSanctumClient();
  await client(`/api/admin/discgolf/game/${gameId.value}/scores`, {
    method: 'POST',
    body: JSON.stringify({ scores: batch }),
    headers: headers(),
  })
    .then((res: any) => {
      // keep server-computed aggregates in sync (net/total/relative) without
      // clobbering per-hole inputs the user may still be editing
      if (res?.players) {
        game.value.players = game.value.players.map((gp: any) => {
          const updated = res.players.find((p: any) => p.id === gp.id);
          return updated ? { ...gp, ...updated, scores: gp.scores } : gp;
        });
      }
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Autouložení skóre selhalo.', severity: 'error' });
    })
    .finally(() => {
      saving.value = false;
    });
}, 500);

function onScoreUpdate(payload: { game_player_id: number; throws: number | null }) {
  if (!activeHole.value) return;
  const key = scoreKey(payload.game_player_id, activeHole.value.id);
  localScores.value[key] = payload.throws;
  if (payload.throws === null) return; // don't persist empty
  pending.value[key] = {
    game_player_id: payload.game_player_id,
    game_hole_id: activeHole.value.id,
    throws: payload.throws,
  };
  flushScores();
}

/* ------------------------------------------------------------- navigation */
function goHole(index: number) {
  if (index < 0 || index >= holes.value.length) return;
  activeHoleIndex.value = index;
}

/* --------------------------------------------------------------- complete */
async function saveRetroTotals() {
  const payload = {
    players: gamePlayers.value.map((gp: any) => ({
      player_id: gp.player_id,
      handicap: Number(retroHandicaps.value[gp.id]) || 0,
      total_throws: Number(retroTotals.value[gp.id]) || 0,
    })),
  };
  const client = useSanctumClient();
  await client(`/api/admin/discgolf/game/${gameId.value}/players`, {
    method: 'POST',
    body: JSON.stringify(payload),
    headers: headers(),
  });
}

async function completeGame() {
  loading.value = true;
  try {
    if (retroMode.value) {
      const missing = gamePlayers.value.some((gp: any) => {
        const v = retroTotals.value[gp.id];
        return v === null || v === undefined || `${v}` === '';
      });
      if (missing) {
        $toast.show({
          summary: 'Upozornění',
          detail: 'Zadejte celkový počet hodů u všech hráčů.',
          severity: 'warn',
        });
        loading.value = false;
        return;
      }
      await saveRetroTotals();
    } else {
      // make sure any debounced scores are persisted first
      flushScores.flush();
      await new Promise((r) => setTimeout(r, 300));
    }
    const client = useSanctumClient();
    const res: any = await client(`/api/admin/discgolf/game/${gameId.value}/complete`, {
      method: 'POST',
      body: JSON.stringify({ note: game.value?.note ?? null }),
      headers: headers(),
    });
    game.value = { ...game.value, ...res };
    editMode.value = false;
    $toast.show({ summary: 'Hotovo', detail: 'Hra byla uložena.', severity: 'success' });
  } catch {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se dokončit hru.', severity: 'error' });
  } finally {
    loading.value = false;
  }
}

function relClass(gp: any) {
  const r = liveRelative(gp);
  if (r < 0) return 'text-emerald-600';
  if (r > 0) return 'text-red-500';
  return 'text-slate-600';
}
function fmtRel(r: number) {
  if (r === 0) return 'E';
  return r > 0 ? `+${r}` : `${r}`;
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => loadGame());
onMounted(() => loadGame());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <div v-if="loading && !game" class="py-16 text-center text-sm italic text-slate-400">
      Načítání hry…
    </div>

    <!-- ============================================ COMPLETED (read-only) -->
    <LayoutContainer v-else-if="game && showReadOnly">
      <div
        class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200"
      >
        <CheckCircleIcon class="size-5" />
        Hra je dokončená.
      </div>
      <GameSummary :players="gamePlayers" :par="game.par ?? 0" />
      <div class="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">
        <BaseButton variant="secondary" size="lg" @click="router.push('/discgolf/hry')">
          Zpět na přehled her
        </BaseButton>
        <BaseButton variant="primary" size="lg" @click="editMode = true">
          <PencilSquareIcon class="mr-2 size-4" /> Upravit skóre
        </BaseButton>
      </div>
    </LayoutContainer>

    <!-- ============================================ LIVE / EDIT -->
    <template v-else-if="game">
      <LayoutContainer
        v-if="isCompleted && editMode"
        class="!mt-0 flex items-center gap-2 border-amber-200 bg-amber-50 text-sm text-amber-700"
      >
        <PencilSquareIcon class="size-5" />
        Upravuješ dokončenou hru. Změny ulož tlačítkem „Uložit změny".
      </LayoutContainer>
      <!-- retro toggle + save indicator -->
      <LayoutContainer>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <BoltIcon class="size-5 text-amber-500" />
            <div>
              <div class="font-semibold text-slate-800">Rychlý zápis (jen celkové skóre)</div>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <span
              v-if="saving"
              class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400"
            >
              <ArrowPathIcon class="size-4 animate-spin" /> Ukládání…
            </span>
            <BaseFormSwitch v-model:enabled="retroMode" />
          </div>
        </div>
      </LayoutContainer>

      <!-- RETRO TOTALS -->
      <LayoutContainer v-if="retroMode" class="space-y-3">
        <LayoutTitle>Celkové skóre hráčů</LayoutTitle>
        <div
          v-for="gp in gamePlayers"
          :key="gp.id"
          class="flex flex-col gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="font-semibold text-slate-800">{{ gp.player_name }}</div>
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-500">Handicap</label>
              <input
                v-model.number="retroHandicaps[gp.id]"
                type="number"
                class="w-20 rounded-xl border-0 px-3 py-2 text-center text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              />
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-500">Celkem hodů</label>
              <input
                v-model.number="retroTotals[gp.id]"
                type="number"
                min="0"
                placeholder="—"
                class="w-24 rounded-xl border-0 px-3 py-2 text-center text-sm font-semibold shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              />
            </div>
          </div>
        </div>
      </LayoutContainer>

      <!-- HOLE-BY-HOLE -->
      <template v-else>
        <!-- hole tabs -->
        <LayoutContainer class="!mt-4">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="(hole, index) in holes"
              :key="hole.id"
              type="button"
              class="flex size-11 flex-col items-center justify-center rounded-xl text-sm font-bold transition-all"
              :class="
                index === activeHoleIndex
                  ? 'bg-indigo-600 text-white shadow'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              "
              @click="goHole(index)"
            >
              <span>J{{ hole.number }}</span>
              <span class="text-[9px] font-medium opacity-70">par {{ hole.par }}</span>
            </button>
          </div>
        </LayoutContainer>

        <LayoutContainer v-if="activeHole" class="space-y-6">
          <LiveHoleTab :hole="activeHole" :rows="activeRows" @update="onScoreUpdate" />

          <div class="flex items-center justify-between border-t border-slate-100 pt-4">
            <BaseButton
              variant="secondary"
              size="md"
              :disabled="activeHoleIndex === 0"
              @click="goHole(activeHoleIndex - 1)"
            >
              <ChevronLeftIcon class="mr-1 size-4" />
            </BaseButton>
            <span class="text-sm font-medium text-slate-500">
              Jamka {{ activeHoleIndex + 1 }} / {{ holes.length }}
            </span>
            <BaseButton
              variant="secondary"
              size="md"
              :disabled="activeHoleIndex === holes.length - 1"
              @click="goHole(activeHoleIndex + 1)"
              ><ChevronRightIcon class="ml-1 size-4" />
            </BaseButton>
          </div>
        </LayoutContainer>

        <!-- live standings -->
        <LayoutContainer>
          <LayoutTitle>Průběžné pořadí</LayoutTitle>
          <div class="overflow-x-auto rounded-2xl ring-1 ring-slate-200">
            <table class="w-full min-w-[500px] text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-4 py-3">Hráč</th>
                  <th class="px-4 py-3 text-center">Hody</th>
                  <th class="px-4 py-3 text-center">Handicap</th>
                  <th class="px-4 py-3 text-center">Čisté</th>
                  <th class="px-4 py-3 text-center">+/- par</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="gp in [...gamePlayers].sort((a, b) => liveRelative(a) - liveRelative(b))"
                  :key="gp.id"
                  class="hover:bg-slate-50"
                >
                  <td class="px-4 py-3 font-semibold text-slate-800">{{ gp.player_name }}</td>
                  <td class="px-4 py-3 text-center tabular-nums text-slate-700">
                    {{ liveTotal(gp.id) }}
                  </td>
                  <td class="px-4 py-3 text-center tabular-nums text-slate-500">
                    {{ gp.handicap }}
                  </td>
                  <td class="px-4 py-3 text-center font-semibold tabular-nums text-slate-900">
                    {{ liveNet(gp) }}
                  </td>
                  <td class="px-4 py-3 text-center font-bold tabular-nums" :class="relClass(gp)">
                    {{ fmtRel(liveRelative(gp)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </LayoutContainer>
      </template>

      <!-- complete -->
      <div class="flex justify-end">
        <BaseButton variant="success" size="lg" :disabled="loading" @click="completeGame">
          <FlagIcon class="mr-2 size-4" />
          {{ isCompleted && editMode ? 'Uložit změny' : 'Dokončit hru' }}
        </BaseButton>
      </div>
    </template>
  </div>
</template>
