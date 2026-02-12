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
</script>

<template>
  <li class="mb-4 ms-6">
    <div
      class="absolute -start-3 mt-3 flex h-6 w-6 items-center justify-center rounded-full border border-white bg-gray-200"
    >
      <CogIcon v-if="history.origin === 'system'" class="size-4 shrink-0 lg:size-5" />
      <UserIcon v-if="history.origin === 'user'" class="size-4 shrink-0 lg:size-5" />
      <AcademicCapIcon v-if="history.origin === 'mentor'" class="size-4 shrink-0 lg:size-5" />
      <QuestionMarkCircleIcon v-if="history.origin === 'other'" class="size-4 shrink-0 lg:size-5" />
    </div>
    <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-sm">
      <div class="px-4 py-5 sm:px-6">
        <div class="grid grid-cols-3">
          <div class="col-span-1">
            <time
              class="mb-1 text-xs font-normal leading-none text-gray-400 lg:text-sm dark:text-gray-500"
              >{{ new Date(history.created_at).toLocaleString() }}</time
            >
            <div class="flex items-end">
              <div class="hidden lg:block">
                <PropsBadge v-if="history.type === 'other'" color="zinc">
                  <QuestionMarkCircleIcon class="size-3 shrink-0" />
                </PropsBadge>
                <PropsBadge v-else-if="history.type === 'meeting'" color="orange">
                  <ComputerDesktopIcon class="size-3 shrink-0" />
                </PropsBadge>
                <PropsBadge v-else-if="history.type === 'call'" color="emerald">
                  <PhoneIcon class="size-3 shrink-0" />
                </PropsBadge>
                <PropsBadge v-else-if="history.type === 'email'" color="indigo">
                  <AtSymbolIcon class="size-3 shrink-0" />
                </PropsBadge>
                <PropsBadge v-else-if="history.type === 'activity'" color="sky">
                  <BoltIcon class="size-3 shrink-0" />
                </PropsBadge>
              </div>
              <h3 class="text-sm font-semibold text-grayDark lg:ml-4 lg:text-lg">
                {{ history.name }}
              </h3>
            </div>
          </div>
          <div class="col-span-1 flex items-end justify-evenly text-center">
            <!-- <PropsBadge
							v-if="history.phase"
							:color="history.phase.color"
						>
							{{ history.phase.name }}
						</PropsBadge> -->
            <PropsBadge v-if="history.activity" :color="history.activity.color">
              {{ history.activity.name }}
            </PropsBadge>
          </div>
          <div class="col-span-1 flex items-end justify-end text-end">
            <PencilIcon
              v-if="history.origin === 'user'"
              class="size-4 shrink-0 cursor-pointer text-primaryCustom hover:text-primaryLight lg:size-5"
              @click="emit('edit-history', history)"
            />
            <TrashIcon
              v-if="history.origin === 'user'"
              class="ml-4 size-4 shrink-0 cursor-pointer text-danger hover:text-dangerLight lg:size-5"
              @click="openDeleteDialog"
            />
          </div>
        </div>
      </div>
      <div class="divide-x-2 divide-gray-200 px-4 py-5 sm:p-6">
        <p class="text-sm font-normal text-gray-500 lg:text-base dark:text-gray-400">
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
