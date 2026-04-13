<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import { DocumentIcon, BanknotesIcon, TrashIcon, XMarkIcon, FolderIcon } from '@heroicons/vue/24/outline';

import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();
const taxRateStore = useTaxRateStore();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const tabs = ref([
  { name: 'Přehled', link: '#prehled', current: false },
  { name: 'Úkoly', link: '#ukoly', current: false },
  { name: 'Sledování času', link: '#cas', current: false },
  { name: 'Náklady', link: '#naklady', current: false },
  { name: 'Poznámky', link: '#poznamky', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový projekt' : 'Detail projektu');
const breadcrumbs = ref([
  { name: 'Projekty', link: '/projekty', current: false },
  { name: pageTitle.value, link: '/projekty/pridat', current: true },
]);

const statuses = ref([]);
const tags = ref([]);
const clients = ref([]);
const users = ref([]);

const item = ref({
  id: null,
  name: '',
  prefix: '',
  description: '',
  note: '',
  image: '',
  client_id: null,
  status_id: null,
  currency_id: null,
  tax_rate_id: null,
  start_date: '',
  deadline_date: '',
  end_date: '',
  hourly_rate: 0,
  expected_hours: 0,
  total_tracked_hours: 0,
  expected_revenue: 0,
  total_revenue: 0,
  total_costs: 0,
  profit: 0,
  is_archived: false,
  tags: [],
  task_categories: [],
  task_boards: [],
  milestones: [],
  tasks: [],
  time_entries: [],
  costs: [],
  notes: [],
  sites: [] as number[],
});

const boards = ref([]);
const categories = ref([]);
const showTaskDetail = ref(false);
const selectedTask = ref(null as any);
const timerInterval = ref(null as any);
const timerDisplay = ref('00:00:00');
const runningEntry = ref(null as any);

// ─── Loaders ───────────────────────────────────────────────

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/project/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      item.value = r;
      item.value.sites = r.sites?.map?.((s: any) => s.id) || r.sites || [];
      categories.value = r.task_categories || [];
      pageTitle.value = item.value.name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/projekty/' + route.params.id,
        current: true,
      };
      checkRunningTimer();
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst projekt.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadBoards() {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task-board', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      boards.value = r;
    })
    .catch(() => {});
}

async function loadStatuses() {
  const client = useSanctumClient();
  await client('/api/admin/project/status', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      statuses.value = r.map((s: any) => ({ value: s.id, name: s.name }));
    })
    .catch(() => {});
}
async function loadTags() {
  const client = useSanctumClient();
  await client('/api/admin/project/tag', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      tags.value = r;
    })
    .catch(() => {});
}
async function loadClients() {
  const client = useSanctumClient();
  await client('/api/admin/client', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      const d = r?.data || r;
      clients.value = d.map((c: any) => ({ value: c.id, name: c.name }));
    })
    .catch(() => {});
}
async function loadUsers() {
  const client = useSanctumClient();
  await client('/api/admin/user', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Site-Hash': selectedSiteHash.value },
  })
    .then((r) => {
      const d = r?.data || r;
      users.value = d.map((u: any) => ({ value: u.id, name: u.name || u.email }));
    })
    .catch(() => {});
}

// ─── Save Project ──────────────────────────────────────────

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;
  const payload = { ...item.value, tags: item.value.tags?.map((t: any) => t.id || t) || [] };
  await client(
    route.params.id === 'pridat' ? '/api/admin/project' : '/api/admin/project/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(payload),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Projekt vytvořen.' : 'Projekt upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') router.push('/projekty/' + r.id);
      else if (redirect) router.push('/projekty');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit projekt.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

// ─── Categories & Boards ───────────────────────────────────

const newCategory = ref({ name: '', color: '#6366f1' });
const newBoard = ref({ name: '', color: '#6366f1', is_completed: false });

async function saveCategory() {
  if (!newCategory.value.name) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task-category', {
    method: 'POST',
    body: JSON.stringify(newCategory.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newCategory.value = { name: '', color: '#6366f1' };
    loadItem();
    loadBoards();
  });
}
async function deleteCategory(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task-category/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItem();
    loadBoards();
  });
}
async function saveBoard() {
  if (!newBoard.value.name) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task-board', {
    method: 'POST',
    body: JSON.stringify(newBoard.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newBoard.value = { name: '', color: '#6366f1', is_completed: false };
    loadBoards();
  });
}
async function deleteBoard(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task-board/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadBoards();
  });
}

// ─── Tasks ─────────────────────────────────────────────────

const newTask = ref({ name: '', board_id: null, category_id: null, priority: 'normal' });

async function createTask() {
  if (!newTask.value.name) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task', {
    method: 'POST',
    body: JSON.stringify(newTask.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newTask.value = { name: '', board_id: null, category_id: null, priority: 'normal' };
    loadBoards();
    loadItem();
  });
}

async function moveTask(taskId: number, boardId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task/' + taskId + '/move', {
    method: 'POST',
    body: JSON.stringify({ board_id: boardId }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadBoards();
  });
}

async function openTaskDetail(taskId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task/' + taskId, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then((r) => {
    selectedTask.value = r;
    showTaskDetail.value = true;
  });
}

async function saveTaskDetail() {
  if (!selectedTask.value) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task/' + selectedTask.value.id, {
    method: 'POST',
    body: JSON.stringify({
      ...selectedTask.value,
      assignees: selectedTask.value.assignees?.map((a: any) => a.id || a) || [],
    }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    $toast.show({ summary: 'Hotovo', detail: 'Úkol uložen.', severity: 'success' });
    loadBoards();
    loadItem();
  });
}

async function deleteTask(taskId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/task/' + taskId, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    showTaskDetail.value = false;
    selectedTask.value = null;
    loadBoards();
    loadItem();
  });
}

// ─── Comments ──────────────────────────────────────────────

const newComment = ref('');
async function addComment() {
  if (!newComment.value || !selectedTask.value) return;
  const client = useSanctumClient();
  await client(
    '/api/admin/project/' + route.params.id + '/task/' + selectedTask.value.id + '/comment',
    {
      method: 'POST',
      body: JSON.stringify({ content: newComment.value }),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  ).then(() => {
    newComment.value = '';
    openTaskDetail(selectedTask.value.id);
  });
}
async function deleteComment(commentId: number) {
  if (!selectedTask.value) return;
  const client = useSanctumClient();
  await client(
    '/api/admin/project/' +
      route.params.id +
      '/task/' +
      selectedTask.value.id +
      '/comment/' +
      commentId,
    {
      method: 'DELETE',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  ).then(() => {
    openTaskDetail(selectedTask.value.id);
  });
}

// ─── Timer & Time Entries ──────────────────────────────────

const newTimeEntry = ref({ description: '', hours: 0, date: '', task_id: null });

async function saveTimeEntry() {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/time-entry', {
    method: 'POST',
    body: JSON.stringify(newTimeEntry.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newTimeEntry.value = { description: '', hours: 0, date: '', task_id: null };
    loadItem();
  });
}
async function deleteTimeEntry(entryId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/time-entry/' + entryId, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItem();
  });
}
async function startTimer(taskId: number | null = null) {
  const client = useSanctumClient();
  await client('/api/admin/time-entry/timer/start', {
    method: 'POST',
    body: JSON.stringify({ project_id: route.params.id, task_id: taskId }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItem();
  });
}
async function stopTimer(entryId: number) {
  const client = useSanctumClient();
  await client('/api/admin/time-entry/timer/' + entryId + '/stop', {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    clearInterval(timerInterval.value);
    runningEntry.value = null;
    timerDisplay.value = '00:00:00';
    loadItem();
  });
}
function checkRunningTimer() {
  const running = item.value.time_entries?.find((e: any) => e.is_running);
  if (running) {
    runningEntry.value = running;
    startTimerDisplay(running.timer_started_at);
  }
}
function startTimerDisplay(startedAt: string) {
  clearInterval(timerInterval.value);
  timerInterval.value = setInterval(() => {
    const e = Math.floor((Date.now() - new Date(startedAt).getTime()) / 1000);
    timerDisplay.value = `${String(Math.floor(e / 3600)).padStart(2, '0')}:${String(Math.floor((e % 3600) / 60)).padStart(2, '0')}:${String(e % 60).padStart(2, '0')}`;
  }, 1000);
}

// ─── Costs & Notes ─────────────────────────────────────────

const newCost = ref({ name: '', amount: 0, category: 'other', date: '' });
const costCategoryOptions = ref([
  { value: 'software', name: 'Software' },
  { value: 'hardware', name: 'Hardware' },
  { value: 'subcontractor', name: 'Subdodavatel' },
  { value: 'marketing', name: 'Marketing' },
  { value: 'travel', name: 'Cestovné' },
  { value: 'other', name: 'Ostatní' },
]);
async function saveCost() {
  if (!newCost.value.name) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/cost', {
    method: 'POST',
    body: JSON.stringify(newCost.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newCost.value = { name: '', amount: 0, category: 'other', date: '' };
    loadItem();
  });
}
async function deleteCost(costId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/cost/' + costId, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItem();
  });
}
const newNote = ref({ content: '' });
async function saveNote() {
  if (!newNote.value.content) return;
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/note', {
    method: 'POST',
    body: JSON.stringify(newNote.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    newNote.value = { content: '' };
    loadItem();
  });
}
async function deleteNote(noteId: number) {
  const client = useSanctumClient();
  await client('/api/admin/project/' + route.params.id + '/note/' + noteId, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadItem();
  });
}

// ─── Options & Lifecycle ───────────────────────────────────

const priorityOptions = ref([
  { value: 'low', name: 'Nízká' },
  { value: 'normal', name: 'Normální' },
  { value: 'high', name: 'Vysoká' },
  { value: 'critical', name: 'Kritická' },
]);
const priorityColors: Record<string, string> = {
  critical: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  normal: 'bg-slate-100 text-slate-600',
  low: 'bg-blue-100 text-blue-600',
};

watchEffect(() => {
  const h = route.hash;
  if (h)
    tabs.value.forEach((t) => {
      t.current = t.link === h;
    });
  else {
    tabs.value[0].current = true;
    router.push(route.path + '#prehled');
  }
});
onBeforeUnmount(() => {
  clearInterval(timerInterval.value);
});
watch(selectedSiteHash, () => loadItem());

useHead({ title: pageTitle.value });
onMounted(() => {
  loadStatuses();
  loadTags();
  loadClients();
  loadUsers();
  if (route.params.id !== 'pridat') {
    loadItem();
    loadBoards();
  }
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      :modify-bottom="false"
      slug="projects"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <!-- ═══ Přehled ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#prehled')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <DocumentIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.name"
                  label="Název projektu"
                  type="text"
                  name="name"
                  rules="required|min:3"
                />
                <BaseFormInput
                  v-model="item.prefix"
                  label="Prefix (kód úkolů)"
                  type="text"
                  name="prefix"
                  placeholder="Např. WP"
                />
                <BaseFormTextarea
                  v-model="item.description"
                  label="Popis"
                  name="description"
                  class="col-span-full"
                  rows="3"
                />
                <BaseFormSelect
                  v-model="item.client_id"
                  label="Klient"
                  name="client_id"
                  :options="clients"
                />
                <BaseFormInput
                  v-model="item.start_date"
                  label="Zahájení"
                  type="date"
                  name="start_date"
                />
                <BaseFormInput
                  v-model="item.deadline_date"
                  label="Deadline"
                  type="date"
                  name="deadline_date"
                />
                <BaseFormInput
                  v-model="item.end_date"
                  label="Ukončení"
                  type="date"
                  name="end_date"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <BanknotesIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Finance</LayoutTitle>
              </div>
              <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <BaseFormSelect
                  v-model="item.currency_id"
                  label="Měna"
                  name="currency_id"
                  :options="currencyStore.currenciesOptions"
                />
                <BaseFormSelect
                  v-model="item.tax_rate_id"
                  label="DPH"
                  name="tax_rate_id"
                  :options="taxRateStore.taxRateOptions"
                />
                <BaseFormInput
                  v-model="item.hourly_rate"
                  label="Hod. sazba"
                  type="number"
                  name="hourly_rate"
                  :step="0.01"
                />
                <BaseFormInput
                  v-model="item.expected_hours"
                  label="Oček. hodiny"
                  type="number"
                  name="expected_hours"
                  :step="0.01"
                />
              </div>
              <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                  <div class="text-2xl font-bold">{{ item.total_tracked_hours }}</div>
                  <div class="text-xs text-slate-500">Odpracováno h</div>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4 text-center ring-1 ring-emerald-200">
                  <div class="text-2xl font-bold text-emerald-700">{{ item.total_revenue }}</div>
                  <div class="text-xs text-emerald-600">Příjmy</div>
                </div>
                <div class="rounded-xl bg-red-50 p-4 text-center ring-1 ring-red-200">
                  <div class="text-2xl font-bold text-red-700">{{ item.total_costs }}</div>
                  <div class="text-xs text-red-600">Náklady</div>
                </div>
                <div class="rounded-xl bg-indigo-50 p-4 text-center ring-1 ring-indigo-200">
                  <div class="text-2xl font-bold text-indigo-700">{{ item.profit }}</div>
                  <div class="text-xs text-indigo-600">Zisk</div>
                </div>
              </div>
            </LayoutContainer>
          </div>
          <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
            <LayoutContainer class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Stav</LayoutTitle
              >
              <div class="mt-4">
                <BaseFormSelect
                  v-model="item.status_id"
                  label=""
                  name="status_id"
                  :options="statuses"
                />
              </div>
              <div class="mt-4">
                <BaseFormCheckbox
                  v-model="item.is_archived"
                  label="Archivovaný"
                  name="is_archived"
                />
              </div>
            </LayoutContainer>
            <LayoutContainer v-if="tags.length > 0" class="!py-6">
              <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
                >Tagy</LayoutTitle
              >
              <div class="mt-3 flex flex-wrap gap-2">
                <label
                  v-for="tag in tags"
                  :key="tag.id"
                  class="cursor-pointer rounded-full px-3 py-1 text-xs font-medium transition"
                  :class="
                    item.tags?.some((t) => (t.id || t) === tag.id)
                      ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-300'
                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                  "
                >
                  <input
                    type="checkbox"
                    class="hidden"
                    :checked="item.tags?.some((t) => (t.id || t) === tag.id)"
                    @change="
                      item.tags?.some((t) => (t.id || t) === tag.id)
                        ? (item.tags = item.tags.filter((t) => (t.id || t) !== tag.id))
                        : item.tags.push(tag)
                    "
                  />{{ tag.name }}
                </label>
              </div>
            </LayoutContainer>
            <LayoutActionsDetailBlock
              v-model:sites="item.sites"
              :allow-image="false"
              :allow-is-active="false"
              :allow-translations="false"
            />
          </div>
        </div>
      </template>

      <!-- ═══ Úkoly (Kanban) ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#ukoly')">
        <div class="space-y-6">
          <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <LayoutContainer class="!py-4">
              <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Boardy</span>
              <div class="mb-3 mt-2 flex gap-2">
                <BaseFormInput
                  v-model="newBoard.name"
                  label=""
                  name="board_name"
                  placeholder="Nový board"
                  class="flex-1"
                />
                <BaseFormInput
                  v-model="newBoard.color"
                  label=""
                  type="color"
                  name="board_color"
                  class="w-12"
                />
                <BaseFormCheckbox
                  v-model="newBoard.is_completed"
                  label="Done"
                  name="board_done"
                  class="!mb-0 text-xs"
                />
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
                  @click="saveBoard"
                >
                  +
                </button>
              </div>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="b in boards"
                  :key="b.id"
                  class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ring-1 ring-slate-200"
                  :style="{ backgroundColor: b.color + '20', color: b.color }"
                  >{{ b.name }} <span v-if="b.is_completed">&#10003;</span
                  ><button
                    type="button"
                    class="ml-1 text-red-400 hover:text-red-600"
                    @click="deleteBoard(b.id)"
                  >
                    &times;
                  </button></span
                >
              </div>
            </LayoutContainer>
            <LayoutContainer class="!py-4">
              <span class="text-xs font-bold uppercase tracking-widest text-slate-400"
                >Kategorie</span
              >
              <div class="mb-3 mt-2 flex gap-2">
                <BaseFormInput
                  v-model="newCategory.name"
                  label=""
                  name="cat_name"
                  placeholder="Nová kategorie"
                  class="flex-1"
                />
                <BaseFormInput
                  v-model="newCategory.color"
                  label=""
                  type="color"
                  name="cat_color"
                  class="w-12"
                />
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
                  @click="saveCategory"
                >
                  +
                </button>
              </div>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="c in categories"
                  :key="c.id"
                  class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ring-1 ring-slate-200"
                  :style="{ backgroundColor: c.color + '20', color: c.color }"
                  >{{ c.name
                  }}<button
                    type="button"
                    class="ml-1 text-red-400 hover:text-red-600"
                    @click="deleteCategory(c.id)"
                  >
                    &times;
                  </button></span
                >
              </div>
            </LayoutContainer>
          </div>

          <LayoutContainer class="!py-4">
            <div class="flex gap-3">
              <BaseFormInput
                v-model="newTask.name"
                label=""
                name="new_task"
                placeholder="Nový úkol..."
                class="flex-1"
              />
              <BaseFormSelect
                v-model="newTask.board_id"
                label=""
                name="nt_board"
                :options="boards.map((b) => ({ value: b.id, name: b.name }))"
                class="w-40"
              />
              <BaseFormSelect
                v-model="newTask.category_id"
                label=""
                name="nt_cat"
                :options="categories.map((c) => ({ value: c.id, name: c.name }))"
                class="w-40"
              />
              <BaseFormSelect
                v-model="newTask.priority"
                label=""
                name="nt_prio"
                :options="priorityOptions"
                class="w-32"
              />
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                @click="createTask"
              >
                Přidat
              </button>
            </div>
          </LayoutContainer>

          <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 400px">
            <div
              v-for="board in boards"
              :key="board.id"
              class="flex w-72 shrink-0 flex-col rounded-2xl bg-slate-100/80 ring-1 ring-slate-200"
            >
              <div
                class="flex items-center justify-between rounded-t-2xl px-4 py-3"
                :style="{ borderBottom: '3px solid ' + board.color }"
              >
                <span class="text-sm font-bold text-slate-900">{{ board.name }}</span>
                <span
                  class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200"
                  >{{ board.tasks?.length || 0 }}</span
                >
              </div>
              <div class="flex-1 space-y-2 overflow-y-auto p-3">
                <div
                  v-for="task in board.tasks"
                  :key="task.id"
                  class="cursor-pointer rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-100 transition hover:shadow-md hover:ring-indigo-200"
                  @click="openTaskDetail(task.id)"
                >
                  <div class="mb-1 flex items-center justify-between">
                    <span class="font-mono text-[10px] font-bold text-indigo-500">{{
                      task.code
                    }}</span>
                    <span
                      v-if="task.priority && task.priority !== 'normal'"
                      class="rounded-full px-1.5 py-0.5 text-[9px] font-bold"
                      :class="priorityColors[task.priority]"
                      >{{ task.priority }}</span
                    >
                  </div>
                  <p class="text-sm font-medium leading-snug text-slate-900">{{ task.name }}</p>
                  <div class="mt-2 flex items-center justify-between">
                    <div
                      v-if="task.category"
                      class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                      :style="{
                        backgroundColor: task.category.color + '20',
                        color: task.category.color,
                      }"
                    >
                      {{ task.category.name }}
                    </div>
                    <div class="flex -space-x-1">
                      <div
                        v-for="a in (task.assignees || []).slice(0, 3)"
                        :key="a.id"
                        class="flex size-5 items-center justify-center rounded-full bg-slate-200 text-[8px] font-bold text-slate-600 ring-1 ring-white"
                      >
                        {{ a.name?.charAt(0) }}
                      </div>
                    </div>
                  </div>
                  <div class="mt-2 flex gap-1">
                    <button
                      v-for="tb in boards.filter((b) => b.id !== board.id)"
                      :key="tb.id"
                      type="button"
                      class="rounded px-1.5 py-0.5 text-[9px] font-medium text-slate-400 ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-700"
                      @click.stop="moveTask(task.id, tb.id)"
                    >
                      &rarr; {{ tb.name }}
                    </button>
                  </div>
                </div>
                <div v-if="!board.tasks?.length" class="py-8 text-center text-xs text-slate-400">
                  Žádné úkoly
                </div>
              </div>
            </div>
            <div
              v-if="!boards.length"
              class="flex w-full items-center justify-center text-sm text-slate-400"
            >
              Vytvořte první board.
            </div>
          </div>
        </div>
      </template>

      <!-- ═══ Sledování času ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#cas')">
        <div class="space-y-8">
          <LayoutContainer>
            <div class="flex items-center justify-between">
              <div>
                <LayoutTitle class="!mb-0">Timer</LayoutTitle>
                <p v-if="runningEntry" class="mt-1 text-sm text-slate-500">
                  Běží od {{ new Date(runningEntry.timer_started_at).toLocaleTimeString() }}
                </p>
              </div>
              <div class="flex items-center gap-4">
                <span
                  class="font-mono text-3xl font-bold"
                  :class="runningEntry ? 'text-indigo-600' : 'text-slate-300'"
                  >{{ timerDisplay }}</span
                >
                <button
                  v-if="!runningEntry"
                  type="button"
                  class="rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-500"
                  @click="startTimer()"
                >
                  Start
                </button>
                <button
                  v-else
                  type="button"
                  class="rounded-lg bg-red-600 px-6 py-3 text-sm font-medium text-white hover:bg-red-500"
                  @click="stopTimer(runningEntry.id)"
                >
                  Stop
                </button>
              </div>
            </div>
          </LayoutContainer>
          <LayoutContainer>
            <LayoutTitle>Ruční záznam</LayoutTitle>
            <div class="flex gap-3">
              <BaseFormInput
                v-model="newTimeEntry.description"
                label=""
                name="te_desc"
                placeholder="Popis práce"
                class="flex-1"
              />
              <BaseFormInput
                v-model="newTimeEntry.hours"
                label=""
                type="number"
                name="te_hours"
                :step="0.25"
                class="w-24"
              />
              <BaseFormInput
                v-model="newTimeEntry.date"
                label=""
                type="date"
                name="te_date"
                class="w-40"
              />
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                @click="saveTimeEntry"
              >
                Přidat
              </button>
            </div>
          </LayoutContainer>
          <LayoutContainer>
            <LayoutTitle>Záznamy</LayoutTitle>
            <div v-if="!item.time_entries?.length" class="py-8 text-center text-sm text-slate-400">
              Žádné záznamy.
            </div>
            <div v-else class="divide-y divide-slate-100">
              <div
                v-for="entry in item.time_entries"
                :key="entry.id"
                class="flex items-center justify-between py-3"
              >
                <div>
                  <span class="font-medium">{{ entry.hours }}h</span>
                  <span v-if="entry.task_code" class="ml-2 font-mono text-xs text-indigo-500">{{
                    entry.task_code
                  }}</span>
                  <span class="ml-2 text-sm text-slate-500">{{ entry.description || '—' }}</span>
                  <span class="ml-2 text-xs text-slate-400">{{ entry.date }}</span>
                  <span
                    v-if="entry.is_running"
                    class="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
                    >Běží</span
                  >
                </div>
                <button
                  type="button"
                  class="text-red-400 hover:text-red-600"
                  @click="deleteTimeEntry(entry.id)"
                >
                  <TrashIcon class="size-4" />
                </button>
              </div>
            </div>
          </LayoutContainer>
          <div class="text-right">
            <NuxtLink
              to="/sledovani-casu"
              class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
              >Globální sledování času &rarr;</NuxtLink
            >
          </div>
        </div>
      </template>

      <!-- ═══ Náklady ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#naklady')">
        <LayoutContainer>
          <LayoutTitle>Náklady projektu</LayoutTitle>
          <div class="mb-4 flex gap-3">
            <BaseFormInput
              v-model="newCost.name"
              label=""
              name="cost_name"
              placeholder="Název"
              class="flex-1"
            />
            <BaseFormInput
              v-model="newCost.amount"
              label=""
              type="number"
              name="cost_amount"
              :step="0.01"
              class="w-32"
            />
            <BaseFormSelect
              v-model="newCost.category"
              label=""
              name="cost_cat"
              :options="costCategoryOptions"
              class="w-40"
            />
            <BaseFormInput
              v-model="newCost.date"
              label=""
              type="date"
              name="cost_date"
              class="w-40"
            />
            <button
              type="button"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
              @click="saveCost"
            >
              Přidat
            </button>
          </div>
          <div v-if="!item.costs?.length" class="py-8 text-center text-sm text-slate-400">
            Žádné náklady.
          </div>
          <div v-else class="divide-y divide-slate-100">
            <div
              v-for="cost in item.costs"
              :key="cost.id"
              class="flex items-center justify-between py-3"
            >
              <div>
                <span class="font-medium">{{ cost.name }}</span
                ><span class="ml-2 text-sm font-bold text-red-600">{{ cost.amount }}</span
                ><span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{
                  cost.category
                }}</span
                ><span v-if="cost.date" class="ml-2 text-xs text-slate-400">{{ cost.date }}</span>
              </div>
              <button
                type="button"
                class="text-red-400 hover:text-red-600"
                @click="deleteCost(cost.id)"
              >
                <TrashIcon class="size-4" />
              </button>
            </div>
          </div>
        </LayoutContainer>
      </template>

      <!-- ═══ Poznámky ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#poznamky')">
        <LayoutContainer>
          <LayoutTitle>Poznámky</LayoutTitle>
          <div class="mb-4 flex gap-3">
            <BaseFormTextarea
              v-model="newNote.content"
              label=""
              name="note_content"
              placeholder="Napište poznámku..."
              rows="2"
              class="flex-1"
            />
            <button
              type="button"
              class="self-end rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
              @click="saveNote"
            >
              Přidat
            </button>
          </div>
          <div v-if="!item.notes?.length" class="py-8 text-center text-sm text-slate-400">
            Žádné poznámky.
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="note in item.notes"
              :key="note.id"
              class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="flex items-start justify-between">
                <div>
                  <p class="whitespace-pre-wrap text-sm text-slate-700">{{ note.content }}</p>
                  <p class="mt-2 text-xs text-slate-400">
                    {{ note.user_name }} &middot;
                    {{ note.created_at ? new Date(note.created_at).toLocaleString() : '' }}
                  </p>
                </div>
                <button
                  type="button"
                  class="text-red-400 hover:text-red-600"
                  @click="deleteNote(note.id)"
                >
                  <TrashIcon class="size-4" />
                </button>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>

    <!-- ═══ Task Detail Modal ═══ -->
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
          v-if="showTaskDetail && selectedTask"
          class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-8"
          @click.self="showTaskDetail = false"
        >
          <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
              <div>
                <span class="font-mono text-sm font-bold text-indigo-500">{{
                  selectedTask.code
                }}</span>
                <h2 class="text-lg font-bold text-slate-900">{{ selectedTask.name }}</h2>
              </div>
              <button
                type="button"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
                @click="showTaskDetail = false"
              >
                <XMarkIcon class="size-5" />
              </button>
            </div>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
              <div class="space-y-4 lg:col-span-2">
                <BaseFormInput v-model="selectedTask.name" label="Název" name="task_name" />
                <BaseFormTextarea
                  v-model="selectedTask.description"
                  label="Popis"
                  name="task_desc"
                  rows="4"
                />
                <div class="border-t border-slate-100 pt-4">
                  <h3 class="mb-3 text-sm font-bold text-slate-700">
                    Komentáře ({{ selectedTask.comments?.length || 0 }})
                  </h3>
                  <div class="mb-4 flex gap-2">
                    <BaseFormInput
                      v-model="newComment"
                      label=""
                      name="comment"
                      placeholder="Komentář..."
                      class="flex-1"
                    />
                    <button
                      type="button"
                      class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
                      @click="addComment"
                    >
                      Odeslat
                    </button>
                  </div>
                  <div class="max-h-60 space-y-3 overflow-y-auto">
                    <div
                      v-for="c in selectedTask.comments"
                      :key="c.id"
                      class="rounded-lg bg-slate-50 p-3"
                    >
                      <div class="flex items-start justify-between">
                        <p class="text-sm text-slate-700">{{ c.content }}</p>
                        <button
                          type="button"
                          class="text-red-300 hover:text-red-500"
                          @click="deleteComment(c.id)"
                        >
                          <TrashIcon class="size-3" />
                        </button>
                      </div>
                      <p class="mt-1 text-[10px] text-slate-400">
                        {{ c.user_name }} &middot;
                        {{ c.created_at ? new Date(c.created_at).toLocaleString() : '' }}
                      </p>
                    </div>
                  </div>
                </div>
                <div
                  v-if="selectedTask.time_entries?.length"
                  class="border-t border-slate-100 pt-4"
                >
                  <h3 class="mb-2 text-sm font-bold text-slate-700">
                    Čas ({{ selectedTask.total_tracked_hours }}h)
                  </h3>
                  <div class="space-y-1">
                    <div
                      v-for="te in selectedTask.time_entries"
                      :key="te.id"
                      class="flex items-center justify-between text-xs text-slate-600"
                    >
                      <span>{{ te.hours }}h — {{ te.description || '—' }} ({{ te.date }})</span
                      ><span>{{ te.user_name }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="space-y-4">
                <BaseFormSelect
                  v-model="selectedTask.board_id"
                  label="Board"
                  name="task_board"
                  :options="boards.map((b) => ({ value: b.id, name: b.name }))"
                />
                <BaseFormSelect
                  v-model="selectedTask.category_id"
                  label="Kategorie"
                  name="task_cat"
                  :options="categories.map((c) => ({ value: c.id, name: c.name }))"
                />
                <BaseFormSelect
                  v-model="selectedTask.priority"
                  label="Priorita"
                  name="task_prio"
                  :options="priorityOptions"
                />
                <BaseFormInput
                  v-model="selectedTask.estimated_hours"
                  label="Odhad hodin"
                  type="number"
                  name="task_est"
                  :step="0.5"
                />
                <BaseFormInput
                  v-model="selectedTask.due_date"
                  label="Termín"
                  type="date"
                  name="task_due"
                />
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-700">Přiřazení</label>
                  <div class="max-h-40 space-y-1 overflow-y-auto">
                    <label
                      v-for="user in users"
                      :key="user.value"
                      class="flex items-center gap-2 rounded-lg p-1 text-xs hover:bg-slate-50"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedTask.assignees?.some((a) => (a.id || a) === user.value)"
                        class="rounded text-indigo-600"
                        @change="
                          selectedTask.assignees?.some((a) => (a.id || a) === user.value)
                            ? (selectedTask.assignees = selectedTask.assignees.filter(
                                (a) => (a.id || a) !== user.value,
                              ))
                            : selectedTask.assignees.push({ id: user.value, name: user.name })
                        "
                      />{{ user.name }}
                    </label>
                  </div>
                </div>
                <div class="flex gap-2 border-t border-slate-100 pt-4">
                  <button
                    type="button"
                    class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                    @click="saveTaskDetail"
                  >
                    Uložit
                  </button>
                  <button
                    type="button"
                    class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-200"
                    @click="deleteTask(selectedTask.id)"
                  >
                    Smazat
                  </button>
                </div>
                <button
                  type="button"
                  class="w-full rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100"
                  @click="
                    startTimer(selectedTask.id);
                    showTaskDetail = false;
                  "
                >
                  Spustit timer
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
