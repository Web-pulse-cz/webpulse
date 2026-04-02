<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { useActivityStore } from '~~/stores/activityStore';

const activityStore = useActivityStore();

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová aktivita' : 'Detail aktivity');

const breadcrumbs = ref([
  {
    name: 'Aktivity',
    link: '/nastaveni/aktivity',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/aktivity/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string | null,
  description: '' as string,
  is_business: false as boolean,
  is_personal: false as boolean,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string | null;
    description: string | null;
    is_business: boolean;
    is_personal: boolean;
  }>('/api/admin/activity/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = item.value.name;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/aktivity/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst aktivitu. Zkuste to prosím později.',
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

  await client<{
    id: number | null;
    name: string;
    color: string | null;
    description: string | null;
    is_business: boolean;
    is_personal: boolean;
  }>(
    route.params.id === 'pridat' ? '/api/admin/activity' : '/api/admin/activity/' + route.params.id,
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
            ? 'Aktivita byla úspěšně vytvořena.'
            : 'Aktivita byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/nastaveni/aktivity/${response.id}`);
      } else if (redirect) {
        router.push('/nastaveni/aktivity');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} aktivitu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      activityStore.fetchActivities();
    });
}

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
      slug="activities"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <QueueListIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Základní informace</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <BaseFormInput
                v-model="item.name"
                label="Název aktivity"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. Stříhání, Poradenství, Administrativa..."
              />

              <BaseFormTextarea
                v-model="item.description"
                label="Interní popis"
                name="description"
                rules="min:3"
                rows="5"
                placeholder="Doplňující informace o tomto typu aktivity..."
              />
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2 text-slate-400">
              <SwatchIcon class="size-4" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest"
                >Vizuální styl</LayoutTitle
              >
            </div>

            <BaseFormColorPicker
              v-model="item.color"
              label="Barva v kalendáři"
              name="color"
              rules="min:3"
              class="w-full"
            />
            <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
              Touto barvou se budou zobrazovat bloky v rozvrhu a grafech.
            </p>
          </LayoutContainer>

          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2 text-slate-400">
              <Square3Stack3DIcon class="size-4" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest">Klasifikace</LayoutTitle>
            </div>

            <div class="space-y-3">
              <div
                class="rounded-xl bg-slate-50 p-3 ring-1 ring-inset ring-slate-200 transition-all hover:bg-white hover:shadow-sm"
              >
                <BaseFormCheckbox
                  v-model="item.is_business"
                  :checked="item.is_business"
                  label="Byznys / Klientská"
                  name="is_business"
                  class="flex-row-reverse justify-between font-medium text-slate-700"
                />
              </div>

              <div
                class="rounded-xl bg-slate-50 p-3 ring-1 ring-inset ring-slate-200 transition-all hover:bg-white hover:shadow-sm"
              >
                <BaseFormCheckbox
                  v-model="item.is_personal"
                  :checked="item.is_personal"
                  label="Osobní / Volno"
                  name="is_personal"
                  class="flex-row-reverse justify-between font-medium text-slate-700"
                />
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
            <h4 class="text-[10px] font-black uppercase tracking-widest text-amber-900">
              Nápověda
            </h4>
            <p class="mt-2 text-xs leading-relaxed text-amber-800/80">
              Byznys aktivity se započítávají do statistik výnosů, zatímco osobní aktivity blokují
              čas v kalendáři bez vlivu na tržby.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
