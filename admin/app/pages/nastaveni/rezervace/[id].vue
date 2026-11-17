<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { ClockIcon, CalendarDaysIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const { formRef, validateForm } = useFormValidation();
const loading = ref(false);

const pageTitle = ref('Nastavení rezervací');
const breadcrumbs = ref([
  { name: 'Nastavení rezervací', link: '/nastaveni/rezervace', current: false },
  { name: pageTitle.value, link: '/nastaveni/rezervace/' + route.params.id, current: true },
]);

const weekDays = [
  { value: 1, name: 'Po' },
  { value: 2, name: 'Út' },
  { value: 3, name: 'St' },
  { value: 4, name: 'Čt' },
  { value: 5, name: 'Pá' },
  { value: 6, name: 'So' },
  { value: 7, name: 'Ne' },
];

const item = ref({
  work_start: '09:00',
  work_end: '17:00',
  slot_duration_minutes: 30,
  break_minutes: 0,
  working_days: [1, 2, 3, 4, 5] as number[],
  active: true,
});

const { markClean } = useDirtyGuard(item);

interface ExceptionRow {
  id: number | null;
  date: string;
  is_closed: boolean;
  custom_times_text: string;
}

const exceptions = ref<ExceptionRow[]>([]);

function toggleWorkingDay(value: number) {
  if (item.value.working_days.includes(value)) {
    item.value.working_days = item.value.working_days.filter((d) => d !== value);
  } else {
    item.value.working_days = [...item.value.working_days, value].sort();
  }
}

function addException() {
  exceptions.value.push({ id: null, date: '', is_closed: true, custom_times_text: '' });
}

function removeException(index: number) {
  exceptions.value.splice(index, 1);
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/service-booking-setting/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      pageTitle.value = 'Nastavení rezervací — ' + r.service_name;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/nastaveni/rezervace/' + route.params.id,
        current: true,
      };

      const setting = r.booking_setting;
      if (setting) {
        item.value = {
          work_start: setting.work_start?.slice(0, 5) ?? '09:00',
          work_end: setting.work_end?.slice(0, 5) ?? '17:00',
          slot_duration_minutes: setting.slot_duration_minutes ?? 30,
          break_minutes: setting.break_minutes ?? 0,
          working_days:
            setting.working_days && setting.working_days.length
              ? setting.working_days
              : [1, 2, 3, 4, 5],
          active: !!setting.active,
        };
        exceptions.value = (setting.exceptions || []).map((e: any) => ({
          id: e.id,
          date: e.date,
          is_closed: !!e.is_closed,
          custom_times_text: Array.isArray(e.custom_times) ? e.custom_times.join(', ') : '',
        }));
      }
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst nastavení rezervací.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      markClean();
    });
}

async function saveItem() {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  const payload = {
    work_start: item.value.work_start,
    work_end: item.value.work_end,
    slot_duration_minutes: item.value.slot_duration_minutes,
    break_minutes: item.value.break_minutes,
    working_days: item.value.working_days,
    active: item.value.active,
    exceptions: exceptions.value
      .filter((e) => e.date)
      .map((e) => ({
        date: e.date,
        is_closed: e.is_closed,
        custom_times: e.is_closed
          ? null
          : e.custom_times_text
              .split(',')
              .map((t) => t.trim())
              .filter(Boolean),
      })),
  };

  await client('/api/admin/service-booking-setting/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(payload),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then(() => {
      markClean();
      $toast.show({
        summary: 'Hotovo',
        detail: 'Nastavení rezervací bylo uloženo.',
        severity: 'success',
      });
      loadItem();
    })
    .catch((e) => {
      const msg = e?.data?.message || 'Nepodařilo se uložit nastavení rezervací.';
      $toast.show({ summary: 'Chyba', detail: msg, severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({
  title: pageTitle,
});
onMounted(() => {
  loadItem();
  if (route.params.id === 'pridat') markClean();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-leave' }]"
      slug="service_booking_settings"
      :saving="loading"
      @save="saveItem"
    />
    <div
      v-if="loading && !item.id && route.params.id !== 'pridat'"
      class="grid animate-pulse grid-cols-1 gap-8 lg:grid-cols-12"
      aria-busy="true"
    >
      <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-9">
        <div class="h-5 w-1/3 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-24 rounded bg-slate-100"></div>
      </div>
      <div class="h-64 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 lg:col-span-3"></div>
    </div>
    <Form v-else ref="formRef" @submit="() => saveItem()">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <ClockIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Pracovní doba a sloty</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <BaseFormInput
                v-model="item.work_start"
                label="Pracovní doba od"
                type="time"
                name="work_start"
                rules="required"
              />
              <BaseFormInput
                v-model="item.work_end"
                label="Pracovní doba do"
                type="time"
                name="work_end"
                rules="required"
              />
              <BaseFormInput
                v-model="item.slot_duration_minutes"
                label="Délka slotu (min)"
                type="number"
                name="slot_duration_minutes"
                rules="required|min:1"
                :min="1"
              />
              <BaseFormInput
                v-model="item.break_minutes"
                label="Pauza mezi sloty (min)"
                type="number"
                name="break_minutes"
                :min="0"
              />
            </div>

            <div class="mt-6">
              <label class="mb-2 block text-xs font-medium text-slate-700">Pracovní dny</label>
              <div class="flex flex-wrap gap-2">
                <label
                  v-for="day in weekDays"
                  :key="day.value"
                  class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm transition-colors"
                  :class="
                    item.working_days.includes(day.value)
                      ? 'border-indigo-200 bg-indigo-50 text-indigo-700'
                      : 'text-slate-600 hover:bg-slate-50'
                  "
                >
                  <input
                    type="checkbox"
                    class="rounded text-indigo-600"
                    :checked="item.working_days.includes(day.value)"
                    @change="toggleWorkingDay(day.value)"
                  />
                  {{ day.name }}
                </label>
              </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
              <BaseFormSwitch
                v-model:enabled="item.active"
                disabled-text="Neaktivní"
                enabled-text="Aktivní"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                >
                  <CalendarDaysIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Výjimky</LayoutTitle>
              </div>
              <button
                type="button"
                class="flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-500"
                @click="addException"
              >
                <PlusIcon class="size-4" />
                Přidat výjimku
              </button>
            </div>

            <p v-if="!exceptions.length" class="text-sm text-slate-500">
              Žádné výjimky nejsou nastaveny.
            </p>

            <div v-else class="space-y-3">
              <div
                v-for="(exception, index) in exceptions"
                :key="index"
                class="rounded-xl border border-slate-200 p-4"
              >
                <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-12">
                  <div class="sm:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-slate-700">Datum</label>
                    <input
                      v-model="exception.date"
                      type="date"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                  </div>
                  <div class="sm:col-span-3">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                      <input
                        v-model="exception.is_closed"
                        type="checkbox"
                        class="rounded text-indigo-600"
                      />
                      Zavřeno celý den
                    </label>
                  </div>
                  <div class="sm:col-span-5">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                      Vlastní časy (odděleno čárkou, např. 09:00, 10:30, 13:00)
                    </label>
                    <input
                      v-model="exception.custom_times_text"
                      type="text"
                      :disabled="exception.is_closed"
                      placeholder="09:00, 10:30, 13:00"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-400"
                    />
                  </div>
                  <div class="flex justify-end sm:col-span-1">
                    <button
                      type="button"
                      class="text-slate-400 transition-colors hover:text-red-500"
                      title="Smazat výjimku"
                      @click="removeException(index)"
                    >
                      <TrashIcon class="size-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </LayoutContainer>
        </div>

        <div class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400">
              Souhrn
            </LayoutTitle>
            <p class="mt-4 text-sm text-slate-600">
              Nastavení určuje, jaké termíny se zákazníkům nabízí v rezervačním widgetu pro tuto
              službu. Sloty se počítají dynamicky podle pracovní doby, délky slotu, pauzy a
              nastavených výjimek.
            </p>
          </LayoutContainer>
        </div>
      </div>
    </Form>
  </div>
</template>
