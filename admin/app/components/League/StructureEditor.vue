<script setup lang="ts">
import draggable from 'vuedraggable';
import { Bars3Icon, PlusIcon, TrashIcon, FolderIcon } from '@heroicons/vue/24/outline';
import type { LeagueActivityForm, LeagueSectionForm } from '~/composables/useLeague';

const sections = defineModel<LeagueSectionForm[]>('sections', { required: true });
const activities = defineModel<LeagueActivityForm[]>('activities', { required: true });

const props = defineProps({
  scoringType: {
    type: String,
    default: 'points',
  },
});

const { scoringLabel, valueUnit, formatScore } = useLeague();

let keyCounter = 0;
function newKey(): string {
  keyCounter++;
  return `new-${Date.now()}-${keyCounter}`;
}

function addSection() {
  sections.value.push({ _key: newKey(), id: null, name: '', activities: [] });
}

function removeSection(index: number) {
  // Activities of a removed section become unsectioned
  const [removed] = sections.value.splice(index, 1);
  if (removed?.activities.length) activities.value.push(...removed.activities);
}

function addActivity(list: LeagueActivityForm[]) {
  list.push({
    _key: newKey(),
    id: null,
    name: '',
    description: '',
    value: props.scoringType === 'minutes' ? 60 : 1,
    max_per_day: 1,
    counts_to_league: true,
  });
}

function removeActivity(list: LeagueActivityForm[], item: LeagueActivityForm) {
  if (
    item.entries_count &&
    !window.confirm(
      `Aktivita „${item.name}“ má ${item.entries_count} záznamů. Po uložení budou záznamy smazány a odečtou se z žebříčku. Pokračovat?`,
    )
  ) {
    return;
  }
  list.splice(list.indexOf(item), 1);
}

function listOf(sectionKey: string | null): LeagueActivityForm[] {
  if (sectionKey === null) return activities.value;
  return sections.value.find((s) => s._key === sectionKey)?.activities ?? activities.value;
}

function moveActivity(from: LeagueActivityForm[], item: LeagueActivityForm, sectionKey: string) {
  const target = listOf(sectionKey === '' ? null : sectionKey);
  if (target === from) return;
  from.splice(from.indexOf(item), 1);
  target.push(item);
}

function setMaxPerDay(item: LeagueActivityForm, value: string) {
  const num = parseInt(value, 10);
  item.max_per_day = isNaN(num) || num < 1 ? null : num;
}
</script>

<template>
  <div class="space-y-6">
    <draggable
      v-model="sections"
      item-key="_key"
      handle=".section-drag-handle"
      class="space-y-4"
      :animation="150"
    >
      <template #item="{ element: section, index }">
        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
          <div class="mb-3 flex items-center gap-3">
            <button
              type="button"
              class="section-drag-handle cursor-grab text-slate-400 hover:text-slate-600"
              title="Přesunout sekci"
            >
              <Bars3Icon class="size-5" />
            </button>
            <FolderIcon class="size-5 text-indigo-500" />
            <input
              v-model="section.name"
              type="text"
              placeholder="Název sekce"
              class="block w-full rounded-xl border-0 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              :class="{ 'ring-red-400': !section.name.trim() }"
            />
            <button
              type="button"
              class="text-slate-400 transition-colors hover:text-red-500"
              title="Smazat sekci (aktivity zůstanou bez sekce)"
              @click="removeSection(index)"
            >
              <TrashIcon class="size-5" />
            </button>
          </div>

          <LeagueStructureActivityList
            :list="section.activities"
            :section-key="section._key"
            :sections="sections"
            :scoring-type="scoringType"
            @remove="(item) => removeActivity(section.activities, item)"
            @move="(item, key) => moveActivity(section.activities, item, key)"
            @set-max="setMaxPerDay"
          />

          <button
            type="button"
            class="mt-3 flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-500"
            @click="addActivity(section.activities)"
          >
            <PlusIcon class="size-4" />
            Přidat aktivitu do sekce
          </button>
        </div>
      </template>
    </draggable>

    <div class="rounded-2xl border-2 border-dashed border-slate-200 p-4">
      <div class="mb-3 text-sm font-semibold text-slate-700">Aktivity bez sekce</div>

      <LeagueStructureActivityList
        :list="activities"
        :section-key="null"
        :sections="sections"
        :scoring-type="scoringType"
        @remove="(item) => removeActivity(activities, item)"
        @move="(item, key) => moveActivity(activities, item, key)"
        @set-max="setMaxPerDay"
      />

      <button
        type="button"
        class="mt-3 flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-500"
        @click="addActivity(activities)"
      >
        <PlusIcon class="size-4" />
        Přidat aktivitu
      </button>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <BaseButton variant="secondary" size="md" type="button" @click="addSection">
        <PlusIcon class="mr-1.5 size-4" />
        Přidat sekci
      </BaseButton>
      <span class="text-xs text-slate-500">
        Bodování ligy: <strong>{{ scoringLabel(scoringType) }}</strong>
        <template v-if="scoringType === 'minutes'">
          — hodnota aktivity se zadává v sekundách ({{ formatScore(90, 'minutes') }} = 90
          {{ valueUnit('minutes') }})
        </template>
      </span>
    </div>
  </div>
</template>
