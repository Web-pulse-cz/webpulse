<script setup lang="ts">
import { createClient } from '@supabase/supabase-js';
// Adjacency Duel — originální tahová multiplayer hra s flippy sousedů
//  - Jediný soubor stránky: pages/arena.vue
//  - Realtime přes Supabase Channels
//  - libovolně hráčů A, B, C..., robot "R" při jednom hráči (volitelně)
//  - Pravidla jsou dole v <details>

// ----- Nuxt & Supabase -----
const route = useRoute();
const router = useRouter();

const runtimeConfig = useRuntimeConfig();
const supabase = createClient(runtimeConfig.public.supabase.url, runtimeConfig.public.supabase.key);
// ----- Typy -----
type Cell = '' | string; // '', 'A','B','C'... nebo 'R' pro robota
type Presence = { id: string; joinedAt: number; symbol?: string };
type PresenceState = Record<string, Presence[]>;

// ----- Stav hry -----
const SIZE = 8;
const MAX_ROUNDS_PER_PLAYER = 10;

const hash = ref<string>(''); // ID místnosti z query
const selfId = ref<string>(''); // lokální presence klíč
const connected = ref(false);
const channel = ref<ReturnType<typeof supabase.channel> | null>(null);

const board = ref<Cell[]>(Array(SIZE * SIZE).fill(''));
const turnIndex = ref(0); // index v pořadí hráčů
const roundCount = ref(0); // počet celých kol (všichni odehráli)
const gameOver = ref(false);

// presence → map { presenceKey: Presence }
const playersMap = ref<Record<string, Presence>>({});
const playersList = computed(() =>
  Object.entries(playersMap.value)
    .map(([k, v]) => ({ key: k, ...v }))
    .sort((a, b) => a.joinedAt - b.joinedAt),
);
const playersCount = computed(() => playersList.value.length);

// moje symbolika
const mySymbol = computed(() => playersMap.value[selfId.value]?.symbol ?? null);
const amHost = computed(() => playersList.value[0]?.key === selfId.value);

// Robot — zapíná jen host, a funguje pouze pokud je v místnosti 1 hráč
const botEnabled = ref(false);
const BOT_SYMBOL = 'R';

// UI helpers
const localStatus = computed(() => {
  if (!connected.value) return 'Připojování...';
  if (gameOver.value) return 'Konec hry';
  if (turnOrder.value.length === 0) return 'Čekám na hráče';
  return `Na tahu: ${currentSymbol.value}`;
});

const scores = computed<Record<string, number>>(() => {
  const m: Record<string, number> = {};
  for (const c of board.value) if (c) m[c] = (m[c] ?? 0) + 1;
  return m;
});

// ----- Pořadí hráčů -----
const symbolsForPlayers = computed(() => {
  // symboly A, B, C, D... podle pořadí připojení
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
  const out: Record<string, string> = {};
  playersList.value.forEach((p, idx) => {
    out[p.key] = letters[idx] ?? letters[letters.length - 1];
  });
  return out;
});

const turnOrder = computed<string[]>(() => {
  // Z hráčů vezmeme jejich symboly (A,B,...) v pořadí připojení
  const humanSymbols = playersList.value.map((p) => symbolsForPlayers.value[p.key]);
  // Pokud je zapnutý bot a je jen 1 hráč, přidej 'R'
  if (botEnabled.value && humanSymbols.length === 1) return [humanSymbols[0], BOT_SYMBOL];
  return humanSymbols;
});

const currentSymbol = computed(
  () => turnOrder.value[turnIndex.value % turnOrder.value.length] ?? '',
);

// ----- Utils -----
function idx(r: number, c: number) {
  return r * SIZE + c;
}
function rcFrom(i: number) {
  return [Math.floor(i / SIZE), i % SIZE] as const;
}

function neighbors4(i: number) {
  const [r, c] = rcFrom(i);
  const out: number[] = [];
  if (r > 0) out.push(idx(r - 1, c));
  if (r < SIZE - 1) out.push(idx(r + 1, c));
  if (c > 0) out.push(idx(r, c - 1));
  if (c < SIZE - 1) out.push(idx(r, c + 1));
  return out;
}

function ownsAny(symbol: string) {
  return board.value.some((c) => c === symbol);
}

function isAdjacentToMine(i: number, symbol: string) {
  return neighbors4(i).some((n) => board.value[n] === symbol);
}

function canPlayAt(i: number, symbol: string) {
  if (gameOver.value) return false;
  if (!symbol) return false;
  if (board.value[i]) return false;
  // první kámen hráče kamkoli, jinak ortogonální sousedství s vlastními
  return !ownsAny(symbol) || isAdjacentToMine(i, symbol);
}

function flipCapturedBy(symbol: string) {
  // „Obklíčení“: pro každé pole, které není moje, ověř, zda všechny
  // existující ortogonální sousedy vlastní 'symbol' → pak flipni na 'symbol'
  // Pro jistotu dělej dokud se něco mění (řetězení).
  let changed = true;
  while (changed) {
    changed = false;
    for (let i = 0; i < board.value.length; i++) {
      const cell = board.value[i];
      if (!cell || cell === symbol) continue;
      const ns = neighbors4(i);
      if (ns.length > 0 && ns.every((n) => board.value[n] === symbol)) {
        board.value[i] = symbol;
        changed = true;
      }
    }
  }
}

function advanceTurn() {
  if (turnOrder.value.length === 0) return;
  const before = turnIndex.value;
  turnIndex.value = (turnIndex.value + 1) % turnOrder.value.length;
  if (turnIndex.value === 0 && before !== 0) {
    roundCount.value++;
    // konec po X kolech na hráče
    const humans = playersCount.value;
    const limit = MAX_ROUNDS_PER_PLAYER * Math.max(1, turnOrder.value.length);
    if (before !== 0 && roundCount.value * turnOrder.value.length >= limit) {
      gameOver.value = true;
    }
  }
  // konec při plném boardu
  if (board.value.every(Boolean)) gameOver.value = true;
}

// ----- Realtime: připojení do místnosti -----
async function joinRoom(roomId: string) {
  if (channel.value) {
    try {
      await channel.value.unsubscribe();
    } catch {}
    channel.value = null;
  }
  const ch = supabase.channel(`arena:${roomId}`, { config: { presence: { key: selfId.value } } });
  channel.value = ch;

  ch.on('presence', { event: 'sync' }, () => {
    const state = ch.presenceState() as PresenceState;
    const flat: Record<string, Presence> = {};
    Object.keys(state).forEach((k) => {
      const a = state[k];
      if (a?.[0]) flat[k] = a[0];
    });
    // Přiděl symboly podle pořadí
    const ordered = Object.entries(flat).sort((a, b) => a[1].joinedAt - b[1].joinedAt);
    ordered.forEach(([k, v], i) => (flat[k] = { ...v, symbol: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[i] }));
    playersMap.value = flat;
  });

  ch.on('broadcast', { event: 'move' }, ({ payload }) => {
    const { index, symbol } = payload as { index: number; symbol: string };
    applyMove(index, symbol, false); // false = nepřeskakuj pořadí dvakrát
  });

  ch.on('broadcast', { event: 'reset' }, () => resetGame(false));

  ch.on('broadcast', { event: 'bot' }, ({ payload }) => {
    const { enabled } = payload as { enabled: boolean };
    botEnabled.value = enabled;
  });

  await ch.subscribe((status: string) => {
    if (status === 'SUBSCRIBED') {
      connected.value = true;
      ch.track({ id: selfId.value, joinedAt: Date.now() } as Presence);
    }
  });
}

// ----- Aplikace tahu -----
function applyMove(index: number, symbol: string, broadcast: boolean) {
  if (!canPlayAt(index, symbol)) return;
  board.value[index] = symbol;
  // flipni obklíčená pole
  flipCapturedBy(symbol);
  // posuň tah
  advanceTurn();
  if (broadcast && channel.value) {
    channel.value.send({ type: 'broadcast', event: 'move', payload: { index, symbol } });
  }
}

function tryPlay(i: number) {
  if (gameOver.value) return;
  const me = mySymbol.value;
  if (!me) return;
  if (currentSymbol.value !== me) return;
  if (!canPlayAt(i, me)) return;
  applyMove(i, me, true);
  maybeBotTurn(); // po mém tahu může hrát robot (pokud je aktivní a na něm řada)
}

// ----- Robot (lokální u hostitele) -----
function robotChooseMove(symbol: string): number | null {
  // 1) preferuj tahy, které okamžitě flipnou aspoň 1 cíl
  const candidates: number[] = [];
  for (let i = 0; i < board.value.length; i++) {
    if (!board.value[i] && canPlayAt(i, symbol)) {
      // simulace
      const snapshot = board.value.slice();
      snapshot[i] = symbol;
      // zjisti, zda by flip proběhl
      const wouldFlip = neighbors4(i).some((n) => snapshot[n] && snapshot[n] !== symbol);
      // hrubší vyhodnocení — spočti kolik flipů by nastalo
      let flips = 0;
      // jednoduchá aproximace: spočítej políčka, která mají po tahu všechny sousedy = symbol
      for (let j = 0; j < snapshot.length; j++) {
        const cell = snapshot[j];
        if (cell && cell !== symbol) {
          const ns = neighbors4(j);
          if (ns.length > 0 && ns.every((n) => (n === i ? symbol : snapshot[n]) === symbol))
            flips++;
        }
      }
      if (flips > 0) candidates.push(i);
    }
  }
  if (candidates.length) return candidates[Math.floor(Math.random() * candidates.length)];
  // 2) jinak libovolný validní sousedící tah
  const valid: number[] = [];
  for (let i = 0; i < board.value.length; i++) {
    if (!board.value[i] && canPlayAt(i, symbol)) valid.push(i);
  }
  if (valid.length) return valid[Math.floor(Math.random() * valid.length)];
  return null;
}

function maybeBotTurn() {
  // Robot je aktivní, jen když je v místnosti 1 člověk, bot zapnutý, a jsem host
  if (!amHost.value) return;
  if (!botEnabled.value) return;
  if (playersCount.value !== 1) return;
  if (currentSymbol.value !== BOT_SYMBOL) return;
  const move = robotChooseMove(BOT_SYMBOL);
  if (move != null) {
    applyMove(move, BOT_SYMBOL, true); // broadcastneme, aby to viděli všichni
  } else {
    // nemůže hrát → posuň tah
    advanceTurn();
  }
}

// ----- Reset -----
function resetGame(broadcast: boolean) {
  board.value = Array(SIZE * SIZE).fill('');
  turnIndex.value = 0;
  roundCount.value = 0;
  gameOver.value = false;
  if (broadcast && channel.value) {
    channel.value.send({ type: 'broadcast', event: 'reset', payload: {} });
  }
  // po resetu když je bot aktivní a je na něm řada, nech ho hrát
  setTimeout(() => maybeBotTurn(), 100);
}

// ----- UI akce -----
function clickCell(i: number) {
  tryPlay(i);
}
function toggleBot() {
  if (!amHost.value) return;
  botEnabled.value = !botEnabled.value;
  channel.value?.send({ type: 'broadcast', event: 'bot', payload: { enabled: botEnabled.value } });
  // jestli je nyní na tahu robot, okamžitě ho nechej hrát
  setTimeout(() => maybeBotTurn(), 50);
}

// ----- Barvy pro symboly -----
function symbolClass(sym: string | null | undefined) {
  switch (sym) {
    case 'A':
      return 'bg-blue-600 text-white';
    case 'B':
      return 'bg-emerald-600 text-white';
    case 'C':
      return 'bg-amber-600 text-white';
    case 'D':
      return 'bg-fuchsia-600 text-white';
    case 'E':
      return 'bg-rose-600 text-white';
    case 'R':
      return 'bg-slate-700 text-white'; // robot
    default:
      return 'bg-white/0';
  }
}

// ----- Lifecycle & routing -----
onMounted(async () => {
  selfId.value = crypto.randomUUID();

  const incoming = (route.query.hash as string | undefined)?.trim();
  if (incoming) {
    hash.value = incoming;
  } else {
    hash.value = crypto.randomUUID().slice(0, 8);
    await router.replace({ path: '/arena', query: { hash: hash.value } });
  }
  await joinRoom(hash.value);
  // pokud je pouze 1 hráč, a jsem host → auto-zapnout bota pro rychlé demo
  setTimeout(() => {
    if (amHost.value && playersCount.value === 1 && !botEnabled.value) {
      botEnabled.value = true;
      channel.value?.send({ type: 'broadcast', event: 'bot', payload: { enabled: true } });
      maybeBotTurn();
    }
  }, 500);
});

watch(
  () => route.query.hash,
  async (v) => {
    const next = (v as string | undefined)?.trim();
    if (next && next !== hash.value) {
      hash.value = next;
      resetGame(false);
      await joinRoom(hash.value);
    }
  },
);

onBeforeUnmount(() => {
  channel.value?.unsubscribe();
});
</script>

<template>
  <main class="flex min-h-screen flex-col items-center gap-6 p-6">
    <div class="w-full max-w-4xl">
      <header class="flex items-center justify-between gap-3">
        <div class="text-sm opacity-70">
          Místnost: <strong>{{ hash }}</strong>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="rounded-xl border px-3 py-2 text-sm"
            @click="
              async () => {
                await navigator.clipboard.writeText(window.location.href);
              }
            "
          >
            Kopírovat odkaz
          </button>

          <button class="rounded-xl border px-3 py-2 text-sm" @click="resetGame(true)">
            Restart
          </button>

          <label v-if="amHost" class="flex items-center gap-2 rounded-xl border px-3 py-2 text-sm">
            <input v-model="botEnabled" type="checkbox" @change="toggleBot" />
            Robot
          </label>
        </div>
      </header>

      <section class="mt-2 grid gap-6 md:grid-cols-[2fr,1fr]">
        <!-- Board -->
        <div class="rounded-2xl border bg-white/5 p-6 shadow">
          <div class="mb-4 text-sm">
            <span v-if="!connected">Připojování...</span>
            <span v-else>{{ localStatus }}</span>
          </div>

          <div
            class="grid gap-2"
            :style="{ gridTemplateColumns: `repeat(${SIZE}, minmax(0, 1fr))` }"
          >
            <button
              v-for="(cell, i) in board"
              :key="i"
              class="grid aspect-square place-items-center rounded-xl border text-2xl font-bold transition hover:shadow disabled:opacity-50"
              :class="symbolClass(cell)"
              :disabled="
                gameOver ||
                !mySymbol ||
                currentSymbol !== mySymbol ||
                !(
                  !cell &&
                  (!board.some((c) => c === mySymbol) ||
                    neighbors4(i).some((n) => board[n] === mySymbol))
                )
              "
              @click="clickCell(i)"
            >
              <span>{{ cell }}</span>
            </button>
          </div>

          <div class="mt-4 text-sm">
            <span class="opacity-70">Kola:</span> {{ roundCount }}
            <span class="ml-4 opacity-70">Obsazeno:</span> {{ board.filter(Boolean).length }}/{{
              SIZE * SIZE
            }}
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-4 rounded-2xl border bg-white/5 p-6 shadow">
          <h2 class="font-semibold">Hráči</h2>
          <ul class="space-y-2 text-sm">
            <li v-for="p in playersList" :key="p.key" class="flex items-center gap-2">
              <span
                class="inline-flex h-6 w-6 items-center justify-center rounded-md border text-xs font-bold"
                :class="symbolClass(p.symbol)"
              >
                {{ p.symbol }}
              </span>
              <span>{{ p.key === selfId ? 'Ty' : 'Host' }}</span>
            </li>
            <li v-if="botEnabled && playersCount === 1" class="flex items-center gap-2">
              <span
                class="inline-flex h-6 w-6 items-center justify-center rounded-md border text-xs font-bold"
                :class="symbolClass('R')"
                >R</span
              >
              <span>Robot</span>
            </li>
          </ul>

          <div class="text-sm">
            <div>
              Tvoje značka:
              <strong class="ml-1">{{ mySymbol || 'čekám...' }}</strong>
            </div>
            <div v-if="gameOver" class="mt-2">
              <div class="font-semibold">Konec hry</div>
              <div class="text-xs opacity-70">Vítězí hráč s nejvíce poli.</div>
            </div>

            <div class="mt-4">
              <h3 class="mb-1 text-sm font-semibold">Skóre (počet polí):</h3>
              <ul class="space-y-1 text-sm">
                <li v-for="(v, k) in scores" :key="k" class="flex items-center gap-2">
                  <span
                    class="inline-flex h-5 w-5 items-center justify-center rounded border text-[10px] font-bold"
                    :class="symbolClass(k)"
                    >{{ k }}</span
                  >
                  <span>{{ v }}</span>
                </li>
              </ul>
            </div>
          </div>

          <details class="mt-4 text-sm">
            <summary class="cursor-pointer font-semibold">Pravidla</summary>
            <ul class="ml-5 mt-2 list-disc space-y-1">
              <li>První tah každého hráče: kamkoli.</li>
              <li>Další tahy: pouze na prázdné pole ortogonálně sousedící s tvojí runou.</li>
              <li>
                Flip: pole soupeře, které má na všech svých ortogonálních sousedech tvoje runy, se
                přebarví na tvoji barvu. Může se řetězit.
              </li>
              <li>
                Konec: po zaplnění 5×5 nebo po 10 kolech na hráče. Vyhrává nejvíc obsazených polí.
              </li>
              <li>
                Robot R hraje jen pokud je v místnosti jeden hráč a je zapnutý přepínač Robot.
              </li>
            </ul>
          </details>
        </aside>
      </section>
    </div>
  </main>
</template>

<style scoped>
main {
  background: radial-gradient(1200px 600px at 70% 0%, rgba(255, 255, 255, 0.06), transparent);
}
</style>
