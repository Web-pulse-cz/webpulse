<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { KeyIcon, UserIcon, CurrencyEuroIcon } from '@heroicons/vue/24/outline';
import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();
const currencyStore = useCurrencyStore();
const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová rezervace' : 'Detail rezervace');

const breadcrumbs = ref([
  { name: 'Rezervace ubytování', link: '/ubytovani/rezervace', current: false },
  { name: pageTitle.value, link: '/ubytovani/rezervace/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  code: '' as string,
  apartment_id: null as number | null,
  start_date: '' as string,
  end_date: '' as string,
  status: 'pending' as string,
  source: 'admin' as string,
  guest_firstname: '' as string,
  guest_lastname: '' as string,
  guest_email: '' as string,
  guest_phone: '' as string,
  number_of_guests: 1 as number,
  total_price: 0 as number,
  currency_id: null as number | null,
  notes: '' as string,
  sites: [] as number[],
});

const apartments = ref<{ value: number; name: string }[]>([]);

async function loadApartments() {
  const client = useSanctumClient();
  await client('/api/admin/apartment', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      apartments.value = (Array.isArray(response) ? response : response.data || []).map(
        (a: any) => ({ value: a.id, name: a.name || a.code }),
      );
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst apartmány.',
        severity: 'error',
      });
    });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/apartment/reservation/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      item.value = response;
      item.value.sites = response.sites?.map((s: any) => s.id) || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.code ? `Rezervace ${item.value.code}` : 'Detail rezervace';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/ubytovani/rezervace/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst rezervaci.',
        severity: 'error',
      });
      router.push('/ubytovani/rezervace');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat'
      ? '/api/admin/apartment/reservation'
      : '/api/admin/apartment/reservation/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat' ? 'Rezervace byla vytvořena.' : 'Rezervace byla upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat')
        router.push('/ubytovani/rezervace/' + response.id);
      else if (redirect) router.push('/ubytovani/rezervace');
      else loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit rezervaci.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => {
  loadApartments();
  if (route.params.id !== 'pridat') loadItem();
});

onMounted(() => {
  loadApartments();
  if (route.params.id !== 'pridat') loadItem();
});
definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="apartment_reservations"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <KeyIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Rezervace</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
              <BaseFormSelect
                v-model="item.apartment_id"
                label="Apartmán"
                name="apartment_id"
                rules="required"
                :options="apartments"
                class="sm:col-span-2"
              />
              <BaseFormInput
                v-model="item.start_date"
                label="Od data"
                type="date"
                name="start_date"
                rules="required"
              />
              <BaseFormInput
                v-model="item.end_date"
                label="Do data"
                type="date"
                name="end_date"
                rules="required"
              />
              <BaseFormSelect
                v-model="item.status"
                label="Stav"
                name="status"
                :options="[
                  { value: 'pending', name: 'Čeká na potvrzení' },
                  { value: 'confirmed', name: 'Potvrzeno' },
                  { value: 'completed', name: 'Dokončeno' },
                  { value: 'cancelled', name: 'Zrušeno' },
                ]"
              />
              <BaseFormSelect
                v-model="item.source"
                label="Zdroj"
                name="source"
                :options="[
                  { value: 'admin', name: 'Admin' },
                  { value: 'web', name: 'Webový formulář' },
                  { value: 'booking', name: 'Booking.com' },
                ]"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <UserIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Údaje o hostovi</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
              <BaseFormInput
                v-model="item.guest_firstname"
                label="Jméno"
                type="text"
                name="guest_firstname"
                rules="required"
              />
              <BaseFormInput
                v-model="item.guest_lastname"
                label="Příjmení"
                type="text"
                name="guest_lastname"
                rules="required"
              />
              <BaseFormInput
                v-model="item.guest_email"
                label="E-mail"
                type="email"
                name="guest_email"
              />
              <BaseFormInput
                v-model="item.guest_phone"
                label="Telefon"
                type="text"
                name="guest_phone"
              />
              <BaseFormInput
                v-model="item.number_of_guests"
                label="Počet hostů"
                type="number"
                name="number_of_guests"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600"
              >
                <CurrencyEuroIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Platba a poznámky</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
              <BaseFormInput
                v-model="item.total_price"
                label="Celková cena"
                type="number"
                name="total_price"
              />
              <BaseFormSelect
                v-model="item.currency_id"
                label="Měna"
                name="currency_id"
                :options="
                  currencyStore.currencies?.map((c: any) => ({ value: c.id, name: c.code })) || []
                "
              />
              <BaseFormTextarea
                v-model="item.notes"
                label="Poznámky"
                name="notes"
                class="sm:col-span-2"
              />
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:sites="item.sites"
            :allow-translations="false"
            :allow-image="false"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
