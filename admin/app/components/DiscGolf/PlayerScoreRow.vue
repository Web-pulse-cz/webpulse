<script setup lang="ts">
import { computed } from 'vue';
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  playerName: {
    type: String,
    required: true,
  },
  handicap: {
    type: Number,
    required: false,
    default: 0,
  },
  /** Throws on the currently displayed hole (null = not entered yet). */
  throws: {
    type: [Number, null] as unknown as () => number | null,
    required: false,
    default: null,
  },
  /** Par of the currently displayed hole, for the per-hole +/- badge. */
  holePar: {
    type: Number,
    required: false,
    default: 0,
  },
  /** Running total across all holes so far. */
  liveTotal: {
    type: Number,
    required: false,
    default: 0,
  },
  liveNet: {
    type: Number,
    required: false,
    default: 0,
  },
  liveRelative: {
    type: Number,
    required: false,
    default: 0,
  },
});

const emit = defineEmits<{ (e: 'update', throws: number | null): void }>();

const { formatRelative, relativeColor } = useDiscGolf();

const holeRelative = computed(() => {
  if (props.throws === null || props.throws === undefined) return null;
  return props.throws - props.holePar;
});

function setThrows(value: string) {
  const trimmed = value.trim();
  if (trimmed === '') {
    emit('update', null);
    return;
  }
  const num = Number(trimmed);
  emit('update', isNaN(num) ? null : Math.max(0, num));
}

function step(delta: number) {
  // No value yet: minus seeds the hole par, plus seeds par + 1.
  if (props.throws === null || props.throws === undefined) {
    const par = props.holePar ?? 0;
    emit('update', Math.max(0, delta < 0 ? par : par + 1));
    return;
  }
  emit('update', Math.max(0, props.throws + delta));
}
</script>

<template>
  <div
    class="flex flex-col gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between"
  >
    <div class="min-w-0 flex-1">
      <div class="font-semibold text-slate-800">{{ playerName }}</div>
      <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500">
        <span>Handicap: {{ handicap }}</span>
        <span>·</span>
        <span
          >Celkem hodů: <strong class="text-slate-700">{{ liveTotal }}</strong></span
        >
        <span>·</span>
        <span
          >Čisté: <strong class="text-slate-700">{{ liveNet }}</strong></span
        >
        <span>·</span>
        <span>
          Aktuálně:
          <strong :class="relativeColor(liveRelative)">{{ formatRelative(liveRelative) }}</strong>
        </span>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <div class="flex items-center gap-1">
        <button
          type="button"
          class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200"
          @click="step(-1)"
        >
          <MinusIcon class="size-4" />
        </button>
        <input
          type="number"
          min="0"
          inputmode="numeric"
          class="w-16 rounded-xl border-0 px-3 py-2.5 text-center text-sm font-semibold tabular-nums text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          :value="throws ?? ''"
          placeholder="—"
          @input="setThrows(($event.target as HTMLInputElement).value)"
        />
        <button
          type="button"
          class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200"
          @click="step(1)"
        >
          <PlusIcon class="size-4" />
        </button>
      </div>
      <span
        v-if="holeRelative !== null"
        class="w-10 text-center text-xs font-bold tabular-nums"
        :class="relativeColor(holeRelative)"
      >
        {{ formatRelative(holeRelative) }}
      </span>
    </div>
  </div>
</template>
