<script setup lang="ts">
import { ClipboardDocumentListIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps<{
  card: {
    id: number;
    title?: string;
    description?: string | null;
    note?: string | null;
    priority?: string;
    due_date?: string | null;
    created_at?: string | null;
    section?: { id: number; name: string; color: string } | null;
  };
}>();

const priorityLabels: Record<string, string> = {
  low: 'Nízká',
  medium: 'Střední',
  high: 'Vysoká',
  critical: 'Kritická',
};

const priorityColors: Record<string, string> = {
  critical: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  medium: 'bg-slate-100 text-slate-600',
  low: 'bg-blue-100 text-blue-600',
};
</script>

<template>
  <li class="relative mb-8 ms-8">
    <div
      class="absolute -start-11 mt-4 flex h-7 w-7 items-center justify-center rounded-full border border-violet-200 bg-violet-50 shadow-sm ring-4 ring-white"
    >
      <ClipboardDocumentListIcon class="size-4 text-violet-500" />
    </div>

    <div
      class="group relative flex flex-col rounded-2xl border border-violet-100 bg-violet-50/40 p-5 shadow-sm transition-all duration-200 hover:border-violet-200 hover:shadow-md"
    >
      <div class="mb-3 flex items-start justify-between gap-4">
        <div class="flex gap-4">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 transition-transform duration-200 group-hover:scale-105"
          >
            <ClipboardDocumentListIcon class="size-6" />
          </div>

          <div class="flex flex-col justify-center">
            <div class="flex items-center gap-2">
              <span
                class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-700"
              >
                Nástěnka
              </span>
              <span
                v-if="card.section"
                class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200"
              >
                {{ card.section.name }}
              </span>
            </div>
            <h3 class="mt-1 text-sm font-bold text-slate-900 lg:text-base">
              {{ card.title }}
            </h3>
            <div
              v-if="card.created_at"
              class="mt-1 flex items-center gap-1.5 text-xs font-medium text-slate-500"
            >
              <ClockIcon class="size-4" />
              <time>{{ new Date(card.created_at).toLocaleDateString() }}</time>
            </div>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <span
            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold"
            :class="priorityColors[card.priority]"
          >
            {{ priorityLabels[card.priority] || card.priority }}
          </span>
          <span v-if="card.due_date" class="shrink-0 text-xs text-slate-400">
            do {{ new Date(card.due_date).toLocaleDateString() }}
          </span>
        </div>
      </div>

      <div v-if="card.description || card.note" class="space-y-1 pl-[4rem]">
        <p v-if="card.description" class="text-sm leading-relaxed text-slate-600 lg:text-base">
          {{ card.description }}
        </p>
        <p v-if="card.note" class="text-xs italic leading-relaxed text-slate-400">
          {{ card.note }}
        </p>
      </div>

      <div class="mt-3 pl-[4rem]">
        <NuxtLink
          to="/nastenka"
          class="text-xs font-semibold text-violet-600 hover:underline"
        >
          Otevřít v nástěnce →
        </NuxtLink>
      </div>
    </div>
  </li>
</template>
