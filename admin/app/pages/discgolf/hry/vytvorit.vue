<script setup lang="ts">
import { inject, ref, computed } from 'vue';
import {
  MapPinIcon,
  PencilSquareIcon,
  PlusIcon,
  TrashIcon,
  UserGroupIcon,
  FlagIcon,
  BoltIcon,
  ArrowLeftIcon,
  ScaleIcon,
} from '@heroicons/vue/24/outline';
import GameSummary from '~/components/DiscGolf/GameSummary.vue';

const { $toast } = useNuxtApp();
const router = useRouter();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const pageTitle = ref('Vytvořit hru');
const breadcrumbs = ref([
  { name: 'Hry', link: '/discgolf/hry', current: false },
  { name: pageTitle.value, link: '/discgolf/hry/vytvorit', current: true },
]);

const step = ref(1);
const loading = ref(false);

function headers() {
  return {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Site-Hash': selectedSiteHash.value,
  };
}

/* ------------------------------------------------------------------ Step 1 */
const courseMode = ref<'existing' | 'custom'>('existing');
const courses = ref<any[]>([]);
const selectedCourseId = ref<number | null>(null);
const selectedLayoutId = ref<number | null>(null);

const customCourseName = ref('');
const customLayoutName = ref('');
const customHoles = ref<{ par: number; meters: number | null }[]>([
  { par: 3, meters: null },
  { par: 3, meters: null },
  { par: 3, meters: null },
]);

function nowLocal() {
  const d = new Date();
  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
  return d.toISOString().slice(0, 16);
}
const playedAt = ref(nowLocal());
const note = ref('');

const selectedCourse = computed(() => courses.value.find((c) => c.id === selectedCourseId.value));
const availableLayouts = computed(() => selectedCourse.value?.layouts ?? []);

const customPar = computed(() =>
  customHoles.value.reduce((sum, h) => sum + (Number(h.par) || 0), 0),
);

async function loadCourses() {
  const client = useSanctumClient();
  await client('/api/admin/discgolf/course', {
    method: 'GET',
    query: { paginate: 100, orderBy: 'position', orderWay: 'asc' },
    headers: headers(),
  })
    .then((res: any) => {
      courses.value = res.data ?? res ?? [];
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst hřiště.', severity: 'error' });
    });
}

function addHole() {
  customHoles.value.push({ par: 3, meters: null });
}
function removeHole(index: number) {
  if (customHoles.value.length <= 1) return;
  customHoles.value.splice(index, 1);
}

const draftGame = ref<any>(null);

async function createGame() {
  // validation
  if (courseMode.value === 'existing') {
    if (!selectedCourseId.value || !selectedLayoutId.value) {
      $toast.show({
        summary: 'Upozornění',
        detail: 'Vyberte prosím hřiště a layout.',
        severity: 'warn',
      });
      return;
    }
  } else {
    if (!customCourseName.value.trim() || !customLayoutName.value.trim()) {
      $toast.show({
        summary: 'Upozornění',
        detail: 'Vyplňte název hřiště i layoutu.',
        severity: 'warn',
      });
      return;
    }
    if (!customHoles.value.length) {
      $toast.show({
        summary: 'Upozornění',
        detail: 'Přidejte alespoň jednu jamku.',
        severity: 'warn',
      });
      return;
    }
  }

  const body: any = {
    played_at: playedAt.value ? playedAt.value.replace('T', ' ') + ':00' : null,
    note: note.value || null,
    sites: [],
  };
  if (courseMode.value === 'existing') {
    body.course_id = selectedCourseId.value;
    body.course_layout_id = selectedLayoutId.value;
  } else {
    body.custom_course_name = customCourseName.value.trim();
    body.custom_layout_name = customLayoutName.value.trim();
    body.holes = customHoles.value.map((h, i) => ({
      number: i + 1,
      par: Number(h.par) || 3,
      meters:
        h.meters !== null && h.meters !== undefined && `${h.meters}` !== ''
          ? Number(h.meters)
          : null,
    }));
  }

  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/discgolf/game/create', {
    method: 'POST',
    body: JSON.stringify(body),
    headers: headers(),
  })
    .then(async (res: any) => {
      draftGame.value = res;
      await loadRecommendedHandicaps();
      step.value = 2;
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se vytvořit hru.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

/* ------------------------------------------------------------------ Step 2 */
const players = ref<any[]>([]);
const selectedPlayers = ref<Record<number, { handicap: number; total_throws: number | null }>>({});
const quickRetro = ref(false);
const summaryGame = ref<any>(null);
const recommendedHandicaps = ref<Record<number, number>>({});

async function loadRecommendedHandicaps() {
  const courseName =
    courseMode.value === 'existing' ? selectedCourse.value?.name : customCourseName.value.trim();
  if (!courseName) {
    recommendedHandicaps.value = {};
    return;
  }
  const client = useSanctumClient();
  await client('/api/admin/discgolf/stats/courses', {
    method: 'GET',
    query: { include_hidden: 1 },
    headers: headers(),
  })
    .then((res: any) => {
      const data = res.data ?? res ?? [];
      const key = courseName.trim().toLowerCase();
      const match = data.find((c: any) => (c.course_name ?? '').trim().toLowerCase() === key);
      const map: Record<number, number> = {};
      (match?.players ?? []).forEach((p: any) => {
        map[p.player_id] = p.recommended_handicap ?? 0;
      });
      recommendedHandicaps.value = map;
    })
    .catch(() => {
      recommendedHandicaps.value = {};
    });
}

async function loadPlayers() {
  const client = useSanctumClient();
  await client('/api/admin/discgolf/player', {
    method: 'GET',
    query: { paginate: 200, orderBy: 'position', orderWay: 'asc' },
    headers: headers(),
  })
    .then((res: any) => {
      players.value = res.data ?? res ?? [];
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst hráče.', severity: 'error' });
    });
}

function isSelected(playerId: number) {
  // Property access (get trap) so Vue tracks it reactively — hasOwnProperty does not.
  return selectedPlayers.value[playerId] !== undefined;
}
function togglePlayer(playerId: number) {
  if (isSelected(playerId)) {
    const { [playerId]: _removed, ...rest } = selectedPlayers.value;
    selectedPlayers.value = rest;
  } else {
    selectedPlayers.value[playerId] = {
      handicap: recommendedHandicaps.value[playerId] ?? 0,
      total_throws: null,
    };
  }
}

const selectedPlayerIds = computed(() =>
  Object.keys(selectedPlayers.value).map((id) => Number(id)),
);

function equalizeHandicaps() {
  const ids = selectedPlayerIds.value;
  if (ids.length < 2) return;
  // Nejsilnější hráč = nejvyšší (nejméně záporný) handicap; ostatní se vůči němu posunou tak,
  // aby si zachovali stejný vzájemný rozestup, ale nejsilnější skončí na 0.
  const strongest = Math.max(...ids.map((id) => selectedPlayers.value[id].handicap));
  ids.forEach((id) => {
    selectedPlayers.value[id].handicap -= strongest;
  });
}

async function savePlayers() {
  if (!selectedPlayerIds.value.length) {
    $toast.show({
      summary: 'Upozornění',
      detail: 'Vyberte alespoň jednoho hráče.',
      severity: 'warn',
    });
    return false;
  }
  const payload = {
    players: selectedPlayerIds.value.map((id) => {
      const entry = selectedPlayers.value[id];
      const p: any = { player_id: id, handicap: Number(entry.handicap) || 0 };
      if (quickRetro.value) {
        p.total_throws = entry.total_throws !== null ? Number(entry.total_throws) : 0;
      }
      return p;
    }),
  };
  const client = useSanctumClient();
  loading.value = true;
  let ok = false;
  await client(`/api/admin/discgolf/game/${draftGame.value.id}/players`, {
    method: 'POST',
    body: JSON.stringify(payload),
    headers: headers(),
  })
    .then(() => {
      ok = true;
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit hráče.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
  return ok;
}

async function startGame() {
  if (!(await savePlayers())) return;
  const client = useSanctumClient();
  loading.value = true;
  await client(`/api/admin/discgolf/game/${draftGame.value.id}/start`, {
    method: 'POST',
    headers: headers(),
  })
    .then(() => {
      router.push(`/discgolf/hry/${draftGame.value.id}/hra`);
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se zahájit hru.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function completeRetro() {
  // validate totals
  const missing = selectedPlayerIds.value.some((id) => {
    const v = selectedPlayers.value[id].total_throws;
    return v === null || v === undefined || `${v}` === '';
  });
  if (missing) {
    $toast.show({
      summary: 'Upozornění',
      detail: 'Zadejte celkový počet hodů u všech hráčů.',
      severity: 'warn',
    });
    return;
  }
  if (!(await savePlayers())) return;
  const client = useSanctumClient();
  loading.value = true;
  await client(`/api/admin/discgolf/game/${draftGame.value.id}/complete`, {
    method: 'POST',
    body: JSON.stringify({ note: note.value || null }),
    headers: headers(),
  })
    .then((res: any) => {
      summaryGame.value = res;
      step.value = 3;
      $toast.show({
        summary: 'Hotovo',
        detail: 'Hra byla uložena a dokončena.',
        severity: 'success',
      });
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se dokončit hru.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

const inputClass =
  'block w-full rounded-xl border-0 px-4 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500';

useHead({
  title: pageTitle,
});
onMounted(() => {
  loadCourses();
  loadPlayers();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="games" />

    <!-- Step indicator -->
    <div class="mx-auto flex max-w-3xl items-center gap-2 px-2">
      <template v-for="(label, index) in ['Hřiště', 'Hráči', 'Souhrn']" :key="index">
        <div class="flex items-center gap-2">
          <div
            class="flex size-8 items-center justify-center rounded-full text-sm font-bold"
            :class="
              step > index + 1
                ? 'bg-emerald-500 text-white'
                : step === index + 1
                  ? 'bg-indigo-600 text-white'
                  : 'bg-slate-200 text-slate-500'
            "
          >
            {{ index + 1 }}
          </div>
          <span
            class="text-sm font-semibold"
            :class="step === index + 1 ? 'text-slate-900' : 'text-slate-400'"
          >
            {{ label }}
          </span>
        </div>
        <div v-if="index < 2" class="h-px flex-1 bg-slate-200" />
      </template>
    </div>

    <!-- ================================================= STEP 1 -->
    <LayoutContainer v-if="step === 1" class="space-y-8">
      <div>
        <LayoutTitle>Výběr hřiště</LayoutTitle>
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
          <button
            type="button"
            class="flex items-center gap-3 rounded-2xl p-4 text-left ring-1 transition-all"
            :class="
              courseMode === 'existing'
                ? 'bg-indigo-50 ring-2 ring-indigo-500'
                : 'bg-white ring-slate-200 hover:ring-slate-300'
            "
            @click="courseMode = 'existing'"
          >
            <MapPinIcon class="size-6 text-indigo-600" />
            <div>
              <div class="font-semibold text-slate-800">Existující hřiště</div>
              <div class="text-xs text-slate-500">Vyber z uložených hřišť a layoutů</div>
            </div>
          </button>
          <button
            type="button"
            class="flex items-center gap-3 rounded-2xl p-4 text-left ring-1 transition-all"
            :class="
              courseMode === 'custom'
                ? 'bg-indigo-50 ring-2 ring-indigo-500'
                : 'bg-white ring-slate-200 hover:ring-slate-300'
            "
            @click="courseMode = 'custom'"
          >
            <PencilSquareIcon class="size-6 text-indigo-600" />
            <div>
              <div class="font-semibold text-slate-800">Vlastní (jednorázové)</div>
              <div class="text-xs text-slate-500">Zadej hřiště a jamky ručně</div>
            </div>
          </button>
        </div>
      </div>

      <!-- Existing -->
      <div v-if="courseMode === 'existing'" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Hřiště</label>
          <select v-model="selectedCourseId" :class="inputClass" @change="selectedLayoutId = null">
            <option :value="null" disabled>— vyber hřiště —</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Layout</label>
          <select v-model="selectedLayoutId" :class="inputClass" :disabled="!selectedCourseId">
            <option :value="null" disabled>— vyber layout —</option>
            <option v-for="l in availableLayouts" :key="l.id" :value="l.id">
              {{ l.name }} (par {{ l.par }}, {{ l.holes_count }} jamek)
            </option>
          </select>
        </div>
      </div>

      <!-- Custom -->
      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Název hřiště</label>
            <input
              v-model="customCourseName"
              type="text"
              :class="inputClass"
              placeholder="Např. Park u řeky"
            />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Název layoutu</label>
            <input
              v-model="customLayoutName"
              type="text"
              :class="inputClass"
              placeholder="Např. Krátký okruh"
            />
          </div>
        </div>

        <div>
          <div class="mb-3 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Jamky</LayoutTitle>
            <span class="text-sm text-slate-500">
              Celkový par: <strong class="text-slate-800">{{ customPar }}</strong> ·
              {{ customHoles.length }} jamek
            </span>
          </div>
          <div class="space-y-2">
            <div
              v-for="(hole, index) in customHoles"
              :key="index"
              class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200"
            >
              <div
                class="flex size-9 items-center justify-center rounded-lg bg-white font-bold text-slate-700 ring-1 ring-slate-200"
              >
                {{ index + 1 }}
              </div>
              <div class="flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500">Par</label>
                <input v-model.number="hole.par" type="number" min="1" :class="inputClass" />
              </div>
              <div class="flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500">Metry</label>
                <input
                  v-model.number="hole.meters"
                  type="number"
                  min="0"
                  :class="inputClass"
                  placeholder="—"
                />
              </div>
              <button
                type="button"
                class="mt-5 rounded-lg p-2 text-rose-600 hover:bg-rose-50"
                @click="removeHole(index)"
              >
                <TrashIcon class="size-4" />
              </button>
            </div>
          </div>
          <button
            type="button"
            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-600 ring-1 ring-slate-200 hover:bg-slate-50"
            @click="addHole"
          >
            <PlusIcon class="size-4" /> Přidat jamku
          </button>
        </div>
      </div>

      <!-- Common: played_at + note -->
      <div class="grid grid-cols-1 gap-6 border-t border-slate-100 pt-6 sm:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Datum a čas hry</label>
          <input v-model="playedAt" type="datetime-local" :class="inputClass" />
          <p class="mt-1 text-xs text-slate-400">
            Pro zpětné zadání můžeš zvolit i datum v minulosti.
          </p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Poznámka</label>
          <input v-model="note" type="text" :class="inputClass" placeholder="Nepovinné" />
        </div>
      </div>

      <div class="flex justify-end">
        <BaseButton variant="primary" size="lg" :disabled="loading" @click="createGame">
          <FlagIcon class="mr-2 size-4" /> Pokračovat na hráče
        </BaseButton>
      </div>
    </LayoutContainer>

    <!-- ================================================= STEP 2 -->
    <LayoutContainer v-if="step === 2" class="space-y-6">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
          >
            <UserGroupIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Hráči a handicap</LayoutTitle>
        </div>
        <BaseButton
          v-if="selectedPlayerIds.length >= 2"
          variant="secondary"
          size="sm"
          @click="equalizeHandicaps"
        >
          <ScaleIcon class="mr-1.5 size-4" /> Vyrovnat handikap
        </BaseButton>
      </div>

      <div
        class="flex items-center justify-between rounded-2xl bg-amber-50 px-5 py-4 ring-1 ring-amber-200"
      >
        <div class="flex items-center gap-3">
          <BoltIcon class="size-5 text-amber-500" />
          <div>
            <div class="font-semibold text-slate-800">Rychlý zápis (zpětně)</div>
            <div class="text-xs text-slate-500">
              Zadej rovnou celkový počet hodů a hru rovnou dokonči.
            </div>
          </div>
        </div>
        <BaseFormSwitch v-model:enabled="quickRetro" />
      </div>

      <div v-if="!players.length" class="py-8 text-center text-sm italic text-slate-400">
        Žádní hráči nejsou založeni. Nejdřív si přidej hráče v sekci Disc Golf → Hráči.
      </div>
      <div v-else class="space-y-2">
        <div
          v-for="player in players"
          :key="player.id"
          class="flex flex-col gap-3 rounded-2xl p-4 ring-1 transition-all sm:flex-row sm:items-center sm:justify-between"
          :class="
            isSelected(player.id) ? 'bg-indigo-50/60 ring-indigo-300' : 'bg-white ring-slate-200'
          "
        >
          <label class="flex items-center gap-3">
            <input
              type="checkbox"
              class="size-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
              :checked="isSelected(player.id)"
              @change="togglePlayer(player.id)"
            />
            <span class="font-semibold text-slate-800">{{ player.name }}</span>
          </label>

          <div v-if="isSelected(player.id)" class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-500">Handicap</label>
              <input
                v-model.number="selectedPlayers[player.id].handicap"
                type="number"
                class="w-20 rounded-xl border-0 px-3 py-2 text-center text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              />
              <span
                v-if="recommendedHandicaps[player.id] !== undefined"
                class="text-xs text-slate-400"
              >
                (doporučeno {{ recommendedHandicaps[player.id] }})
              </span>
            </div>
            <div v-if="quickRetro" class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-500">Celkem hodů</label>
              <input
                v-model.number="selectedPlayers[player.id].total_throws"
                type="number"
                min="0"
                placeholder="—"
                class="w-24 rounded-xl border-0 px-3 py-2 text-center text-sm font-semibold shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              />
            </div>
          </div>
        </div>
      </div>

      <div
        class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-between"
      >
        <BaseButton variant="secondary" size="lg" @click="step = 1">
          <ArrowLeftIcon class="mr-2 size-4" /> Zpět
        </BaseButton>
        <BaseButton
          v-if="quickRetro"
          variant="success"
          size="lg"
          :disabled="loading"
          @click="completeRetro"
        >
          <FlagIcon class="mr-2 size-4" /> Uložit a dokončit hru
        </BaseButton>
        <BaseButton v-else variant="primary" size="lg" :disabled="loading" @click="startGame">
          <FlagIcon class="mr-2 size-4" /> Zahájit hru
        </BaseButton>
      </div>
    </LayoutContainer>

    <!-- ================================================= STEP 3 (retro summary) -->
    <LayoutContainer v-if="step === 3 && summaryGame">
      <GameSummary :players="summaryGame.players ?? []" :par="summaryGame.par ?? 0" />
      <div class="mt-6 flex justify-end">
        <BaseButton variant="primary" size="lg" @click="router.push('/discgolf/hry')">
          Zpět na přehled her
        </BaseButton>
      </div>
    </LayoutContainer>
  </div>
</template>
