<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

interface Project {
  id: number | null;
  name: string;
  description: string;
  note: string;
  image: string;
  hourly_rate: number;
  expected_price: number;
  expected_price_vat: number;
  expected_hours: number;
  total_price: number;
  total_price_vat: number;
  total_hours: number;
  start_date: string;
  formatted_start_date: string;
  end_date: string;
  formatted_end_date: string;
  invoice_firstname: string;
  invoice_lastname: string;
  invoice_email: string;
  invoice_phone_prefix: string;
  invoice_phone: string;
  invoice_street: string;
  invoice_city: string;
  invoice_zip: string;
  invoice_country_id: number | null;
  is_delivery_address_same: boolean;
  invoice_ico: string;
  invoice_dic: string;
  delivery_firstname: string;
  delivery_lastname: string;
  delivery_email: string;
  delivery_phone_prefix: string;
  delivery_phone: string;
  delivery_street: string;
  delivery_city: string;
  delivery_zip: string;
  delivery_country_id: number | null;
  currency_id: number | null;
  client_id: number | null;
  tax_rate_id: number | null;
  status_id: number | null;
}

const statuses = ref([]);

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const tabs = ref([
  { name: 'Základní údaje', link: '#info', current: true },
  { name: 'Cenotvorba', link: '#cenotvorba', current: false },
]);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová cenová nabídka' : 'Detail cenové nabídky',
);

const breadcrumbs = ref([
  {
    name: 'Cenové nabídky',
    link: '/cenove-nabidky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/cenove-nabidky/pridat',
    current: true,
  },
]);

const item = ref({} as Project);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>('/api/admin/price-offer/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/cenove-nabidky/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst cenovou nabídku. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  const client = useSanctumClient();
  loading.value = true;

  await client<Project>(
    route.params.id === 'pridat'
      ? '/api/admin/price-offer'
      : '/api/admin/price-offer/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Cenová nabídka byla úspěšně vytvořena.'
            : 'Cenová nabídka byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/cenove-nabidky/' + response.id);
      } else if (redirect) {
        router.push('/cenove-nabidky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit cenovou nabídku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watchEffect(() => {
  const routeTabHash = route.hash;
  if (routeTabHash && routeTabHash !== '') {
    tabs.value.forEach((tab) => {
      tab.current = tab.link === routeTabHash;
    });
  } else {
    tabs.value[0].current = true;
    router.push(route.path + '#info');
  }
});

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="contacts"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                >
                  <MagnifyingGlassIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Identifikace zdroje</LayoutTitle>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormInput
                v-model="item.name"
                label="Název zdroje"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-1"
                placeholder="Např. Facebook reklama, Osobní doporučení..."
              />

              <BaseFormColorPicker
                v-model="item.color"
                label="Barva pro grafy a štítky"
                name="color"
                class="col-span-1"
              />

              <div
                class="col-span-full mt-4 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60"
              >
                <div class="flex items-center justify-between">
                  <div class="space-y-1">
                    <span class="text-sm font-bold text-slate-900"
                      >Sledování výkonu (Analytics)</span
                    >
                    <p class="text-xs italic text-slate-500">
                      Pokud je aktivní, zdroj se bude započítávat do konverzních přehledů v
                      dashboardu.
                    </p>
                  </div>
                  <BaseFormCheckbox
                    v-model="item.show_in_statistics"
                    :checked="item.show_in_statistics"
                    label="Zobrazovat ve statistikách"
                    name="show_in_statistics"
                    class="flex-row-reverse gap-4 font-bold text-indigo-600"
                  />
                </div>
              </div>
            </div>
          </LayoutContainer>

          <div
            class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-white/50 p-8"
          >
            <span class="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400"
              >Náhled vizuálního stylu</span
            >
            <div
              class="rounded-full px-6 py-2 text-sm font-black shadow-lg transition-all"
              :style="{
                backgroundColor: item.color + '20',
                color: item.color,
                border: `2px solid ${item.color}40`,
              }"
            >
              {{ item.name || 'Název zdroje' }}
            </div>
          </div>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:position="item.position"
            :allow-image="false"
            :allow-sites="false"
            :allow-position="true"
            :allow-translations="false"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200">
            <div class="mb-3 flex items-center gap-2">
              <PresentationChartLineIcon class="size-5 text-indigo-200" />
              <h4 class="text-sm font-bold">Marketingový ROI</h4>
            </div>
            <p class="text-xs leading-relaxed opacity-80">
              Správné nastavení zdrojů vám umožní přesně měřit, kolik vás stojí získání jednoho
              zákazníka (CAC). Barvy doporučujeme sladit se schématem vašich reklamních kampaní.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
