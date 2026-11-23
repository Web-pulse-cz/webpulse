<script setup lang="ts">
import { ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  history: {
    type: Object,
    required: true,
  },
});

// description obsahuje předchozí hodnotu „Naposledy kontaktováno“ ve formátu Y-m-d H:i:s
const formattedDate = computed(() => {
  const raw = String(props.history.description ?? '');
  const date = new Date(raw.replace(' ', 'T'));
  return Number.isNaN(date.getTime()) ? raw : date.toLocaleDateString('cs-CZ');
});
</script>

<template>
  <li class="relative mb-8 ms-8">
    <div
      class="absolute -start-[2.4rem] mt-1.5 flex size-5 items-center justify-center rounded-full bg-slate-200 ring-4 ring-white"
    >
      <ClockIcon class="size-3 text-slate-500" />
    </div>
    <div class="flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-500">
      <span class="font-semibold text-slate-700">{{ formattedDate }}</span>
      <span>–</span>
      <span>poslední kontakt nebo pokus o kontakt</span>
    </div>
  </li>
</template>
