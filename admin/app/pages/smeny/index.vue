<script setup lang="ts">
import { ref, computed, inject } from 'vue';
import { definePageMeta } from '#imports';

const { $toast } = useNuxtApp();
const pageTitle = ref('Směny');
const loading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const breadcrumbs = ref([{ name: pageTitle.value, link: '/smeny', current: true }]);

const shifts = ref([]);
const employees = ref([]);
const templates = ref([]);

// Calendar state
const currentDate = ref(new Date());
const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());
const monthName = computed(() =>
  currentDate.value.toLocaleString('cs-CZ', { month: 'long', year: 'numeric' }),
);

const todayStr = computed(() => formatDate(new Date()));

const calendarDays = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value;
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startOffset = (firstDay.getDay() + 6) % 7; // Monday start
  const days = [];

  for (let i = 0; i < startOffset; i++) {
    const d = new Date(year, month, -startOffset + i + 1);
    days.push({ date: d, inMonth: false, dateStr: formatDate(d) });
  }
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const d = new Date(year, month, i);
    days.push({ date: d, inMonth: true, dateStr: formatDate(d) });
  }
  const remaining = 7 - (days.length % 7);
  if (remaining < 7) {
    for (let i = 1; i <= remaining; i++) {
      const d = new Date(year, month + 1, i);
      days.push({ date: d, inMonth: false, dateStr: formatDate(d) });
    }
  }
  return days;
});

function formatDate(d: Date): string {
  return d.toISOString().split('T')[0];
}

function shiftsForDay(dateStr: string) {
  return shifts.value.filter((s: any) => s.date === dateStr);
}

function prevMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
  loadShifts();
}
function nextMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
  loadShifts();
}
function goToday() {
  currentDate.value = new Date();
  loadShifts();
}

// CRUD
const showForm = ref(false);
const editingShift = ref({
  id: null,
  date: '',
  start_time: '08:00',
  end_time: '16:00',
  break_minutes: 30,
  shift_template_id: null,
  location: '',
  note: '',
  employees: [] as number[],
});

function openNewShift(dateStr: string = '') {
  editingShift.value = {
    id: null,
    date: dateStr || formatDate(new Date()),
    start_time: '08:00',
    end_time: '16:00',
    break_minutes: 30,
    shift_template_id: null,
    location: '',
    note: '',
    employees: [],
  };
  showForm.value = true;
}

function openEditShift(shift: any) {
  editingShift.value = {
    id: shift.id,
    date: shift.date,
    start_time: shift.start_time,
    end_time: shift.end_time,
    break_minutes: shift.break_minutes,
    shift_template_id: shift.shift_template_id,
    location: shift.location,
    note: shift.note,
    employees: shift.employees?.map((e: any) => e.id) || [],
  };
  showForm.value = true;
}

function applyTemplate() {
  const tmpl = templates.value.find((t: any) => t.id === editingShift.value.shift_template_id);
  if (tmpl) {
    editingShift.value.start_time = tmpl.start_time;
    editingShift.value.end_time = tmpl.end_time;
    editingShift.value.break_minutes = tmpl.break_minutes;
  }
}

async function loadShifts() {
  loading.value = true;
  const client = useSanctumClient();
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
  await client('/api/admin/shift', {
    method: 'GET',
    query: { date_from: formatDate(firstDay), date_to: formatDate(lastDay) },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      shifts.value = Array.isArray(d) ? d : [];
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadEmployees() {
  const client = useSanctumClient();
  await client('/api/admin/employee', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      employees.value = d.map((e: any) => ({
        value: e.id,
        name: e.full_name || e.first_name + ' ' + e.last_name,
      }));
    })
    .catch(() => {});
}

async function loadTemplates() {
  const client = useSanctumClient();
  await client('/api/admin/shift/template', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      templates.value = Array.isArray(d) ? d : [];
    })
    .catch(() => {});
}

async function saveShift() {
  const client = useSanctumClient();
  const url = editingShift.value.id
    ? '/api/admin/shift/' + editingShift.value.id
    : '/api/admin/shift';
  await client(url, {
    method: 'POST',
    body: JSON.stringify(editingShift.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Směna uložena.', severity: 'success' });
      showForm.value = false;
      loadShifts();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit směnu.', severity: 'error' });
    });
}

async function deleteShift(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/shift/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then(() => {
    loadShifts();
  });
}

watch(selectedSiteHash, () => {
  loadShifts();
  loadTemplates();
});

useHead({ title: pageTitle.value });
onMounted(() => {
  loadShifts();
  loadEmployees();
  loadTemplates();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="shifts" />

    <!-- Calendar header -->
    <LayoutContainer class="!py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-lg p-2 hover:bg-slate-100" @click="prevMonth">
            &larr;
          </button>
          <h2 class="min-w-[180px] text-center text-lg font-bold capitalize text-slate-900">
            {{ monthName }}
          </h2>
          <button type="button" class="rounded-lg p-2 hover:bg-slate-100" @click="nextMonth">
            &rarr;
          </button>
          <button
            type="button"
            class="ml-2 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
            @click="goToday"
          >
            Dnes
          </button>
        </div>
        <button
          type="button"
          class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
          @click="openNewShift()"
        >
          Nová směna
        </button>
      </div>
    </LayoutContainer>

    <!-- Calendar grid -->
    <LayoutContainer class="overflow-hidden !p-0">
      <div class="grid grid-cols-7">
        <div
          v-for="day in ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne']"
          :key="day"
          class="border-b border-slate-200 bg-slate-50 px-2 py-2 text-center text-xs font-bold uppercase tracking-wider text-slate-400"
        >
          {{ day }}
        </div>
      </div>
      <div class="grid grid-cols-7">
        <div
          v-for="day in calendarDays"
          :key="day.dateStr"
          class="min-h-[100px] cursor-pointer border-b border-r border-slate-100 p-1.5 transition hover:bg-slate-50"
          :class="{
            'bg-white': day.inMonth && day.dateStr !== todayStr,
            'bg-slate-50/50': !day.inMonth,
            'bg-indigo-50/60': day.dateStr === todayStr,
          }"
          @click="openNewShift(day.dateStr)"
        >
          <div
            class="mb-1 flex items-center justify-center text-xs font-medium"
            :class="day.inMonth ? 'text-slate-700' : 'text-slate-300'"
          >
            <span
              v-if="day.dateStr === todayStr"
              class="flex size-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white"
            >
              {{ day.date.getDate() }}
            </span>
            <span v-else>{{ day.date.getDate() }}</span>
          </div>
          <div class="space-y-1">
            <div
              v-for="shift in shiftsForDay(day.dateStr)"
              :key="shift.id"
              class="rounded-md px-1.5 py-0.5 text-[10px] font-medium leading-tight transition hover:opacity-80"
              :style="{
                backgroundColor: (shift.template?.color || '#6366f1') + '20',
                color: shift.template?.color || '#6366f1',
                borderLeft: '3px solid ' + (shift.template?.color || '#6366f1'),
              }"
              @click.stop="openEditShift(shift)"
            >
              <div class="font-bold">
                {{ shift.start_time?.slice(0, 5) }}-{{ shift.end_time?.slice(0, 5) }}
              </div>
              <div v-for="emp in shift.employees" :key="emp.id" class="truncate">
                {{ emp.full_name }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </LayoutContainer>

    <!-- Shift form modal -->
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
          <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">
              {{ editingShift.id ? 'Upravit směnu' : 'Nová směna' }}
            </h3>
            <div class="space-y-4">
              <BaseFormInput v-model="editingShift.date" label="Datum" type="date" name="s_date" />
              <div class="flex gap-3">
                <BaseFormSelect
                  v-model="editingShift.shift_template_id"
                  label="Šablona"
                  name="s_template"
                  :options="templates.map((t) => ({ value: t.id, name: t.name }))"
                  class="flex-1"
                  placeholder="-- Bez šablony --"
                  @change="applyTemplate"
                />
                <button
                  v-if="editingShift.shift_template_id"
                  type="button"
                  class="self-end rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-300"
                  @click="applyTemplate"
                >
                  Aplikovat
                </button>
              </div>
              <div class="grid grid-cols-3 gap-3">
                <BaseFormInput
                  v-model="editingShift.start_time"
                  label="Od"
                  type="time"
                  name="s_start"
                />
                <BaseFormInput
                  v-model="editingShift.end_time"
                  label="Do"
                  type="time"
                  name="s_end"
                />
                <BaseFormInput
                  v-model="editingShift.break_minutes"
                  label="Přestávka (min)"
                  type="number"
                  name="s_break"
                />
              </div>
              <BaseFormInput v-model="editingShift.location" label="Místo" name="s_location" />
              <BaseFormTextarea
                v-model="editingShift.note"
                label="Poznámka"
                name="s_note"
                rows="2"
              />

              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Zaměstnanci</label>
                <div
                  class="max-h-40 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2"
                >
                  <label
                    v-for="emp in employees"
                    :key="emp.value"
                    class="flex items-center gap-2 rounded p-1 text-xs hover:bg-slate-50"
                  >
                    <input
                      type="checkbox"
                      :checked="editingShift.employees.includes(emp.value)"
                      class="rounded text-indigo-600"
                      @change="
                        editingShift.employees.includes(emp.value)
                          ? (editingShift.employees = editingShift.employees.filter(
                              (e) => e !== emp.value,
                            ))
                          : editingShift.employees.push(emp.value)
                      "
                    />
                    {{ emp.name }}
                  </label>
                </div>
              </div>

              <div class="flex justify-between pt-2">
                <button
                  v-if="editingShift.id"
                  type="button"
                  class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-200"
                  @click="
                    deleteShift(editingShift.id);
                    showForm = false;
                  "
                >
                  Smazat
                </button>
                <div v-else></div>
                <div class="flex gap-2">
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
                    @click="saveShift"
                  >
                    Uložit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
