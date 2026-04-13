<script setup lang="ts">
import { ref } from 'vue';
import {
  CogIcon,
  UserIcon,
  QuestionMarkCircleIcon,
  AcademicCapIcon,
  PencilIcon,
  TrashIcon,
  PhoneIcon,
  ComputerDesktopIcon,
  AtSymbolIcon,
  BoltIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  history: {
    type: Object,
    required: true,
    default: {} as Record<string, any>,
  },
});
const emit = defineEmits(['edit-history', 'delete-item']);

const deleteDialog = ref({
  show: false,
  item: {},
});

function openDeleteDialog() {
  deleteDialog.value.show = true;
  deleteDialog.value.item = props.history;
}

// Slazeno s naším novým design systémem (amber místo orange, slate místo gray)
const getIconBgColor = (type: string) => {
  switch (type) {
    case 'meeting':
      return 'bg-amber-100 text-amber-600';
    case 'call':
      return 'bg-emerald-100 text-emerald-600';
    case 'email':
      return 'bg-indigo-100 text-indigo-600';
    case 'activity':
      return 'bg-sky-100 text-sky-600';
    default:
      return 'bg-slate-100 text-slate-500';
  }
};
</script>

<template>
  <li class="relative mb-8 ms-8">
    <div
      class="absolute -start-11 mt-4 flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-50 shadow-sm ring-4 ring-white"
    >
      <CogIcon v-if="history.origin === 'system'" class="size-4 text-slate-400" />
      <UserIcon v-if="history.origin === 'user'" class="size-4 text-slate-400" />
      <AcademicCapIcon v-if="history.origin === 'mentor'" class="size-4 text-slate-400" />
      <QuestionMarkCircleIcon v-if="history.origin === 'other'" class="size-4 text-slate-400" />
    </div>

    <div
      class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-md"
    >
      <div class="mb-4 flex items-start justify-between gap-4">
        <div class="flex gap-4">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105"
            :class="getIconBgColor(history.type)"
          >
            <ComputerDesktopIcon v-if="history.type === 'meeting'" class="size-6" />
            <PhoneIcon v-else-if="history.type === 'call'" class="size-6" />
            <AtSymbolIcon v-else-if="history.type === 'email'" class="size-6" />
            <BoltIcon v-else-if="history.type === 'activity'" class="size-6" />
            <QuestionMarkCircleIcon v-else class="size-6" />
          </div>

          <div class="flex flex-col justify-center">
            <div class="flex items-center gap-3">
              <h3 class="text-sm font-bold text-slate-900 lg:text-base">
                {{ history.name }}
              </h3>
              <PropsBadge
                v-if="history.activity"
                :color="history.activity.color"
                class="!px-2.5 !py-0.5 text-[11px] font-bold uppercase tracking-wide"
              >
                {{ history.activity.name }}
              </PropsBadge>
            </div>

            <div
              class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-slate-500 lg:text-sm"
            >
              <ClockIcon class="size-4" />
              <time>{{ new Date(history.created_at).toLocaleDateString() }}</time>
            </div>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-1">
          <button
            v-if="history.origin === 'user'"
            type="button"
            class="rounded-full p-2 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            title="Upravit"
            @click="emit('edit-history', history)"
          >
            <PencilIcon class="size-4 lg:size-5" />
          </button>

          <button
            v-if="history.origin === 'user'"
            type="button"
            class="rounded-full p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500"
            title="Smazat"
            @click="openDeleteDialog"
          >
            <TrashIcon class="size-4 lg:size-5" />
          </button>
        </div>
      </div>

      <div class="pl-[4rem]">
        <p class="text-sm leading-relaxed text-slate-600 lg:text-base">
          {{ history.description }}
        </p>
      </div>
    </div>

    <BaseDialogDelete
      v-model:show="deleteDialog.show"
      v-model:item="deleteDialog.item"
      @delete-item="
        emit('delete-item', history.id);
        deleteDialog.show = false;
      "
    />
  </li>
</template>
