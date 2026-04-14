<script setup lang="ts">
import { ref, inject } from 'vue';
import { PlusIcon, TrashIcon, CheckCircleIcon, SwatchIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const pageTitle = ref('Boardy úkolů');
const loading = ref(false);

const breadcrumbs = ref([
  { name: 'Úkoly', link: '/ukoly', current: false },
  { name: pageTitle.value, link: '/ukoly/boardy', current: true },
]);

const boards = ref([]);
const showForm = ref(false);
const editingBoard = ref({
  id: null,
  name: '',
  color: '#6366f1',
  is_completed: false,
  position: 0,
  sites: [] as number[],
});

async function loadBoards() {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/task-board', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      boards.value = r;
    })
    .finally(() => {
      loading.value = false;
    });
}

function openNew() {
  editingBoard.value = {
    id: null,
    name: '',
    color: '#6366f1',
    is_completed: false,
    position: 0,
    sites: [],
  };
  showForm.value = true;
}

function openEdit(board: any) {
  editingBoard.value = {
    id: board.id,
    name: board.name,
    color: board.color,
    is_completed: board.is_completed,
    position: board.position,
    sites: board.sites || [],
  };
  showForm.value = true;
}

async function saveBoard() {
  if (!editingBoard.value.name) return;
  const client = useSanctumClient();
  const url = editingBoard.value.id
    ? '/api/admin/task-board/' + editingBoard.value.id
    : '/api/admin/task-board';
  await client(url, {
    method: 'POST',
    body: JSON.stringify(editingBoard.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Board uložen.', severity: 'success' });
      showForm.value = false;
      loadBoards();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit board.', severity: 'error' });
    });
}

async function deleteBoard(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/task-board/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadBoards();
  });
}

watch(selectedSiteHash, () => loadBoards());
useHead({ title: pageTitle.value });
onMounted(() => {
  loadBoards();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'add', text: 'Přidat board' }]"
      slug="project_tasks"
    />

    <!-- Board list -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="board in boards"
        :key="board.id"
        class="cursor-pointer rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
        @click="openEdit(board)"
      >
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="size-4 rounded-full" :style="{ backgroundColor: board.color }"></div>
            <span class="text-sm font-bold text-slate-900">{{ board.name }}</span>
          </div>
          <div class="flex items-center gap-1">
            <span
              v-if="board.is_completed"
              class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
            >
              <CheckCircleIcon class="mr-0.5 inline size-3" /> Dokončený
            </span>
            <button
              type="button"
              class="rounded-lg p-1 text-slate-300 transition hover:bg-red-50 hover:text-red-500"
              @click.stop="deleteBoard(board.id)"
            >
              <TrashIcon class="size-4" />
            </button>
          </div>
        </div>
        <div class="text-xs text-slate-400">
          {{ board.tasks_count || 0 }} úkolů &middot; Pořadí: {{ board.position }}
        </div>
      </div>

      <!-- Add card -->
      <div
        class="flex cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 p-8 transition hover:border-indigo-300 hover:bg-indigo-50/30"
        @click="openNew"
      >
        <div class="text-center">
          <PlusIcon class="mx-auto size-8 text-slate-300" />
          <p class="mt-2 text-sm font-medium text-slate-400">Nový board</p>
        </div>
      </div>
    </div>

    <!-- Edit/Create dialog -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showForm"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showForm = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">
              {{ editingBoard.id ? 'Upravit board' : 'Nový board' }}
            </h3>
            <div class="space-y-4">
              <BaseFormInput
                v-model="editingBoard.name"
                label="Název"
                name="board_name"
                rules="required"
              />
              <div class="grid grid-cols-2 gap-4">
                <BaseFormColorPicker
                  v-model="editingBoard.color"
                  label="Barva"
                  name="board_color"
                />
                <BaseFormInput
                  v-model="editingBoard.position"
                  label="Pořadí"
                  type="number"
                  name="board_position"
                />
              </div>
              <BaseFormCheckbox
                v-model="editingBoard.is_completed"
                label="Board označuje úkoly jako dokončené"
                name="board_done"
              />
              <div class="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  @click="showForm = false"
                >
                  Zrušit
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                  @click="saveBoard"
                >
                  Uložit
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
