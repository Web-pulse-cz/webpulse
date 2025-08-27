<script setup lang="ts">
import { createClient } from '@supabase/supabase-js';
import { useTicTacToe } from '~/composables/useTicTacToe';

type PresencePayload = {
  id: string;
  joinedAt: number;
  symbol?: 'X' | 'O';
};

const route = useRoute();
const router = useRouter();

const runtimeConfig = useRuntimeConfig();

const supabase = createClient(runtimeConfig.public.supabase.url, runtimeConfig.public.supabase.key);
const { board, turn, winner, isFull, isOver, reset, playAt } = useTicTacToe();

const channel = ref<ReturnType<typeof supabase.channel> | null>(null);
const connected = ref(false);

const selfId = ref('');
const playersMap = ref<Record<string, PresencePayload>>({});
const mySymbol = ref<'X' | 'O' | null>(null);

const playersList = computed(() =>
  Object.values(playersMap.value).sort((a, b) => a.joinedAt - b.joinedAt),
);
const playersCount = computed(() => playersList.value.length);

const localTurnText = computed(() => (winner.value ? 'konec' : turn.value));
const hash = ref<string>(''); // aktuální kód místnosti
const lobbyMode = computed(() => !hash.value); // lobby se ukazuje pokud hash chybí
const hashInput = ref('');

// připojení do místnosti
async function joinRoom(roomId: string) {
  if (channel.value) {
    try {
      await channel.value.unsubscribe();
    } catch {}
    channel.value = null;
  }

  const ch = supabase.channel(`room:${roomId}`, {
    config: { presence: { key: selfId.value } },
  });
  channel.value = ch;

  ch.on('presence', { event: 'sync' }, () => {
    const state = ch.presenceState() as Record<string, PresencePayload[]>;
    const flat: Record<string, PresencePayload> = {};
    Object.keys(state).forEach((key) => {
      const arr = state[key];
      if (arr && arr[0]) flat[key] = arr[0];
    });
    playersMap.value = flat;

    const order = Object.values(flat).sort((a, b) => a.joinedAt - b.joinedAt);
    if (order[0]) order[0].symbol = 'X';
    if (order[1]) order[1].symbol = 'O';
    mySymbol.value = flat[selfId.value]?.symbol || null;
  });

  ch.on('broadcast', { event: 'move' }, ({ payload }) => {
    const { index, symbol } = payload as { index: number; symbol: 'X' | 'O' };
    playAt(index, symbol);
  });

  ch.on('broadcast', { event: 'reset' }, () => reset());

  await ch.subscribe((status: string) => {
    if (status === 'SUBSCRIBED') {
      connected.value = true;
      ch.track({ id: selfId.value, joinedAt: Date.now() } as PresencePayload);
    }
  });
}

// vytvoření nové hry
async function createRoom() {
  const id = crypto.randomUUID().slice(0, 8);
  await router.push({ path: '/piskvorky', query: { hash: id } });
}

// připojení k existující hře
async function connectToHashInput() {
  const id = hashInput.value.trim();
  if (!id) return;
  await router.push({ path: '/piskvorky', query: { hash: id } });
}

// lifecycle
onMounted(async () => {
  selfId.value = crypto.randomUUID();
  const incoming = (route.query.hash as string | undefined)?.trim();
  if (incoming) {
    hash.value = incoming;
    await joinRoom(hash.value);
  }
});

watch(
  () => route.query.hash,
  async (val) => {
    const next = (val as string | undefined)?.trim();
    if (next && next !== hash.value) {
      hash.value = next;
      reset();
      await joinRoom(hash.value);
    } else if (!next) {
      hash.value = '';
      reset();
    }
  },
);

onBeforeUnmount(() => {
  channel.value?.unsubscribe();
});

// herní akce
function canPlay(i: number) {
  if (!mySymbol.value) return false;
  if (isOver.value) return false;
  if (board.value[i]) return false;
  return turn.value === mySymbol.value && playersCount.value >= 2;
}

function handleMove(i: number) {
  if (!channel.value || !mySymbol.value) return;
  if (!canPlay(i)) return;
  channel.value.send({
    type: 'broadcast',
    event: 'move',
    payload: { index: i, symbol: mySymbol.value },
  });
}

function emitReset() {
  if (!channel.value) return;
  channel.value.send({ type: 'broadcast', event: 'reset', payload: {} });
}

async function copyLink() {
  await navigator.clipboard.writeText(window.location.href);
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center p-6">
    <!-- Lobby -->
    <div v-if="lobbyMode" class="w-full max-w-md space-y-6">
      <h1 class="text-center text-3xl font-bold">Piškvorky online</h1>
      <div class="space-y-4 rounded-2xl border bg-white/5 p-6 shadow">
        <button class="w-full rounded-2xl border py-3 transition hover:shadow" @click="createRoom">
          Vytvořit novou hru
        </button>

        <div class="flex items-center gap-3">
          <input
            v-model="hashInput"
            type="text"
            placeholder="Zadej kód místnosti"
            class="flex-1 rounded-xl border bg-transparent px-3 py-2"
          />
          <button class="rounded-xl border px-4 py-2" @click="connectToHashInput">Připojit</button>
        </div>
      </div>
      <p class="text-center text-sm opacity-70">
        Po vytvoření místnosti dostaneš odkaz <code>/piskvorky?hash=...</code>
      </p>
    </div>

    <!-- Hra -->
    <div v-else class="w-full max-w-3xl">
      <div class="flex items-center justify-between">
        <NuxtLink to="/piskvorky" class="text-sm underline">Zpět do lobby</NuxtLink>
        <div class="text-sm opacity-70">
          Místnost: <strong>{{ hash }}</strong>
        </div>
      </div>

      <div class="mt-4 grid gap-6 md:grid-cols-[2fr,1fr]">
        <!-- Board -->
        <div class="flex flex-col items-center rounded-2xl border bg-white/5 p-6 shadow">
          <div class="mb-4 text-center">
            <p v-if="!connected" class="text-sm">Připojování...</p>
            <p v-else-if="playersCount < 2" class="text-sm">Čekám na protihráče</p>
            <p v-else class="text-sm">
              Na tahu: <strong>{{ localTurnText }}</strong>
            </p>
            <p v-if="winner" class="mt-1 text-lg font-semibold">
              Výsledek: {{ winner === 'X' ? 'Vyhrál hráč X' : 'Vyhrál hráč O' }}
            </p>
            <p v-else-if="isFull" class="mt-1 text-lg font-semibold">Remíza</p>
          </div>

          <div class="grid w-full max-w-md grid-cols-3 gap-2">
            <button
              v-for="(cell, i) in board"
              :key="i"
              class="flex aspect-square items-center justify-center rounded-xl border text-4xl font-bold transition hover:shadow disabled:opacity-50"
              :disabled="!canPlay(i)"
              @click="handleMove(i)"
            >
              {{ cell }}
            </button>
          </div>

          <div class="mt-6 flex gap-3">
            <button class="rounded-xl border px-4 py-2" @click="copyLink">Zkopírovat odkaz</button>
            <button class="rounded-xl border px-4 py-2" @click="emitReset">Restart</button>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-4 rounded-2xl border bg-white/5 p-6 shadow">
          <h2 class="font-semibold">Hráči</h2>
          <ul class="space-y-1 text-sm">
            <li v-for="p in playersList" :key="p.id">
              {{ p.id === selfId ? 'Ty' : 'Host' }} {{ p.symbol ? `(${p.symbol})` : '' }}
            </li>
          </ul>
          <div class="pt-2 text-sm">
            Tvoje značka: <strong>{{ mySymbol || 'čekám...' }}</strong>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>
