<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová rezervace' : 'Detail rezervace');
const breadcrumbs = ref([
  { name: 'Rezervace', link: '/restaurace/rezervace', current: false },
  { name: pageTitle.value, link: '/restaurace/rezervace/pridat', current: true },
]);

const tables = ref([]);

const item = ref({
  id: null,
  table_id: null,
  date: '',
  time_from: '',
  time_to: '',
  guest_first_name: '',
  guest_last_name: '',
  guest_phone_prefix: '+420',
  guest_phone: '',
  guest_email: '',
  guests_count: 2,
  customer_id: null,
  is_registered_customer: false,
  status: 'pending',
  source: 'manual',
  note: '',
});

const statusOptions = ref([
  { value: 'pending', name: 'Čeká na potvrzení' },
  { value: 'confirmed', name: 'Potvrzeno' },
  { value: 'seated', name: 'Usazení' },
  { value: 'completed', name: 'Dokončeno' },
  { value: 'cancelled', name: 'Zrušeno' },
  { value: 'no_show', name: 'Nedorazili' },
]);

const sourceOptions = ref([
  { value: 'manual', name: 'Manuální' },
  { value: 'web', name: 'Web' },
  { value: 'phone', name: 'Telefon' },
  { value: 'email', name: 'E-mail' },
]);

const statusColors: Record<string, string> = {
  pending: 'bg-amber-100 text-amber-700',
  confirmed: 'bg-blue-100 text-blue-700',
  seated: 'bg-emerald-100 text-emerald-700',
  completed: 'bg-slate-100 text-slate-600',
  cancelled: 'bg-red-100 text-red-700',
  no_show: 'bg-red-50 text-red-500',
};

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/food/reservation/' + route.params.id, {
    method: 'GET',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((r) => {
      item.value = r;
      pageTitle.value = r.guest_full_name + ' — ' + r.date;
      breadcrumbs.value[1] = {
        name: pageTitle.value,
        link: '/restaurace/rezervace/' + route.params.id,
        current: true,
      };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadTables() {
  const client = useSanctumClient();
  await client('/api/admin/food/table', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      const d = r?.data || r;
      tables.value = d.map((t: any) => ({
        value: t.id,
        name: 'Stůl ' + t.number + (t.name ? ' — ' + t.name : '') + ' (' + t.seats + ' míst)',
      }));
    })
    .catch(() => {});
}

async function saveItem(redirect = true) {
  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat'
      ? '/api/admin/food/reservation'
      : '/api/admin/food/reservation/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({ summary: 'Hotovo', detail: 'Rezervace uložena.', severity: 'success' });
      if (!redirect && route.params.id === 'pridat') router.push('/restaurace/rezervace/' + r.id);
      else if (redirect) router.push('/restaurace/rezervace');
      else loadItem();
    })
    .catch((e) => {
      const msg = e?.data?.message || 'Nepodařilo se uložit rezervaci.';
      $toast.show({ summary: 'Chyba', detail: msg, severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function updateStatus(newStatus: string) {
  const client = useSanctumClient();
  await client('/api/admin/food/reservation/' + route.params.id + '/status', {
    method: 'POST',
    body: JSON.stringify({ status: newStatus }),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  }).then((r) => {
    item.value = r;
    $toast.show({ summary: 'Hotovo', detail: 'Stav rezervace aktualizován.', severity: 'success' });
  });
}

useHead({ title: pageTitle.value });
onMounted(() => {
  loadTables();
  if (route.params.id !== 'pridat') {
    loadItem();
  } else if (route.query.table_id) {
    item.value.table_id = Number(route.query.table_id);
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
      slug="reservations"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
              >
                <CalendarDaysIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Rezervace</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
              <BaseFormSelect
                v-model="item.table_id"
                label="Stůl"
                name="table_id"
                rules="required"
                :options="tables"
                class="col-span-full sm:col-span-1"
              />
              <BaseFormInput
                v-model="item.date"
                label="Datum"
                type="date"
                name="date"
                rules="required"
              />
              <BaseFormInput
                v-model="item.time_from"
                label="Čas od"
                type="time"
                name="time_from"
                rules="required"
              />
              <BaseFormInput v-model="item.time_to" label="Čas do" type="time" name="time_to" />
              <BaseFormInput
                v-model="item.guests_count"
                label="Počet hostů"
                type="number"
                name="guests_count"
                :min="1"
              />
              <BaseFormSelect
                v-model="item.source"
                label="Zdroj"
                name="source"
                :options="sourceOptions"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UserIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Údaje hosta</LayoutTitle>
            </div>

            <div
              v-if="item.is_registered_customer"
              class="mb-4 rounded-xl bg-indigo-50 p-4 ring-1 ring-indigo-200"
            >
              <div class="flex items-center gap-2">
                <span
                  class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold text-white"
                  >Registrovaný zákazník</span
                >
                <NuxtLink
                  v-if="item.customer_id"
                  :to="'/zakaznici/' + item.customer_id"
                  class="text-xs font-medium text-indigo-600 hover:text-indigo-500"
                >
                  Zobrazit profil &rarr;
                </NuxtLink>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.guest_first_name"
                label="Jméno"
                name="guest_first_name"
                rules="required"
              />
              <BaseFormInput
                v-model="item.guest_last_name"
                label="Příjmení"
                name="guest_last_name"
                rules="required"
              />
              <div class="flex gap-3">
                <BaseFormInput
                  v-model="item.guest_phone_prefix"
                  label="Předčíslí"
                  name="phone_prefix"
                  class="w-24"
                />
                <BaseFormInput
                  v-model="item.guest_phone"
                  label="Telefon"
                  name="guest_phone"
                  class="flex-1"
                />
              </div>
              <BaseFormInput
                v-model="item.guest_email"
                label="E-mail"
                type="email"
                name="guest_email"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <LayoutTitle>Poznámka</LayoutTitle>
            <BaseFormTextarea
              v-model="item.note"
              label=""
              name="note"
              rows="3"
              placeholder="Speciální požadavky, alergie, oslava..."
            />
          </LayoutContainer>
        </div>

        <div class="col-span-1 space-y-6 lg:sticky lg:top-24 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <LayoutTitle class="text-sm uppercase tracking-widest text-slate-400"
              >Stav rezervace</LayoutTitle
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

            <div v-if="item.id" class="mt-4 space-y-2 border-t border-slate-100 pt-4">
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                Rychlá akce
              </p>
              <button
                v-if="item.status === 'pending'"
                type="button"
                class="w-full rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-500"
                @click="updateStatus('confirmed')"
              >
                Potvrdit
              </button>
              <button
                v-if="item.status === 'confirmed'"
                type="button"
                class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-500"
                @click="updateStatus('seated')"
              >
                Usadit
              </button>
              <button
                v-if="item.status === 'seated'"
                type="button"
                class="w-full rounded-lg bg-slate-600 px-3 py-2 text-xs font-medium text-white hover:bg-slate-500"
                @click="updateStatus('completed')"
              >
                Dokončit
              </button>
              <button
                v-if="['pending', 'confirmed'].includes(item.status)"
                type="button"
                class="w-full rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-200"
                @click="updateStatus('cancelled')"
              >
                Zrušit
              </button>
              <button
                v-if="['pending', 'confirmed'].includes(item.status)"
                type="button"
                class="w-full rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-500 hover:bg-red-100"
                @click="updateStatus('no_show')"
              >
                Nedorazili
              </button>
            </div>
          </LayoutContainer>
        </div>
      </div>
    </Form>
  </div>
</template>
