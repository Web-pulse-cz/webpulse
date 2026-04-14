<script setup lang="ts">
import { ref, inject } from 'vue';
import { Form } from 'vee-validate';
import {
  DocumentIcon,
  DocumentTextIcon,
  BanknotesIcon,
  TrashIcon,
  XMarkIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  CheckCircleIcon,
  ChatBubbleLeftIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline';

import { useCurrencyStore } from '~/../stores/currencyStore';
import { useTaxRateStore } from '~/../stores/taxRateStore';

const { formatSeconds, formatNumber } = useFormat();

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
  { name: 'Náklady', link: '#naklady', current: false },
  { name: 'Soubory', link: '#soubory', current: false },
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
  total_tracked_seconds: 0,
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
const expandedBoards = ref<Record<number, boolean>>({});
const showDrawer = ref(false);
const selectedTask = ref(null as any);

// ─── Loaders ───────────────────────────────────────────────

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/project/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = r;
      item.value.sites = Array.isArray(r.sites)
        ? r.sites.map((s: any) => (typeof s === 'object' ? s.id : s))
        : [];
      projectFiles.value = r.files || [];
      pageTitle.value = item.value.name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/projekty/' + route.params.id,
        current: true,
      };
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
  await client('/api/admin/task-board', {
    method: 'GET',
    query: { with_tasks: true, project_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      boards.value = r;
      if (Object.keys(expandedBoards.value).length === 0) {
        r.forEach((b: any) => {
          expandedBoards.value[b.id] = true;
        });
      }
    })
    .catch(() => {});
}

function toggleBoard(boardId: number) {
  expandedBoards.value[boardId] = !expandedBoards.value[boardId];
}

async function loadStatuses() {
  const client = useSanctumClient();
  await client('/api/admin/project/status', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
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
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
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
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
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
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
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
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Site-Hash': selectedSiteHash.value,
      },
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

// ─── Tasks ─────────────────────────────────────────────────

const newTask = ref({ name: '', global_board_id: null, priority: 'normal' });

async function createTask() {
  if (!newTask.value.name) return;
  const client = useSanctumClient();
  await client('/api/admin/task', {
    method: 'POST',
    body: JSON.stringify({ ...newTask.value, project_id: route.params.id }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      newTask.value = { name: '', global_board_id: null, priority: 'normal' };
      loadBoards();
      loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se vytvořit úkol.', severity: 'error' });
    });
}

async function moveTask(taskId: number, boardId: number) {
  const client = useSanctumClient();
  await client('/api/admin/task/' + taskId + '/move', {
    method: 'POST',
    body: JSON.stringify({ global_board_id: boardId }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  }).then(() => {
    loadBoards();
  });
}

async function openTaskDrawer(taskId: number) {
  const client = useSanctumClient();
  await client('/api/admin/task/' + taskId, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  }).then((r) => {
    selectedTask.value = r;
    showDrawer.value = true;
  });
}

async function saveTask() {
  if (!selectedTask.value) return;
  const client = useSanctumClient();
  await client('/api/admin/task/' + selectedTask.value.id, {
    method: 'POST',
    body: JSON.stringify({
      ...selectedTask.value,
      assignees: selectedTask.value.assignees?.map((a: any) => a.id || a) || [],
    }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Úkol uložen.', severity: 'success' });
      loadBoards();
      loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit úkol.', severity: 'error' });
    });
}

async function deleteTask(taskId: number) {
  const client = useSanctumClient();
  await client('/api/admin/task/' + taskId, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    showDrawer.value = false;
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
    openTaskDrawer(selectedTask.value.id);
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
    openTaskDrawer(selectedTask.value.id);
  });
}

// ─── Costs ────────────────────────────────────────────────

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

// ─── Files, Contracts & Price Offers (Soubory tab) ──────────────────────────────
const projectFiles = ref([] as any[]);
const projectContracts = ref([]);
const projectPriceOffers = ref([]);

async function loadProjectContracts() {
  if (route.params.id === 'pridat') return;
  const client = useSanctumClient();
  await client('/api/admin/contract', {
    method: 'GET',
    query: { project_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      projectContracts.value = Array.isArray(d) ? d : [];
    })
    .catch(() => {});
}

async function loadProjectPriceOffers() {
  if (route.params.id === 'pridat') return;
  const client = useSanctumClient();
  await client('/api/admin/price-offer', {
    method: 'GET',
    query: { project_id: route.params.id },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      projectPriceOffers.value = Array.isArray(d) ? d : [];
    })
    .catch(() => {});
}

function onFileUploaded(files: any[]) {
  projectFiles.value = files;
}

function onFileDeleted(fileId: number) {
  projectFiles.value = projectFiles.value.filter((f: any) => f.id !== fileId);
}

async function downloadProjectFile(type: 'contract' | 'price-offer', entity: any) {
  const client = useSanctumClient();
  try {
    const file = entity.files?.[0];
    if (!file) {
      $toast.show({ summary: 'Info', detail: 'Žádný přiložený soubor.', severity: 'warning' });
      return;
    }
    const res = await client.raw('/api/admin/' + type + '/' + entity.id + '/file/' + file.id, {
      method: 'GET',
      credentials: 'include',
      responseType: 'blob',
    });
    if (!res.ok) throw new Error('Chyba');
    const blob = res._data as Blob;
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || type + '-' + entity.id + '.pdf';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (e) {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se stáhnout soubor.', severity: 'error' });
  }
}

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
    loadProjectContracts();
    loadProjectPriceOffers();
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
              <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                  <div class="font-mono text-xl font-bold tabular-nums">
                    {{ formatSeconds(item.total_tracked_seconds) }}
                  </div>
                  <div class="text-xs text-slate-500">Odpracováno</div>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4 text-center ring-1 ring-emerald-200">
                  <div class="text-xl font-bold tabular-nums text-emerald-700">
                    {{ formatNumber(item.total_revenue_without_vat) }} {{ item.currency_symbol }}
                  </div>
                  <div class="text-xs text-emerald-600">Bez DPH</div>
                </div>
                <div class="rounded-xl bg-amber-50 p-4 text-center ring-1 ring-amber-200">
                  <div class="text-xl font-bold tabular-nums text-amber-700">
                    {{ formatNumber(item.total_vat) }} {{ item.currency_symbol }}
                  </div>
                  <div class="text-xs text-amber-600">DPH ({{ item.vat_rate }}%)</div>
                </div>
                <div class="rounded-xl bg-indigo-50 p-4 text-center ring-1 ring-indigo-200">
                  <div class="text-xl font-bold tabular-nums text-indigo-700">
                    {{ formatNumber(item.total_revenue_with_vat) }} {{ item.currency_symbol }}
                  </div>
                  <div class="text-xs text-indigo-600">S DPH</div>
                </div>
                <div class="rounded-xl bg-red-50 p-4 text-center ring-1 ring-red-200">
                  <div class="text-xl font-bold tabular-nums text-red-700">
                    {{ formatNumber(item.total_costs) }} {{ item.currency_symbol }}
                  </div>
                  <div class="text-xs text-red-600">Náklady</div>
                </div>
                <div class="rounded-xl bg-slate-900 p-4 text-center ring-1 ring-slate-700">
                  <div class="text-xl font-bold tabular-nums text-white">
                    {{ formatNumber(item.profit) }} {{ item.currency_symbol }}
                  </div>
                  <div class="text-xs text-slate-400">Zisk</div>
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

      <!-- ═══ Úkoly ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#ukoly')">
        <div class="space-y-6">
          <!-- New task form -->
          <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row">
              <BaseFormInput
                v-model="newTask.name"
                label=""
                name="new_task"
                placeholder="Nový úkol..."
                class="flex-1"
              />
              <div class="flex flex-wrap gap-3">
                <BaseFormSelect
                  v-model="newTask.global_board_id"
                  label=""
                  name="nt_board"
                  :options="boards.map((b) => ({ value: b.id, name: b.name }))"
                  class="w-full sm:w-36"
                />
                <BaseFormSelect
                  v-model="newTask.priority"
                  label=""
                  name="nt_prio"
                  :options="priorityOptions"
                  class="w-full sm:w-28"
                />
                <button
                  type="button"
                  class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500 sm:w-auto"
                  @click="createTask"
                >
                  Přidat
                </button>
              </div>
            </div>
          </div>

          <!-- Empty state -->
          <div v-if="!boards.length && !loading" class="py-16 text-center text-sm text-slate-400">
            Zatím žádné boardy s úkoly.
          </div>

          <!-- Boards as accordion sections -->
          <div class="space-y-4">
            <div
              v-for="board in boards"
              :key="board.id"
              class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"
            >
              <!-- Board header -->
              <div
                class="flex cursor-pointer items-center justify-between px-5 py-4 transition hover:bg-slate-50"
                :style="{ borderLeft: '4px solid ' + board.color }"
                @click="toggleBoard(board.id)"
              >
                <div class="flex items-center gap-3">
                  <component
                    :is="expandedBoards[board.id] ? ChevronDownIcon : ChevronRightIcon"
                    class="size-5 text-slate-400"
                  />
                  <span class="text-sm font-bold text-slate-900">{{ board.name }}</span>
                  <span
                    v-if="board.is_completed"
                    class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                    >Dokončený</span
                  >
                  <span
                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500"
                    >{{ board.tasks?.length || 0 }}</span
                  >
                </div>
              </div>

              <!-- Tasks -->
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
              >
                <div
                  v-if="expandedBoards[board.id]"
                  class="divide-y divide-slate-100 border-t border-slate-100"
                >
                  <div
                    v-for="task in board.tasks"
                    :key="task.id"
                    class="cursor-pointer px-4 py-3 transition hover:bg-slate-50/80 sm:px-5"
                    @click="openTaskDrawer(task.id)"
                  >
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                      <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-[10px] font-bold text-indigo-500">{{
                          task.code
                        }}</span>
                        <span class="text-sm font-medium text-slate-900">{{ task.name }}</span>
                        <span
                          v-if="task.priority && task.priority !== 'normal'"
                          class="rounded-full px-1.5 py-0.5 text-[9px] font-bold"
                          :class="priorityColors[task.priority]"
                          >{{ task.priority }}</span
                        >
                      </div>
                      <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex -space-x-1">
                          <div
                            v-for="a in (task.assignees || []).slice(0, 3)"
                            :key="a.id"
                            class="flex size-6 items-center justify-center rounded-full bg-slate-200 text-[9px] font-bold text-slate-600 ring-1 ring-white"
                          >
                            {{ a.name?.charAt(0) }}
                          </div>
                        </div>
                        <span v-if="task.due_date" class="text-xs text-slate-400">{{
                          task.due_date
                        }}</span>
                        <!-- Move buttons -->
                        <div class="hidden gap-1 sm:flex" @click.stop>
                          <button
                            v-for="targetBoard in boards.filter((b) => b.id !== board.id)"
                            :key="targetBoard.id"
                            type="button"
                            class="rounded px-1.5 py-0.5 text-[9px] font-medium text-slate-400 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-700"
                            @click="moveTask(task.id, targetBoard.id)"
                          >
                            &rarr; {{ targetBoard.name }}
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    v-if="!board.tasks?.length"
                    class="px-5 py-8 text-center text-xs text-slate-400"
                  >
                    Žádné úkoly v tomto boardu.
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </template>

      <!-- ═══ Náklady ═══ -->
      <template v-if="tabs.find((t) => t.current && t.link === '#naklady')">
        <LayoutContainer>
          <LayoutTitle>Náklady projektu</LayoutTitle>
          <div class="mb-4 flex flex-col gap-3 sm:flex-row">
            <BaseFormInput
              v-model="newCost.name"
              label=""
              name="cost_name"
              placeholder="Název"
              class="w-full sm:flex-1"
            />
            <BaseFormInput
              v-model="newCost.amount"
              label=""
              type="number"
              name="cost_amount"
              :step="0.01"
              class="w-full sm:w-32"
            />
            <BaseFormSelect
              v-model="newCost.category"
              label=""
              name="cost_cat"
              :options="costCategoryOptions"
              class="w-full sm:w-40"
            />
            <BaseFormInput
              v-model="newCost.date"
              label=""
              type="date"
              name="cost_date"
              class="w-full sm:w-40"
            />
            <button
              type="button"
              class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 sm:w-auto"
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

      <!-- Soubory tab -->
      <template v-if="tabs.find((t) => t.current && t.link === '#soubory')">
        <LayoutContainer>
          <BaseFileSection
            entity-type="project"
            :entity-id="route.params.id !== 'pridat' ? route.params.id : null"
            :files="projectFiles"
            :allow-upload="true"
            @file-uploaded="onFileUploaded"
            @file-deleted="onFileDeleted"
          >
            <template #actions>
              <NuxtLink
                to="/smlouvy/pridat"
                class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-500"
              >
                Nová smlouva
              </NuxtLink>
            </template>
            <template #extra>
              <!-- Contracts -->
              <div v-if="projectContracts.length" class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-500">Smlouvy</h3>
                <div class="space-y-3">
                  <div
                    v-for="contract in projectContracts"
                    :key="'c-' + contract.id"
                    class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                  >
                    <NuxtLink :to="'/smlouvy/' + contract.id" class="flex-1">
                      <div class="flex items-center gap-3">
                        <span class="font-medium text-slate-900">{{ contract.title }}</span>
                        <span
                          class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                          :class="{
                            'bg-emerald-100 text-emerald-700': contract.status === 'active',
                            'bg-slate-100 text-slate-600': contract.status === 'draft',
                            'bg-red-100 text-red-700': contract.status === 'terminated',
                            'bg-amber-100 text-amber-700': contract.status === 'expired',
                          }"
                        >
                          {{
                            {
                              draft: 'Koncept',
                              active: 'Aktivní',
                              terminated: 'Ukončená',
                              expired: 'Vypršelá',
                            }[contract.status] || contract.status
                          }}
                        </span>
                      </div>
                      <div class="mt-1 text-xs text-slate-500">
                        {{ contract.date_from || '—' }} &mdash;
                        {{ contract.date_to || 'Doba neurčitá' }}
                      </div>
                    </NuxtLink>
                    <button
                      v-if="contract.files?.length"
                      type="button"
                      class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                      @click="downloadProjectFile('contract', contract)"
                    >
                      Stáhnout
                    </button>
                  </div>
                </div>
              </div>

              <!-- Price Offers -->
              <div v-if="projectPriceOffers.length" class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-500">Cenové nabídky</h3>
                <div class="space-y-3">
                  <div
                    v-for="offer in projectPriceOffers"
                    :key="'o-' + offer.id"
                    class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                  >
                    <NuxtLink :to="'/cenove-nabidky/' + offer.id" class="flex-1">
                      <div class="flex items-center gap-3">
                        <span class="font-medium text-slate-900"
                          >{{ offer.code }} — {{ offer.title }}</span
                        >
                        <span
                          class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                          :class="{
                            'bg-slate-100 text-slate-600': offer.status === 'draft',
                            'bg-blue-100 text-blue-700': offer.status === 'sent',
                            'bg-emerald-100 text-emerald-700': offer.status === 'accepted',
                            'bg-red-100 text-red-700': offer.status === 'rejected',
                            'bg-amber-100 text-amber-700': offer.status === 'expired',
                          }"
                        >
                          {{
                            {
                              draft: 'Koncept',
                              sent: 'Odeslaná',
                              accepted: 'Přijatá',
                              rejected: 'Zamítnutá',
                              expired: 'Vypršelá',
                            }[offer.status] || offer.status
                          }}
                        </span>
                      </div>
                      <div class="mt-1 text-xs text-slate-500">
                        Celkem s DPH: {{ offer.total_with_vat }} · Platnost do
                        {{ offer.valid_to || '—' }}
                      </div>
                    </NuxtLink>
                    <button
                      v-if="offer.files?.length"
                      type="button"
                      class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
                      @click="downloadProjectFile('price-offer', offer)"
                    >
                      Stáhnout PDF
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </BaseFileSection>
        </LayoutContainer>
      </template>
    </Form>

    <!-- ═══ Task Drawer ═══ -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div v-if="showDrawer && selectedTask" class="fixed inset-y-0 right-0 z-50 flex">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black/30" @click="showDrawer = false"></div>

          <!-- Drawer panel -->
          <div
            class="relative ml-auto flex h-full w-full flex-col bg-white shadow-2xl"
            style="max-width: 700px"
          >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
              <div>
                <span class="font-mono text-xs font-bold text-indigo-500">{{
                  selectedTask.code
                }}</span>
                <h2 class="text-lg font-bold text-slate-900">{{ selectedTask.name }}</h2>
              </div>
              <button
                type="button"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
                @click="showDrawer = false"
              >
                <XMarkIcon class="size-5" />
              </button>
            </div>

            <!-- Content (scrollable) -->
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
              <BaseFormInput v-model="selectedTask.name" label="Název" name="task_name" />
              <BaseFormTextarea
                v-model="selectedTask.description"
                label="Popis"
                name="task_desc"
                rows="4"
              />

              <div class="grid grid-cols-2 gap-4">
                <BaseFormSelect
                  v-model="selectedTask.global_board_id"
                  label="Board"
                  name="task_board"
                  :options="boards.map((b) => ({ value: b.id, name: b.name }))"
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
              </div>

              <!-- Assignees -->
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Přiřazení</label>
                <div
                  class="max-h-32 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2"
                >
                  <label
                    v-for="user in users"
                    :key="user.value"
                    class="flex items-center gap-2 rounded p-1 text-xs hover:bg-slate-50"
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
                    />
                    {{ user.name }}
                  </label>
                </div>
              </div>

              <!-- Time entries -->
              <div v-if="selectedTask.time_entries?.length" class="border-t border-slate-100 pt-4">
                <h3 class="mb-2 flex items-center gap-2 text-xs font-bold text-slate-700">
                  <ClockIcon class="size-4" /> Čas ({{
                    formatSeconds(selectedTask.total_tracked_seconds)
                  }})
                </h3>
                <div class="space-y-1">
                  <div
                    v-for="te in selectedTask.time_entries"
                    :key="te.id"
                    class="flex items-center justify-between text-xs text-slate-600"
                  >
                    <span>{{ te.hours }}h — {{ te.description || '—' }} ({{ te.date }})</span>
                    <span>{{ te.user_name }}</span>
                  </div>
                </div>
              </div>

              <!-- Comments -->
              <div class="border-t border-slate-100 pt-4">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-bold text-slate-700">
                  <ChatBubbleLeftIcon class="size-4" /> Komentáře ({{
                    selectedTask.comments?.length || 0
                  }})
                </h3>
                <div class="mb-3 flex gap-2">
                  <BaseFormInput
                    v-model="newComment"
                    label=""
                    name="comment"
                    placeholder="Napište komentář..."
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
                <div class="max-h-40 space-y-2 overflow-y-auto">
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
                      {{ c.created_at ? new Date(c.created_at).toLocaleString('cs-CZ') : '' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer actions -->
            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <button
                type="button"
                class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-200"
                @click="deleteTask(selectedTask.id)"
              >
                <TrashIcon class="mr-1 inline size-4" /> Smazat
              </button>
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
                @click="saveTask"
              >
                <CheckCircleIcon class="mr-1 inline size-4" /> Uložit
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
