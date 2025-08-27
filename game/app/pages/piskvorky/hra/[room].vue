<script setup lang="ts">
import { useTicTacToe } from '~/composables/useTicTacToe';

type PresencePayload = {
  id: string;
  joinedAt: number;
  symbol?: 'X' | 'O';
};

const route = useRoute();
const room = computed(() => String(route.params.room));
const client = useSupabaseClient();
const { board, turn, winner, isFull, isOver, reset, playAt } = useTicTacToe();

const channel = ref<ReturnType<typeof client.channel> | null>(null);
const connected = ref(false);

const selfId = crypto.randomUUID();
const playersMap = ref<Record<string, PresencePayload>>({});
const mySymbol = ref<'X' | 'O' | null>(null);

const playersList = computed(() =>
  Object.values(playersMap.value).sort((a, b) => a.joinedAt - b.joinedAt),
);
const playersCount = computed(() => playersList.value.length);

const localTurnText = computed(() => {
  if (winner.value) return 'konec';
  return turn.value;
});

// připojení k Realtime channelu
onMounted(async () => {
  const ch = client.channel(`room:${room.value}`, {
    config: { presence: { key: selfId } },
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

    // přiřazení symbolů
    const order = Object.values(flat).sort((a, b) => a.joinedAt - b.joinedAt);
    if (order[0]) order[0].symbol = 'X';
    if (order[1]) order[1].symbol = 'O';
    mySymbol.value = flat[selfId]?.symbol || null;
  });

  ch.on('broadcast', { event: 'move' }, ({ payload }) => {
    const { index, symbol } = payload as { index: number; symbol: 'X' | 'O' };
    playAt(index, symbol);
  });

  ch.on('broadcast', { event: 'reset' }, () => {
    reset();
  });

  await ch.subscribe(async (status) => {
    if (status === 'SUBSCRIBED') {
      connected.value = true;
      ch.track({ id: selfId, joinedAt: Date.now() } as PresencePayload);
    }
  });
});

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
  <main class="flex min-h-screen flex-col items-center gap-6 p-6">
    <div class="w-full max-w-3xl">
      <div class="flex items-center justify-between">
        <NuxtLink to="/" class="text-sm underline">Zpět</NuxtLink>
        <div class="text-sm opacity-70">
          Místnost: <strong>{{ room }}</strong>
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
              class="flex aspect-square items-center justify-center rounded-xl border text-4xl font-bold transition hover:shadow"
              @click="handleMove(i)"
              :disabled="!canPlay(i)"
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
          <div class="text-xs opacity-70">
            Sdílej odkaz, aby se druhý hráč přidal do stejné místnosti.
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<style scoped>
main {
  background: radial-gradient(1200px 600px at 70% 0%, rgba(255, 255, 255, 0.06), transparent);
}
</style>
