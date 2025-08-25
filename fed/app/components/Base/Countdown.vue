<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import dayjs from 'dayjs';

const countdown = ref('');

const targetDate = dayjs('2025-11-01T11:00:00');

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

  countdown.value = `${days} dnů ${hours} hodin ${minutes} minut ${seconds} sekund`;
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
    <p class="mb-4 text-center text-gray-600">
      Do
      <NuxtLink to="https://fed2025.cz/" class="font-bold" target="_blank">FED</NuxtLink>
      zbývá:
      <span class="text-red-600">{{ countdown }}</span>
    </p>
  </div>
</template>
