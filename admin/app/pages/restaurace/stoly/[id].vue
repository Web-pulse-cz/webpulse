<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { BuildingOfficeIcon, CalendarDaysIcon, UserIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();
const loading = ref(false);

const tabs = ref([
  { name: 'Údaje stolu', link: '#info', current: false },
  { name: 'Rezervace', link: '#rezervace', current: false },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový stůl' : 'Detail stolu');
const breadcrumbs = ref([
  { name: 'Stoly', link: '/restaurace/stoly', current: false },
  { name: pageTitle.value, link: '/restaurace/stoly/pridat', current: true },
]);

const item = ref({
  id: null,
  number: '',
  name: '',
  seats: 2,
  location: '',
  description: '',
  status: 'available',
  position: 0,
  sites: [] as number[],
  upcoming_reservations: [] as any[],
  today_reservations: [] as any[],
  reservations: [] as any[],
});

const statusOptions = ref([
  { value: 'available', name: 'Volný' },
  { value: 'occupied', name: 'Obsazený' },
  { value: 'reserved', name: 'Rezervovaný' },
  { value: 'maintenance', name: 'Údržba' },
]);

const statusColors: Record<string, string> = {
  available: 'bg-emerald-100 text-emerald-700',
  occupied: 'bg-red-100 text-red-700',
  reserved: 'bg-amber-100 text-amber-700',
  maintenance: 'bg-slate-100 text-slate-600',
};

const reservationStatusColors: Record<string, string> = {
  pending: 'bg-amber-100 text-amber-700',
  confirmed: 'bg-blue-100 text-blue-700',
  seated: 'bg-emerald-100 text-emerald-700',
  completed: 'bg-slate-100 text-slate-600',
  cancelled: 'bg-red-100 text-red-700',
  no_show: 'bg-red-50 text-red-500',
};

const reservationStatusLabels: Record<string, string> = {
  pending: 'Čeká',
  confirmed: 'Potvrzeno',
  seated: 'Usazení',
  completed: 'Dokončeno',
  cancelled: 'Zrušeno',
  no_show: 'Nedorazili',
};

// Filter for reservations tab
const reservationFilter = ref('upcoming'); // upcoming, today, all

const filteredReservations = computed(() => {
  switch (reservationFilter.value) {
    case 'today':
      return item.value.today_reservations || [];
    case 'all':
      return item.value.reservations || [];
    default:
      return item.value.upcoming_reservations || [];
  }
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/food/table/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = { ...r, sites: r.sites || [] };
      pageTitle.value = 'Stůl ' + r.number + (r.name ? ' — ' + r.name : '');
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/restaurace/stoly/' + route.params.id,
        current: true,
      };
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst stůl.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/table'
      : '/api/admin/food/table/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({ summary: 'Hotovo', detail: 'Stůl uložen.', severity: 'success' });
      if (!redirect && route.params.id === 'pridat') router.push('/restaurace/stoly/' + r.id);
      else if (redirect) router.push('/restaurace/stoly');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit stůl.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

watchEffect(() => {
  const h = route.hash;
  if (h)
    tabs.value.forEach((t) => {
      t.current = t.link === h;
    });
  else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
});

watch(selectedSiteHash, () => {
  loadItem();
});
useHead({ title: pageTitle.value });
onMounted(() => {
  if (route.params.id !== 'pridat') loadItem();
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
      slug="restaurant_tables"
      @save="saveItem"
    />
    <LayoutTabs :tabs="tabs" class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-md" />

    <Form @submit="saveItem">
      <!-- Info -->
      <template v-if="tabs.find((t) => t.current && t.link === '#info')">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
          <div class="col-span-1 space-y-8 lg:col-span-9">
            <LayoutContainer>
              <div class="mb-6 flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <BuildingOfficeIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Údaje stolu</LayoutTitle>
              </div>
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.number"
                  label="Číslo stolu"
                  name="number"
                  rules="required"
                  placeholder="Např. 1, A3, T-5"
                />
                <BaseFormInput
                  v-model="item.name"
                  label="Název (volitelný)"
                  name="name"
                  placeholder="Např. VIP box, Okno u zahrady"
                />
                <BaseFormInput
                  v-model="item.seats"
                  label="Počet míst"
                  type="number"
                  name="seats"
                  :min="1"
                />
                <BaseFormInput
                  v-model="item.location"
                  label="Umístění"
                  name="location"
                  placeholder="Např. terasa, salonek, bar"
                />
                <BaseFormTextarea
                  v-model="item.description"
                  label="Popis"
                  name="description"
                  rows="3"
                  class="col-span-full"
                />
                <BaseFormInput
                  v-model="item.position"
                  label="Pořadí"
                  type="number"
                  name="position"
                />
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
                  v-model="item.status"
                  label=""
                  name="status"
                  :options="statusOptions"
                />
              </div>
              <div class="mt-3 text-center">
                <span
                  class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                  :class="statusColors[item.status] || 'bg-slate-100 text-slate-600'"
                >
                  {{ statusOptions.find((s) => s.value === item.status)?.name || item.status }}
                </span>
              </div>
            </LayoutContainer>
            <LayoutActionsDetailBlock
              v-model:sites="item.sites"
              :allow-image="false"
              :allow-is-active="false"
            />
          </div>
        </div>
      </template>

      <!-- Rezervace -->
      <template v-if="tabs.find((t) => t.current && t.link === '#rezervace')">
        <LayoutContainer>
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
              >
                <CalendarDaysIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Rezervace</LayoutTitle>
            </div>
            <div class="flex gap-2">
              <NuxtLink
                :to="'/restaurace/rezervace/pridat?table_id=' + item.id"
                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-indigo-500"
              >
                Nová rezervace
              </NuxtLink>
              <button
                v-for="f in [
                  { key: 'upcoming', label: 'Nadcházející' },
                  { key: 'today', label: 'Dnes' },
                  { key: 'all', label: 'Všechny' },
                ]"
                :key="f.key"
                type="button"
                class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                :class="
                  reservationFilter === f.key
                    ? 'bg-indigo-600 text-white'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                "
                @click="reservationFilter = f.key"
              >
                {{ f.label }}
              </button>
            </div>
          </div>

          <div v-if="!filteredReservations.length" class="py-12 text-center text-sm text-slate-400">
            Žádné rezervace.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="res in filteredReservations"
              :key="res.id"
              class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
              :class="res.is_registered_customer ? 'ring-1 ring-indigo-200' : ''"
            >
              <div class="flex items-center gap-4">
                <div
                  class="flex size-10 items-center justify-center rounded-lg"
                  :class="
                    res.is_registered_customer
                      ? 'bg-indigo-50 text-indigo-600'
                      : 'bg-slate-100 text-slate-500'
                  "
                >
                  <UserIcon class="size-5" />
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-900">{{
                      res.guest_full_name
                    }}</span>
                    <span
                      v-if="res.is_registered_customer"
                      class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700"
                    >
                      Registrovaný zákazník
                    </span>
                    <span
                      class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                      :class="reservationStatusColors[res.status]"
                    >
                      {{ reservationStatusLabels[res.status] || res.status }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-400">
                    {{ res.date }} &middot; {{ res.time_from?.slice(0, 5)
                    }}<span v-if="res.time_to"> — {{ res.time_to?.slice(0, 5) }}</span> &middot;
                    {{ res.guests_count }}
                    {{ res.guests_count === 1 ? 'host' : res.guests_count < 5 ? 'hosté' : 'hostů' }}
                    <template v-if="res.guest_phone || res.guest_email">
                      &middot;
                      {{ res.guest_phone ? res.guest_phone_prefix + ' ' + res.guest_phone : '' }}
                      {{ res.guest_email ? ' · ' + res.guest_email : '' }}
                    </template>
                  </p>
                </div>
              </div>
              <NuxtLink
                :to="'/restaurace/rezervace/' + res.id"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-500"
              >
                Detail
              </NuxtLink>
            </div>
          </div>
        </LayoutContainer>
      </template>
    </Form>
  </div>
</template>
