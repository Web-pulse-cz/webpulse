<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { CheckIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

export interface PersonalGrowthTask {
  id: number | null;
  title: string;
  is_done: boolean;
  done_at?: string | null;
}

// Úkoly z knihy / semináře. Hotové úkoly zůstávají v seznamu (přeškrtnuté).
const tasks = defineModel<PersonalGrowthTask[]>({ default: () => [] });

defineProps({
  placeholder: { type: String, default: 'Co chci udělat…' },
});

const draft = ref('');
const draftInput = ref<HTMLInputElement | null>(null);

const doneCount = computed(() => tasks.value.filter((task) => task.is_done).length);

async function addTask() {
  const title = draft.value.trim();
  if (!title) return;
  tasks.value = [...tasks.value, { id: null, title, is_done: false }];
  draft.value = '';
  await nextTick();
  draftInput.value?.focus();
}

function toggle(index: number) {
  const task = tasks.value[index];
  if (!task) return;
  task.is_done = !task.is_done;
  task.done_at = task.is_done ? new Date().toISOString() : null;
}

function remove(index: number) {
  tasks.value = tasks.value.filter((_, i) => i !== index);
}

function formatDone(value?: string | null) {
  if (!value) return '';
  return new Date(value).toLocaleDateString('cs-CZ');
}
</script>

<template>
  <div class="space-y-3">
    <div v-if="tasks.length" class="flex items-center justify-between">
      <span class="label-caps text-slate-500">Hotovo {{ doneCount }} / {{ tasks.length }}</span>
      <div class="h-1.5 w-32 overflow-hidden rounded-full bg-slate-100">
        <div
          class="h-full rounded-full bg-emerald-500 transition-all"
          :style="{ width: `${tasks.length ? (doneCount / tasks.length) * 100 : 0}%` }"
        />
      </div>
    </div>

    <ul
      v-if="tasks.length"
      role="list"
      class="divide-y divide-slate-100 rounded-xl ring-1 ring-slate-200"
    >
      <li
        v-for="(task, index) in tasks"
        :key="task.id ?? `new-${index}`"
        class="group flex items-center gap-3 px-3 py-2.5"
        :class="task.is_done ? 'bg-slate-50' : 'bg-white'"
      >
        <button
          type="button"
          class="flex size-5 shrink-0 items-center justify-center rounded-md border-2 transition"
          :class="
            task.is_done
              ? 'border-emerald-500 bg-emerald-500 text-white'
              : 'border-slate-300 hover:border-emerald-400'
          "
          :aria-pressed="task.is_done"
          :aria-label="task.is_done ? 'Označit jako nehotové' : 'Označit jako hotové'"
          @click="toggle(index)"
        >
          <CheckIcon v-if="task.is_done" class="size-3.5" stroke-width="3" />
        </button>
        <input
          v-model="task.title"
          type="text"
          class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
          :class="task.is_done ? 'text-slate-400 line-through' : 'text-slate-900'"
          :aria-label="`Úkol ${index + 1}`"
        />
        <span v-if="task.is_done && task.done_at" class="shrink-0 text-xs text-slate-400">
          {{ formatDone(task.done_at) }}
        </span>
        <button
          type="button"
          class="shrink-0 rounded-md p-1 text-slate-300 opacity-0 transition hover:bg-red-50 hover:text-red-600 focus:opacity-100 group-hover:opacity-100"
          aria-label="Smazat úkol"
          @click="remove(index)"
        >
          <TrashIcon class="size-4" />
        </button>
      </li>
    </ul>

    <div class="flex items-center gap-2">
      <input
        ref="draftInput"
        v-model="draft"
        type="text"
        :placeholder="placeholder"
        class="block w-full rounded-xl border-0 px-4 py-2.5 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
        @keydown.enter.prevent="addTask"
      />
      <button
        type="button"
        class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-40"
        :disabled="!draft.trim()"
        @click="addTask"
      >
        <PlusIcon class="size-4" />
        Přidat
      </button>
    </div>
  </div>
</template>
