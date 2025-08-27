<script setup lang="ts">
const roomId = ref('');

function createRoom() {
  const id =
    typeof window !== 'undefined'
      ? crypto.randomUUID().slice(0, 8)
      : Math.random().toString(36).slice(2, 10);
  navigateTo(`/game/${id}`);
}

function joinRoom() {
  if (!roomId.value.trim()) return;
  navigateTo(`/hra/${roomId.value.trim()}`);
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center p-6">
    <div class="w-full max-w-md space-y-6">
      <BasePropsHeading type="h1" class="text-center">Piškvorky</BasePropsHeading>

      <div class="space-y-4 rounded-2xl border bg-white/5 p-6 shadow">
        <button class="w-full rounded-2xl border py-3 transition hover:shadow" @click="createRoom">
          Vytvořit novou hru
        </button>

        <div class="flex items-center gap-3">
          <input
            v-model="roomId"
            type="text"
            placeholder="Zadej ID místnosti"
            class="flex-1 rounded-xl border bg-transparent px-3 py-2"
          />
          <button class="rounded-xl border px-4 py-2" @click="joinRoom">Připojit</button>
        </div>
      </div>

      <p class="text-center text-sm opacity-70">
        Sdílej odkaz s kamarádem. Jakmile se připojí, hra začne.
      </p>
    </div>
  </main>
</template>
