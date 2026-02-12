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
  ClockIcon, // Přidáno pro hezčí zobrazení času
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

// Pomocná funkce pro barvy pozadí ikon (volitelné, pro hezčí efekt)
const getIconBgColor = (type: string) => {
  switch (type) {
    case 'meeting':
      return 'bg-orange-100 text-orange-600';
    case 'call':
      return 'bg-emerald-100 text-emerald-600';
    case 'email':
      return 'bg-indigo-100 text-indigo-600';
    case 'activity':
      return 'bg-sky-100 text-sky-600';
    default:
      return 'bg-gray-100 text-gray-500';
  }
};
</script>

<template>
  <li class="relative mb-6 ms-6">
    <div
      class="absolute -start-9 mt-4 flex h-6 w-6 items-center justify-center rounded-full border border-white bg-gray-200 ring-4 ring-white"
    >
      <CogIcon v-if="history.origin === 'system'" class="size-3.5 text-gray-500" />
      <UserIcon v-if="history.origin === 'user'" class="size-3.5 text-gray-500" />
      <AcademicCapIcon v-if="history.origin === 'mentor'" class="size-3.5 text-gray-500" />
      <QuestionMarkCircleIcon v-if="history.origin === 'other'" class="size-3.5 text-gray-500" />
    </div>

    <div
      class="relative flex flex-col rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-shadow hover:shadow-md"
    >
      <div class="mb-3 flex items-start justify-between gap-4">
        <div class="flex gap-3">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="getIconBgColor(history.type)"
          >
            <QuestionMarkCircleIcon v-if="history.type === 'other'" class="size-5" />
            <ComputerDesktopIcon v-else-if="history.type === 'meeting'" class="size-5" />
            <PhoneIcon v-else-if="history.type === 'call'" class="size-5" />
            <AtSymbolIcon v-else-if="history.type === 'email'" class="size-5" />
            <BoltIcon v-else-if="history.type === 'activity'" class="size-5" />
            <QuestionMarkCircleIcon v-else class="size-5" />
          </div>

          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-semibold text-gray-900 lg:text-base">
                {{ history.name }}
              </h3>
              <PropsBadge
                v-if="history.activity"
                :color="history.activity.color"
                class="!px-2 !py-0.5 text-[10px]"
              >
                {{ history.activity.name }}
              </PropsBadge>
            </div>

            <div class="mt-1 flex items-center gap-2 text-xs text-gray-400 lg:text-sm">
              <ClockIcon class="size-3.5" />
              <time>{{ new Date(history.created_at).toLocaleDateString() }}</time>
            </div>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <PencilIcon
            v-if="history.origin === 'user'"
            class="size-4 cursor-pointer text-gray-400 transition-colors hover:bg-gray-50 hover:text-primaryCustom lg:size-5"
            @click="emit('edit-history', history)"
          />
          <TrashIcon
            v-if="history.origin === 'user'"
            class="size-4 cursor-pointer text-gray-400 transition-colors hover:bg-gray-50 hover:text-danger lg:size-5"
            @click="openDeleteDialog"
          />
        </div>
      </div>

      <div class="pl-[3.25rem]">
        <p class="text-sm font-normal leading-relaxed text-gray-600 lg:text-base">
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
