<script setup lang="ts">
import { inject, ref } from 'vue';
import { TrophyIcon, FlagIcon, UserGroupIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import { useFormat } from '~/composables/useFormat';

const { $toast } = useNuxtApp();
const { formatDate } = useFormat();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref('Detail hry');

const breadcrumbs = ref([
  { name: 'Hry', link: '/discgolf/hry', current: false },
  { name: pageTitle.value, link: '/discgolf/hry/' + route.params.id, current: true },
]);

const game = ref<any>(null);

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

async function updateCountToCup() {
  const client = useSanctumClient();

  await client('/api/admin/discgolf/game/' + route.params.id, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
    body: { count_to_cup: game.value.count_to_cup },
  })
    .then(() => {
      $toast.show({
        summary: 'Uloženo',
        detail: 'Nastavení bylo uloženo.',
        severity: 'success',
      });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit nastavení.',
        severity: 'error',
      });
    });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/discgolf/game/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      // Games that are not completed are continued in the live game page.
      if (response.status !== 'completed') {
        router.replace('/discgolf/hry/' + route.params.id + '/hra');
        return;
      }
      game.value = response;
      pageTitle.value = `${response.course_name || 'Hra'} — ${formatDate(response.played_at)}`;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/discgolf/hry/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst hru.',
        severity: 'error',
      });
      router.push('/discgolf/hry');
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => loadItem());
onMounted(() => loadItem());
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <div v-if="game" class="space-y-8">
      <div class="flex justify-end">
        <NuxtLink
          :to="`/discgolf/hry/${route.params.id}/hra?edit=1`"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-indigo-700"
        >
          <PencilSquareIcon class="size-4" />
          Upravit skóre
        </NuxtLink>
      </div>

      <LayoutContainer>
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
          <div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Datum</div>
            <div class="mt-1 text-sm font-semibold text-slate-900">
              {{ formatDate(game.played_at) }}
            </div>
          </div>
          <div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Hřiště</div>
            <div class="mt-1 text-sm font-semibold text-slate-900">{{ game.course_name }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Layout</div>
            <div class="mt-1 text-sm font-semibold text-slate-900">{{ game.layout_name }}</div>
          </div>
          <div>
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Par</div>
            <div class="mt-1 text-sm font-semibold text-slate-900">{{ game.par }}</div>
          </div>
        </div>
        <div
          v-if="game.winner"
          class="mt-6 flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 ring-1 ring-amber-100"
        >
          <TrophyIcon class="size-5" />
          Vítěz: {{ game.winner.player_name }}
          <span class="font-semibold">({{ relativeLabel(game.winner.relative_to_par) }})</span>
        </div>
        <p v-if="game.note" class="mt-4 text-sm text-slate-500">{{ game.note }}</p>
      </LayoutContainer>

      <LayoutContainer>
        <div
          class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 ring-1 ring-slate-200"
        >
          <div>
            <div class="font-semibold text-slate-800">Počítat do poháru</div>
            <div class="text-xs text-slate-500">
              Pokud je zapnuto, hra se zobrazí v tabulce Záznamy.
            </div>
          </div>
          <BaseFormSwitch v-model:enabled="game.count_to_cup" @update:enabled="updateCountToCup" />
        </div>
      </LayoutContainer>

      <LayoutContainer>
        <div class="mb-6 flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
          >
            <UserGroupIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Výsledky hráčů</LayoutTitle>
        </div>
        <div v-if="game.players && game.players.length" class="mb-8 pt-2">
          <DiscGolfPodium :players="game.players" />
        </div>
        <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Hráč</th>
                <th class="px-4 py-3 text-center">Hendikep</th>
                <th class="px-4 py-3 text-center">Celkem hodů</th>
                <th class="px-4 py-3 text-center">Čistý (net)</th>
                <th class="px-4 py-3 text-center">+/- Par</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-for="player in game.players" :key="player.id" class="hover:bg-slate-50/50">
                <td class="px-4 py-3 font-medium text-slate-900">{{ player.player_name }}</td>
                <td class="px-4 py-3 text-center tabular-nums text-slate-500">
                  {{ player.handicap ?? '-' }}
                </td>
                <td class="px-4 py-3 text-center tabular-nums text-slate-700">
                  {{ player.total_throws ?? '-' }}
                </td>
                <td class="px-4 py-3 text-center font-semibold tabular-nums text-slate-900">
                  {{ player.net_score ?? '-' }}
                </td>
                <td
                  class="px-4 py-3 text-center font-bold tabular-nums"
                  :class="relativeClass(player.relative_to_par)"
                >
                  {{ relativeLabel(player.relative_to_par) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </LayoutContainer>

      <LayoutContainer v-if="game.holes && game.holes.length">
        <div class="mb-6 flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
          >
            <FlagIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Jamky</LayoutTitle>
        </div>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="hole in game.holes"
            :key="hole.id"
            class="flex min-w-[70px] flex-col items-center rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200"
          >
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
              #{{ hole.number }}
            </span>
            <span class="text-sm font-extrabold text-slate-900">Par {{ hole.par }}</span>
            <span v-if="hole.meters" class="text-[11px] text-slate-400">{{ hole.meters }} m</span>
          </div>
        </div>
      </LayoutContainer>
    </div>
  </div>
</template>
