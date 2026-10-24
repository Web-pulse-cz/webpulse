<script setup lang="ts">
import draggable from 'vuedraggable';
import { Bars3Icon, TrashIcon } from '@heroicons/vue/24/outline';
import type { LeagueActivityForm, LeagueSectionForm } from '~/composables/useLeague';

const props = defineProps<{
  list: LeagueActivityForm[];
  sectionKey: string | null;
  sections: LeagueSectionForm[];
  scoringType: string;
}>();

const emit = defineEmits<{
  remove: [item: LeagueActivityForm];
  move: [item: LeagueActivityForm, sectionKey: string];
  'set-max': [item: LeagueActivityForm, value: string];
}>();

const { scoringLabel, valueUnit, formatScore } = useLeague();

// Mutating the passed array in place keeps the parent's model in sync and limits drag to this list
function onReorder(value: LeagueActivityForm[]) {
  props.list.splice(0, props.list.length, ...value);
}

const inputClass =
  'block w-full rounded-xl border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500';
</script>

<template>
  <div>
    <draggable
      :model-value="list"
      item-key="_key"
      handle=".activity-drag-handle"
      class="space-y-2"
      :animation="150"
      @update:model-value="onReorder"
    >
      <template #item="{ element: activity }">
        <div class="space-y-2 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
          <div class="flex flex-wrap items-end gap-x-3 gap-y-2 md:flex-nowrap">
            <button
              type="button"
              class="activity-drag-handle flex h-9 cursor-grab items-center text-slate-400 hover:text-slate-600"
              title="Přesunout aktivitu"
            >
              <Bars3Icon class="size-5" />
            </button>

            <div class="min-w-0 flex-1 basis-[calc(100%-2.75rem)] md:basis-auto">
              <label class="mb-0.5 block text-[11px] font-medium text-slate-500">Název</label>
              <input
                v-model="activity.name"
                type="text"
                placeholder="Název aktivity"
                :class="[inputClass, { 'ring-red-400': !activity.name.trim() }]"
              />
            </div>

            <div class="w-[calc(50%-0.375rem)] md:w-32">
              <label class="mb-0.5 block truncate text-[11px] font-medium text-slate-500">
                {{ scoringLabel(scoringType) }} ({{ valueUnit(scoringType) }})
                <template v-if="scoringType === 'minutes'">
                  = {{ formatScore(activity.value, 'minutes') }}
                </template>
              </label>
              <input v-model.number="activity.value" type="number" :class="inputClass" />
            </div>

            <div class="w-[calc(50%-0.375rem)] md:w-32">
              <label class="mb-0.5 block text-[11px] font-medium text-slate-500">Max. za den</label>
              <input
                :value="activity.max_per_day ?? ''"
                type="number"
                min="1"
                placeholder="Neomezeně"
                :class="inputClass"
                @input="emit('set-max', activity, ($event.target as HTMLInputElement).value)"
              />
            </div>

            <div class="w-full md:w-44">
              <label class="mb-0.5 block text-[11px] font-medium text-slate-500">Sekce</label>
              <select
                :value="sectionKey ?? ''"
                :class="inputClass"
                @change="emit('move', activity, ($event.target as HTMLSelectElement).value)"
              >
                <option value="">Bez sekce</option>
                <option v-for="section in sections" :key="section._key" :value="section._key">
                  {{ section.name || 'Nová sekce' }}
                </option>
              </select>
            </div>

            <div class="shrink-0" title="Započítávat do žebříčku ligy">
              <label class="mb-0.5 block text-[11px] font-medium text-slate-500">Do ligy</label>
              <div class="flex h-9 items-center">
                <BaseFormSwitch v-model:enabled="activity.counts_to_league" size="sm" />
              </div>
            </div>

            <button
              type="button"
              class="ml-auto flex h-9 items-center text-slate-400 transition-colors hover:text-red-500 md:ml-0"
              title="Smazat aktivitu"
              @click="emit('remove', activity)"
            >
              <TrashIcon class="size-5" />
            </button>
          </div>

          <div class="md:pl-8">
            <input
              v-model="activity.description"
              type="text"
              maxlength="1000"
              placeholder="Popis aktivity (nepovinné)"
              :class="inputClass"
            />
          </div>
        </div>
      </template>
    </draggable>

    <div v-if="!list.length" class="py-2 text-center text-xs text-slate-400">Žádné aktivity</div>
  </div>
</template>
