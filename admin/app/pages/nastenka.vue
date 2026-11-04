<script setup lang="ts">
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';
import {
  PlusIcon,
  TrashIcon,
  XMarkIcon,
  Bars3Icon,
  PencilSquareIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const { canEdit, canDelete } = usePermissions();

const pageTitle = ref('Nástěnka');
const breadcrumbs = ref([{ name: pageTitle.value, link: '/nastenka', current: true }]);

const loading = ref(false);
const sections = ref([] as any[]);

const priorityOptions = ref([
  { value: 'low', name: 'Nízká' },
  { value: 'medium', name: 'Střední' },
  { value: 'high', name: 'Vysoká' },
  { value: 'critical', name: 'Kritická' },
]);

const priorityColors: Record<string, string> = {
  critical: 'bg-red-100 text-red-700',
  high: 'bg-orange-100 text-orange-700',
  medium: 'bg-slate-100 text-slate-600',
  low: 'bg-blue-100 text-blue-600',
};

// Mapování na Tailwind třídy (stejný vzor jako Props/Badge.vue) — BaseFormColorPicker
// vrací název Tailwind barvy (např. "indigo"), ne hex kód, takže třídy musí být literální.
const sectionColorClasses: Record<string, { bg: string; dot: string }> = {
  red: { bg: 'bg-red-50', dot: 'bg-red-500' },
  orange: { bg: 'bg-orange-50', dot: 'bg-orange-500' },
  yellow: { bg: 'bg-yellow-50', dot: 'bg-yellow-500' },
  lime: { bg: 'bg-lime-50', dot: 'bg-lime-500' },
  green: { bg: 'bg-green-50', dot: 'bg-green-500' },
  emerald: { bg: 'bg-emerald-50', dot: 'bg-emerald-500' },
  teal: { bg: 'bg-teal-50', dot: 'bg-teal-500' },
  cyan: { bg: 'bg-cyan-50', dot: 'bg-cyan-500' },
  sky: { bg: 'bg-sky-50', dot: 'bg-sky-500' },
  blue: { bg: 'bg-blue-50', dot: 'bg-blue-500' },
  indigo: { bg: 'bg-indigo-50', dot: 'bg-indigo-500' },
  violet: { bg: 'bg-violet-50', dot: 'bg-violet-500' },
  purple: { bg: 'bg-purple-50', dot: 'bg-purple-500' },
  fuchsia: { bg: 'bg-fuchsia-50', dot: 'bg-fuchsia-500' },
  pink: { bg: 'bg-pink-50', dot: 'bg-pink-500' },
  rose: { bg: 'bg-rose-50', dot: 'bg-rose-500' },
  slate: { bg: 'bg-slate-100', dot: 'bg-slate-500' },
  gray: { bg: 'bg-gray-100', dot: 'bg-gray-500' },
  zinc: { bg: 'bg-zinc-100', dot: 'bg-zinc-500' },
  stone: { bg: 'bg-stone-100', dot: 'bg-stone-500' },
  neutral: { bg: 'bg-neutral-100', dot: 'bg-neutral-500' },
};

function sectionColorClass(color: string, variant: 'bg' | 'dot') {
  return (sectionColorClasses[color] || sectionColorClasses.slate)[variant];
}

// ─── Load ──────────────────────────────────────────────────

async function loadSections() {
  loading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/contact/board/section', {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r: any) => {
      sections.value = r;
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst nástěnku.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

// ─── Sections CRUD + reorder ───────────────────────────────

const showSectionDialog = ref(false);
const editingSection = ref({ id: null as number | null, name: '', color: 'indigo' });

function openNewSection() {
  editingSection.value = { id: null, name: '', color: 'indigo' };
  showSectionDialog.value = true;
}

function openEditSection(section: any) {
  editingSection.value = { id: section.id, name: section.name, color: section.color };
  showSectionDialog.value = true;
}

async function saveSection() {
  if (!editingSection.value.name) return;
  const client = useSanctumClient();
  const url = editingSection.value.id
    ? '/api/admin/contact/board/section/' + editingSection.value.id
    : '/api/admin/contact/board/section';
  await client(url, {
    method: 'POST',
    body: JSON.stringify(editingSection.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      showSectionDialog.value = false;
      loadSections();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit sekci.', severity: 'error' });
    });
}

const showReassignDialog = ref(false);
const sectionToDelete = ref(null as any);
const moveToSectionId = ref(null as number | null);

const otherSections = computed(() =>
  sections.value
    .filter((s) => s.id !== sectionToDelete.value?.id)
    .map((s) => ({ value: s.id, name: s.name })),
);

function confirmDeleteSection(section: any) {
  if ((section.cards?.length || 0) === 0) {
    deleteSectionNow(section.id);
    return;
  }
  sectionToDelete.value = section;
  moveToSectionId.value = otherSections.value.find((s) => s.value !== section.id)?.value ?? null;
  showReassignDialog.value = true;
}

async function deleteSectionNow(id: number, moveTo: number | null = null) {
  const client = useSanctumClient();
  await client('/api/admin/contact/board/section/' + id, {
    method: 'DELETE',
    query: moveTo ? { move_to_section_id: moveTo } : {},
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      showReassignDialog.value = false;
      loadSections();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat sekci.', severity: 'error' });
    });
}

function submitReassignAndDelete() {
  if (!sectionToDelete.value || !moveToSectionId.value) return;
  deleteSectionNow(sectionToDelete.value.id, moveToSectionId.value);
}

async function persistSectionOrder() {
  const client = useSanctumClient();
  await client('/api/admin/contact/board/section/reorder', {
    method: 'POST',
    body: JSON.stringify({ ids: sections.value.map((s) => s.id) }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).catch(() => {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se uložit pořadí sekcí.',
      severity: 'error',
    });
  });
}

// ─── Cards CRUD + reorder ──────────────────────────────────

const showCardDrawer = ref(false);
const selectedCard = ref(null as any);
const selectedCardContactOptions = ref({ id: null, firstname: '', lastname: '' });

function openNewCard(section: any) {
  selectedCard.value = {
    id: null,
    title: '',
    contact_id: null,
    contact_board_section_id: section.id,
    priority: 'medium',
    due_date: null,
    description: '',
    note: '',
  };
  selectedCardContactOptions.value = { id: null, firstname: '', lastname: '' };
  showCardDrawer.value = true;
}

async function openCard(card: any) {
  const client = useSanctumClient();
  await client('/api/admin/contact/board/card/' + card.id, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r: any) => {
      selectedCard.value = r;
      selectedCardContactOptions.value = r.contact || { id: null, firstname: '', lastname: '' };
      showCardDrawer.value = true;
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst kartu.', severity: 'error' });
    });
}

async function saveCard() {
  if (!selectedCard.value?.title) {
    $toast.show({
      summary: 'Chybí údaje',
      detail: 'Vyplňte nadpis karty.',
      severity: 'warn',
    });
    return;
  }
  const client = useSanctumClient();
  const url = selectedCard.value.id
    ? '/api/admin/contact/board/card/' + selectedCard.value.id
    : '/api/admin/contact/board/card';
  await client(url, {
    method: 'POST',
    body: JSON.stringify(selectedCard.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Karta uložena.', severity: 'success' });
      showCardDrawer.value = false;
      loadSections();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit kartu.', severity: 'error' });
    });
}

async function deleteCard() {
  if (!selectedCard.value?.id) return;
  const client = useSanctumClient();
  await client('/api/admin/contact/board/card/' + selectedCard.value.id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      showCardDrawer.value = false;
      loadSections();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat kartu.', severity: 'error' });
    });
}

async function persistCardOrder() {
  const client = useSanctumClient();
  const items = sections.value.flatMap((section) =>
    (section.cards || []).map((card: any, index: number) => ({
      id: card.id,
      position: index,
      contact_board_section_id: section.id,
    })),
  );
  await client('/api/admin/contact/board/card/reorder', {
    method: 'POST',
    body: JSON.stringify({ items }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).catch(() => {
    $toast.show({
      summary: 'Chyba',
      detail: 'Nepodařilo se uložit pořadí karet.',
      severity: 'error',
    });
  });
}

function formatDate(value: string | null) {
  if (!value) return '';
  return new Date(value).toLocaleDateString('cs-CZ');
}

useHead({ title: pageTitle.value });
onMounted(() => {
  loadSections();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="contact_board_sections" />

    <div v-if="canEdit('contact_board_sections')" class="flex justify-end">
      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
        @click="openNewSection"
      >
        <PlusIcon class="size-4" /> Přidat sekci
      </button>
    </div>

    <div v-if="!loading && !sections.length" class="py-16 text-center text-sm text-slate-400">
      Vytvořte první sekci nástěnky.
    </div>

    <draggable
      v-model="sections"
      item-key="id"
      handle=".section-drag-handle"
      class="flex items-start gap-4 overflow-x-auto pb-4"
      :animation="150"
      @end="persistSectionOrder"
    >
      <template #item="{ element: section }">
        <div
          class="flex w-80 shrink-0 flex-col rounded-2xl p-3"
          :class="sectionColorClass(section.color, 'bg')"
        >
          <!-- Section header -->
          <div class="mb-3 flex items-center justify-between gap-2 px-1">
            <div class="flex min-w-0 items-center gap-2">
              <Bars3Icon
                class="section-drag-handle size-4 shrink-0 cursor-grab text-slate-400 active:cursor-grabbing"
              />
              <span
                class="size-2.5 shrink-0 rounded-full"
                :class="sectionColorClass(section.color, 'dot')"
              />
              <span class="truncate text-sm font-bold text-slate-900">{{ section.name }}</span>
              <span
                class="shrink-0 rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold text-slate-500"
              >
                {{ section.cards?.length || 0 }}
              </span>
            </div>
            <div v-if="canEdit('contact_board_sections')" class="flex shrink-0 items-center gap-1">
              <button
                type="button"
                class="rounded-lg p-1 text-slate-400 transition hover:bg-white hover:text-slate-700"
                @click="openEditSection(section)"
              >
                <PencilSquareIcon class="size-4" />
              </button>
              <button
                v-if="canDelete('contact_board_sections')"
                type="button"
                class="rounded-lg p-1 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                @click="confirmDeleteSection(section)"
              >
                <TrashIcon class="size-4" />
              </button>
            </div>
          </div>

          <!-- Cards -->
          <draggable
            v-model="section.cards"
            item-key="id"
            group="board-cards"
            class="min-h-[40px] space-y-2"
            :animation="150"
            @end="persistCardOrder"
          >
            <template #item="{ element: card }">
              <div
                class="cursor-pointer rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md"
                @click="openCard(card)"
              >
                <div class="mb-1.5 flex items-start justify-between gap-2">
                  <span class="text-sm font-semibold text-slate-900">{{ card.title }}</span>
                  <span
                    class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-bold"
                    :class="priorityColors[card.priority]"
                    >{{ priorityOptions.find((p) => p.value === card.priority)?.name }}</span
                  >
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400">
                  <span class="truncate">{{ card.contact_name || 'Bez kontaktu' }}</span>
                  <span v-if="card.due_date" class="shrink-0">{{ formatDate(card.due_date) }}</span>
                </div>
              </div>
            </template>
          </draggable>

          <button
            v-if="canEdit('contact_board_sections')"
            type="button"
            class="mt-2 flex items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-slate-200 py-2 text-xs font-medium text-slate-400 transition hover:border-indigo-300 hover:bg-white hover:text-indigo-600"
            @click="openNewCard(section)"
          >
            <PlusIcon class="size-3.5" /> Přidat kartu
          </button>
        </div>
      </template>
    </draggable>

    <!-- Section create/edit dialog -->
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
          v-if="showSectionDialog"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showSectionDialog = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">
              {{ editingSection.id ? 'Upravit sekci' : 'Nová sekce' }}
            </h3>
            <div class="space-y-4">
              <BaseFormInput
                v-model="editingSection.name"
                label="Název"
                name="section_name"
                rules="required"
              />
              <BaseFormColorPicker
                v-model="editingSection.color"
                label="Barva"
                name="section_color"
              />
              <div class="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  @click="showSectionDialog = false"
                >
                  Zrušit
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                  @click="saveSection"
                >
                  Uložit
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Reassign cards before delete dialog -->
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
          v-if="showReassignDialog"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showReassignDialog = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-2 text-lg font-bold text-slate-900">
              Smazat sekci "{{ sectionToDelete?.name }}"
            </h3>
            <p class="mb-4 text-sm text-slate-500">
              Sekce obsahuje {{ sectionToDelete?.cards?.length || 0 }} karet. Vyberte sekci, do
              které se mají přesunout.
            </p>
            <div v-if="otherSections.length" class="space-y-4">
              <BaseFormSelect
                v-model="moveToSectionId"
                label="Přesunout karty do"
                name="move_to_section_id"
                :options="otherSections"
              />
              <div class="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  @click="showReassignDialog = false"
                >
                  Zrušit
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
                  @click="submitReassignAndDelete"
                >
                  Přesunout a smazat
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-red-500">
              Nejdřív vytvořte jinou sekci, do které lze karty přesunout.
            </p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Card drawer -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div v-if="showCardDrawer && selectedCard" class="fixed inset-y-0 right-0 z-50 flex">
          <div class="fixed inset-0 bg-black/30" @click="showCardDrawer = false"></div>

          <div
            class="relative ml-auto flex h-full w-full flex-col bg-white shadow-2xl"
            style="max-width: 600px"
          >
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
              <h2 class="text-lg font-bold text-slate-900">
                {{ selectedCard.id ? 'Upravit kartu' : 'Nová karta' }}
              </h2>
              <button
                type="button"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
                @click="showCardDrawer = false"
              >
                <XMarkIcon class="size-5" />
              </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
              <BaseFormInput
                v-model="selectedCard.title"
                label="Nadpis"
                name="card_title"
                rules="required"
              />

              <ContactAutocomplete
                v-model="selectedCard.contact_id"
                :contact-options="selectedCardContactOptions"
                label="Přiřazený kontakt"
              />

              <div class="grid grid-cols-2 gap-4">
                <BaseFormSelect
                  v-model="selectedCard.contact_board_section_id"
                  label="Sekce"
                  name="card_section"
                  :options="sections.map((s) => ({ value: s.id, name: s.name }))"
                />
                <BaseFormSelect
                  v-model="selectedCard.priority"
                  label="Priorita"
                  name="card_priority"
                  :options="priorityOptions"
                />
                <BaseFormInput
                  v-model="selectedCard.due_date"
                  label="Datum splnění"
                  type="date"
                  name="card_due_date"
                />
                <div v-if="selectedCard.created_at">
                  <label class="mb-1.5 block text-sm font-medium text-slate-700"
                    >Datum vytvoření</label
                  >
                  <p class="py-2.5 text-sm text-slate-500">
                    {{ formatDate(selectedCard.created_at) }}
                  </p>
                </div>
              </div>

              <BaseFormTextarea
                v-model="selectedCard.description"
                label="Popis"
                name="card_description"
                rows="4"
              />
              <BaseFormTextarea
                v-model="selectedCard.note"
                label="Poznámka"
                name="card_note"
                rows="3"
              />
            </div>

            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <button
                v-if="selectedCard.id && canDelete('contact_board_sections')"
                type="button"
                class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-200"
                @click="deleteCard"
              >
                <TrashIcon class="mr-1 inline size-4" /> Smazat
              </button>
              <div v-else />
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
                @click="saveCard"
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
