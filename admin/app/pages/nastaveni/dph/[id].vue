<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { ReceiptPercentIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová sazba' : 'Detail sazby');

const breadcrumbs = ref([
  {
    name: 'Sazby DPH',
    link: '/nastaveni/dph',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/dph',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  rate: 0 as number,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    rate: number;
  }>('/api/admin/tax-rate/' + route.params.id, {
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
        link: '/nastaveni/dph/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst sazbu DPH. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    rate: number;
  }>(
    route.params.id === 'pridat' ? '/api/admin/tax-rate' : '/api/admin/tax-rate/' + route.params.id,
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
            ? 'Sazba DPH byla úspěšně vytvořena.'
            : 'Sazba DPH byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/dph/' + response.id + '?created=true');
      } else if (redirect) {
        router.push('/nastaveni/dph');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit sazbu DPH. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
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
      slug="tax_rates"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="space-y-8">
        <LayoutContainer>
          <div class="mb-8 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
            >
              <ReceiptPercentIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Nastavení daňové sazby</LayoutTitle>
          </div>

          <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
            <BaseFormInput
              v-model="item.name"
              label="Název sazby"
              type="text"
              name="name"
              rules="required|min:2"
              placeholder="Např. Základní sazba DPH"
            />

            <div class="relative">
              <BaseFormInput
                v-model="item.rate"
                label="Výše sazby (v %)"
                type="number"
                name="rate"
                min="0"
                rules="required"
                placeholder="21"
              />
              <div
                class="absolute bottom-3 right-4 flex h-6 items-center border-l border-slate-200 pl-3 text-slate-400"
              >
                <span class="text-sm font-bold">%</span>
              </div>
            </div>
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
