<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import dayjs from 'dayjs';

const countdown = ref('');

const targetDate = dayjs('2025-03-09T14:00:00');

function updateCountdown() {
  const now = dayjs();
  const duration = targetDate.diff(now);

  if (duration <= 0) {
    countdown.value = 'Time is up!';
    return;
  }

  const days = Math.floor(duration / (1000 * 60 * 60 * 24));
  const hours = Math.floor((duration % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((duration % (1000 * 60)) / 1000);

  countdown.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
}

let intervalId: number;

onMounted(() => {
  updateCountdown();
  intervalId = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
  clearInterval(intervalId);
});
</script>

<template>
  <div>
    <p class="mb-4 font-semibold text-gray-300">
      Do <span class="font-bold underline">HINTRU</span> zbývá:
    </p>
    <p class="text-dangerLight font-bold">
      {{ countdown }}
    </p>
  </div>
</template>
