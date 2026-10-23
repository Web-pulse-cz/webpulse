<script setup lang="ts">
import { ref, computed, inject } from 'vue';
import {
  PhoneIcon,
  EnvelopeIcon,
  ChatBubbleLeftIcon,
  ArrowsRightLeftIcon,
  XCircleIcon,
  CheckCircleIcon,
  ClockIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';
import { getCzechHolidays } from '~/composables/useCzechHolidays';

const { $toast } = useNuxtApp();
const pageTitle = ref('Rezervace');
const loading = ref(false);
const dayLoading = ref(false);
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const breadcrumbs = ref([
  { name: 'Služby', link: '/obsah/sluzby', current: false },
  { name: pageTitle.value, link: '/obsah/sluzby/rezervace', current: true },
]);

const statusMap: Record<string, { label: string; class: string }> = {
  pending: { label: 'Čeká', class: 'bg-amber-100 text-amber-700' },
  confirmed: { label: 'Potvrzeno', class: 'bg-blue-100 text-blue-700' },
  completed: { label: 'Dokončeno', class: 'bg-slate-100 text-slate-600' },
  cancelled: { label: 'Zrušeno', class: 'bg-red-100 text-red-700' },
  no_show: { label: 'Nedorazili', class: 'bg-red-50 text-red-500' },
};

// Calendar state
const currentDate = ref(new Date());
const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());
const monthName = computed(() =>
  currentDate.value.toLocaleString('cs-CZ', { month: 'long', year: 'numeric' }),
);

function formatDate(d: Date): string {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

const todayStr = computed(() => formatDate(new Date()));
const selectedDate = ref(todayStr.value);

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

function holidayName(dateStr: string): string | undefined {
  const year = Number(dateStr.slice(0, 4));
  return getCzechHolidays(year).get(dateStr);
}

// Bookings for the visible calendar range (for the day badges)
const monthBookings = ref<any[]>([]);

const bookingCounts = computed(() => {
  const counts: Record<string, number> = {};
  monthBookings.value.forEach((b: any) => {
    counts[b.date] = (counts[b.date] || 0) + 1;
  });
  return counts;
});

async function loadMonthBookings() {
  loading.value = true;
  const client = useSanctumClient();
  const days = calendarDays.value;
  const dateFrom = days[0]?.dateStr;
  const dateTo = days[days.length - 1]?.dateStr;

  await client('/api/admin/service-booking', {
    method: 'GET',
    query: { date_from: dateFrom, date_to: dateTo },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      monthBookings.value = Array.isArray(r) ? r : r?.data || [];
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst přehled rezervací.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

// Bookings for the selected day (right column list)
const dayBookings = ref<any[]>([]);

async function loadDayBookings() {
  dayLoading.value = true;
  const client = useSanctumClient();

  await client('/api/admin/service-booking', {
    method: 'GET',
    query: { date: selectedDate.value },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      dayBookings.value = Array.isArray(r) ? r : r?.data || [];
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst rezervace pro vybraný den.',
        severity: 'error',
      });
    })
    .finally(() => {
      dayLoading.value = false;
    });
}

function selectDay(dateStr: string) {
  selectedDate.value = dateStr;
  loadDayBookings();
}

function prevMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
  loadMonthBookings();
}
function nextMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
  loadMonthBookings();
}
function goToday() {
  currentDate.value = new Date();
  selectedDate.value = todayStr.value;
  loadMonthBookings();
  loadDayBookings();
}

const selectedDateFormatted = computed(() => {
  const [y, m, d] = selectedDate.value.split('-');
  return `${d}. ${m}. ${y}`;
});

// Move (reschedule) modal
const showMoveModal = ref(false);
const moveBooking = ref<any>(null);
const moveDate = ref('');
const moveTime = ref('');
const moveSlots = ref<string[]>([]);
const moveLoading = ref(false);

function openMoveModal(booking: any) {
  moveBooking.value = booking;
  moveDate.value = booking.date;
  moveTime.value = '';
  moveSlots.value = [];
  showMoveModal.value = true;
  loadMoveSlots();
}

async function loadMoveSlots() {
  if (!moveBooking.value || !moveDate.value) return;
  const client = useSanctumClient();
  await client('/api/admin/service-booking/slots', {
    method: 'GET',
    query: { service_id: moveBooking.value.service_id, date: moveDate.value },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      moveSlots.value = Array.isArray(r) ? r : [];
    })
    .catch(() => {
      moveSlots.value = [];
    });
}

watch(moveDate, () => {
  moveTime.value = '';
  loadMoveSlots();
});

async function confirmMove() {
  if (!moveTime.value) {
    $toast.show({ summary: 'Chyba', detail: 'Vyberte prosím volný čas.', severity: 'error' });
    return;
  }

  moveLoading.value = true;
  const client = useSanctumClient();
  const booking = moveBooking.value;

  await client('/api/admin/service-booking/' + booking.id, {
    method: 'POST',
    body: JSON.stringify({
      service_id: booking.service_id,
      date: moveDate.value,
      time_from: moveTime.value,
      first_name: booking.first_name,
      last_name: booking.last_name,
      phone: booking.phone,
      email: booking.email,
      note: booking.note,
      status: booking.status,
    }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Rezervace byla přesunuta.', severity: 'success' });
      showMoveModal.value = false;
      loadDayBookings();
      loadMonthBookings();
    })
    .catch((e) => {
      const msg = e?.data?.message || 'Nepodařilo se přesunout rezervaci.';
      $toast.show({ summary: 'Chyba', detail: msg, severity: 'error' });
    })
    .finally(() => {
      moveLoading.value = false;
    });
}

async function updateBookingStatus(booking: any, status: string, errorMsg: string) {
  const client = useSanctumClient();
  await client('/api/admin/service-booking/' + booking.id + '/status', {
    method: 'POST',
    body: JSON.stringify({ status }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Stav rezervace byl změněn.', severity: 'success' });
      loadDayBookings();
      loadMonthBookings();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: errorMsg, severity: 'error' });
    });
}

async function cancelBooking(booking: any) {
  const confirmed = window.confirm(
    `Opravdu chcete zrušit rezervaci ${booking.first_name} ${booking.last_name} (${booking.time_from?.slice(0, 5)})?`,
  );
  if (!confirmed) return;

  await updateBookingStatus(booking, 'cancelled', 'Nepodařilo se zrušit rezervaci.');
}

async function completeBooking(booking: any) {
  await updateBookingStatus(booking, 'completed', 'Nepodařilo se označit rezervaci jako hotovou.');
}

// Add booking modal
const services = ref<any[]>([]);
const showAddModal = ref(false);
const addServiceId = ref<number | string>('');
const addDate = ref('');
const addTime = ref('');
const addSlots = ref<string[]>([]);
const addFirstName = ref('');
const addLastName = ref('');
const addPhone = ref('');
const addEmail = ref('');
const addNote = ref('');
const addLoading = ref(false);

async function loadServices() {
  const client = useSanctumClient();
  await client('/api/admin/service', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      services.value = Array.isArray(r) ? r : r?.data || [];
    })
    .catch(() => {
      services.value = [];
    });
}

async function loadAddSlots() {
  if (!addServiceId.value || !addDate.value) {
    addSlots.value = [];
    return;
  }
  const client = useSanctumClient();
  await client('/api/admin/service-booking/slots', {
    method: 'GET',
    query: { service_id: addServiceId.value, date: addDate.value },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      addSlots.value = Array.isArray(r) ? r : [];
    })
    .catch(() => {
      addSlots.value = [];
    });
}

watch([addServiceId, addDate], () => {
  addTime.value = '';
  loadAddSlots();
});

function openAddModal() {
  addServiceId.value = services.value[0]?.id ?? '';
  addDate.value = selectedDate.value;
  addTime.value = '';
  addSlots.value = [];
  addFirstName.value = '';
  addLastName.value = '';
  addPhone.value = '';
  addEmail.value = '';
  addNote.value = '';
  showAddModal.value = true;
  loadAddSlots();
}

async function confirmAdd() {
  if (!addServiceId.value) {
    $toast.show({ summary: 'Chyba', detail: 'Vyberte prosím službu.', severity: 'error' });
    return;
  }
  if (!addTime.value) {
    $toast.show({ summary: 'Chyba', detail: 'Vyberte prosím volný čas.', severity: 'error' });
    return;
  }
  if (!addFirstName.value || !addLastName.value || !addPhone.value) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Vyplňte prosím jméno, příjmení a telefon.',
      severity: 'error',
    });
    return;
  }

  addLoading.value = true;
  const client = useSanctumClient();
  await client('/api/admin/service-booking', {
    method: 'POST',
    body: JSON.stringify({
      service_id: addServiceId.value,
      date: addDate.value,
      time_from: addTime.value,
      first_name: addFirstName.value,
      last_name: addLastName.value,
      phone: addPhone.value,
      email: addEmail.value || null,
      note: addNote.value || null,
    }),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Rezervace byla vytvořena.', severity: 'success' });
      showAddModal.value = false;
      loadDayBookings();
      loadMonthBookings();
    })
    .catch((e) => {
      const msg = e?.data?.message || 'Nepodařilo se vytvořit rezervaci.';
      $toast.show({ summary: 'Chyba', detail: msg, severity: 'error' });
    })
    .finally(() => {
      addLoading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadServices();
  loadMonthBookings();
  loadDayBookings();
});

useHead({ title: pageTitle.value });
onMounted(() => {
  loadServices();
  loadMonthBookings();
  loadDayBookings();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader :title="pageTitle" :breadcrumbs="breadcrumbs" slug="services" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Calendar -->
      <div class="space-y-4">
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
            </div>
            <button
              type="button"
              class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
              @click="goToday"
            >
              Dnes
            </button>
          </div>
        </LayoutContainer>

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
              class="min-h-[90px] cursor-pointer border-b border-r border-slate-100 p-1.5 transition hover:bg-slate-50"
              :class="{
                'bg-white': day.inMonth && day.dateStr !== todayStr && !holidayName(day.dateStr),
                'bg-slate-50/50': !day.inMonth,
                'bg-indigo-50/60': day.dateStr === todayStr,
                'bg-rose-50/70':
                  day.inMonth && day.dateStr !== todayStr && holidayName(day.dateStr),
                'ring-2 ring-inset ring-indigo-500': day.dateStr === selectedDate,
              }"
              :title="holidayName(day.dateStr)"
              @click="selectDay(day.dateStr)"
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
              <div
                v-if="holidayName(day.dateStr)"
                class="truncate text-center text-[9px] font-medium text-rose-500"
              >
                {{ holidayName(day.dateStr) }}
              </div>
              <div v-if="bookingCounts[day.dateStr]" class="mt-1 flex justify-center">
                <span
                  class="inline-flex items-center rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold text-white"
                >
                  {{ bookingCounts[day.dateStr] }}
                </span>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </div>

      <!-- Day detail -->
      <div class="space-y-4">
        <LayoutContainer class="!py-4">
          <div class="flex items-center justify-between gap-2">
            <h2 class="text-lg font-bold text-slate-900">
              Rezervace — {{ selectedDateFormatted }}
            </h2>
            <button
              type="button"
              class="flex shrink-0 items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
              @click="openAddModal"
            >
              <PlusIcon class="size-4" />
              Přidat rezervaci
            </button>
          </div>
        </LayoutContainer>

        <div v-if="dayLoading" class="flex items-center justify-center py-12">
          <svg
            class="h-6 w-6 animate-spin text-indigo-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
        </div>

        <LayoutContainer v-else-if="!dayBookings.length" class="text-center text-sm text-slate-500">
          Žádné rezervace na tento den.
        </LayoutContainer>

        <div v-else class="space-y-3">
          <LayoutContainer v-for="booking in dayBookings" :key="booking.id" class="!py-4">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="font-bold text-slate-900">
                    {{ booking.first_name }} {{ booking.last_name }}
                  </span>
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                    :class="statusMap[booking.status]?.class || 'bg-slate-100 text-slate-600'"
                  >
                    {{ statusMap[booking.status]?.label || booking.status }}
                  </span>
                </div>

                <div class="mt-1 flex items-center gap-1 text-sm text-slate-600">
                  <ClockIcon class="size-4" />
                  {{ booking.time_from?.slice(0, 5) }}
                  <template v-if="booking.time_to">– {{ booking.time_to.slice(0, 5) }}</template>
                  <span class="text-slate-400">· {{ booking.service_name }}</span>
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                  <a
                    v-if="booking.phone"
                    :href="'tel:' + booking.phone"
                    class="flex items-center gap-1 text-indigo-600 hover:text-indigo-500"
                  >
                    <PhoneIcon class="size-4" />
                    {{ booking.phone }}
                  </a>
                  <a
                    v-if="booking.email"
                    :href="'mailto:' + booking.email"
                    class="flex items-center gap-1 text-indigo-600 hover:text-indigo-500"
                  >
                    <EnvelopeIcon class="size-4" />
                    {{ booking.email }}
                  </a>
                </div>

                <div
                  v-if="booking.note"
                  class="mt-2 flex items-start gap-1.5 text-sm text-slate-500"
                >
                  <ChatBubbleLeftIcon class="mt-0.5 size-4 shrink-0" />
                  <span>{{ booking.note }}</span>
                </div>
              </div>

              <div class="flex shrink-0 flex-col gap-2">
                <button
                  v-if="['pending', 'confirmed'].includes(booking.status)"
                  type="button"
                  class="flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200"
                  @click="openMoveModal(booking)"
                >
                  <ArrowsRightLeftIcon class="size-4" />
                  Přesunout
                </button>
                <button
                  v-if="['pending', 'confirmed'].includes(booking.status)"
                  type="button"
                  class="flex items-center gap-1 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200"
                  @click="completeBooking(booking)"
                >
                  <CheckCircleIcon class="size-4" />
                  Hotovo
                </button>
                <button
                  v-if="['pending', 'confirmed'].includes(booking.status)"
                  type="button"
                  class="flex items-center gap-1 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-200"
                  @click="cancelBooking(booking)"
                >
                  <XCircleIcon class="size-4" />
                  Zrušit
                </button>
              </div>
            </div>
          </LayoutContainer>
        </div>
      </div>
    </div>

    <!-- Move modal -->
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
          v-if="showMoveModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showMoveModal = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">Přesunout rezervaci</h3>
            <div v-if="moveBooking" class="mb-4 text-sm text-slate-500">
              {{ moveBooking.first_name }} {{ moveBooking.last_name }} —
              {{ moveBooking.service_name }}
            </div>

            <div class="space-y-4">
              <BaseFormInput v-model="moveDate" label="Nové datum" type="date" name="move_date" />

              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Volný čas</label>
                <select
                  v-model="moveTime"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="" disabled>-- Vyberte čas --</option>
                  <option v-for="slot in moveSlots" :key="slot" :value="slot">{{ slot }}</option>
                </select>
                <p v-if="!moveSlots.length" class="mt-1 text-xs text-slate-400">
                  Pro vybraný den nejsou dostupné žádné volné časy.
                </p>
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  @click="showMoveModal = false"
                >
                  Zrušit
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                  :disabled="moveLoading"
                  @click="confirmMove"
                >
                  Potvrdit přesun
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Add booking modal -->
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
          v-if="showAddModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
          @click.self="showAddModal = false"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="mb-4 text-lg font-bold text-slate-900">Přidat rezervaci</h3>

            <div class="space-y-4">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Služba</label>
                <select
                  v-model="addServiceId"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="" disabled>-- Vyberte službu --</option>
                  <option v-for="service in services" :key="service.id" :value="service.id">
                    {{ service.name }}
                  </option>
                </select>
              </div>

              <BaseFormInput v-model="addDate" label="Datum" type="date" name="add_date" />

              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Volný čas</label>
                <select
                  v-model="addTime"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="" disabled>-- Vyberte čas --</option>
                  <option v-for="slot in addSlots" :key="slot" :value="slot">{{ slot }}</option>
                </select>
                <p v-if="!addSlots.length" class="mt-1 text-xs text-slate-400">
                  Pro vybranou službu a den nejsou dostupné žádné volné časy.
                </p>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <BaseFormInput v-model="addFirstName" label="Jméno" name="add_first_name" />
                <BaseFormInput v-model="addLastName" label="Příjmení" name="add_last_name" />
              </div>

              <div class="grid grid-cols-2 gap-3">
                <BaseFormInput v-model="addPhone" label="Telefon" name="add_phone" />
                <BaseFormInput v-model="addEmail" label="E-mail" name="add_email" />
              </div>

              <BaseFormTextarea v-model="addNote" label="Poznámka" name="add_note" />

              <div class="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  @click="showAddModal = false"
                >
                  Zrušit
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                  :disabled="addLoading"
                  @click="confirmAdd"
                >
                  Vytvořit rezervaci
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
