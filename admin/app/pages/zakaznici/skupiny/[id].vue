<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { UsersIcon, BanknotesIcon } from '@heroicons/vue/24/outline';
import { useCurrencyStore } from '~/../stores/currencyStore';

const { $toast } = useNuxtApp();
const currencyStore = useCurrencyStore();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová skupina zákazníků' : 'Detail skupiny');
const breadcrumbs = ref([
  { name: 'Zákazníci', link: '/zakaznici', current: false },
  { name: 'Skupiny', link: '/zakaznici/skupiny', current: false },
  { name: pageTitle.value, link: '/zakaznici/skupiny/pridat', current: true },
]);

const item = ref({
  id: null,
  name: '',
  description: '',
  color: '#6366f1',
  discount_type: null as string | null,
  discount_value: 0,
  discount_currency_id: null,
  position: 0,
  sites: [] as number[],
});

const discountTypeOptions = ref([
  { value: 'fixed', name: 'Pevná částka' },
  { value: 'percentage', name: 'Procenta' },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;
  await client('/api/admin/customer/group/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((r) => {
      item.value = { ...r, sites: r.sites || [] };
      pageTitle.value = r.name;
      breadcrumbs.value[2] = { name: r.name, link: '/zakaznici/skupiny/' + r.id, current: true };
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;
  await client(
    route.params.id === 'pridat'
      ? '/api/admin/customer/group'
      : '/api/admin/customer/group/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((r) => {
      $toast.show({ summary: 'Hotovo', detail: 'Skupina uložena.', severity: 'success' });
      if (!redirect && route.params.id === 'pridat') router.push('/zakaznici/skupiny/' + r.id);
      else if (redirect) router.push('/zakaznici/skupiny');
      else loadItem();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit skupinu.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

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
      slug="customers"
      @save="saveItem"
    />
    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UsersIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Údaje skupiny</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <BaseFormInput v-model="item.name" label="Název" name="name" rules="required" />
              <BaseFormColorPicker v-model="item.color" label="Barva" name="color" />
              <BaseFormTextarea
                v-model="item.description"
                label="Popis"
                name="description"
                rows="3"
                class="col-span-full"
              />
              <BaseFormInput v-model="item.position" label="Pořadí" type="number" name="position" />
            </div>
          </LayoutContainer>

          <LayoutContainer class="mt-8">
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <BanknotesIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Stálá sleva</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
              <BaseFormSelect
                v-model="item.discount_type"
                label="Typ slevy"
                name="discount_type"
                :options="discountTypeOptions"
              />
              <BaseFormInput
                v-model="item.discount_value"
                label="Hodnota slevy"
                type="number"
                name="discount_value"
                :step="0.01"
              />
              <BaseFormSelect
                v-if="item.discount_type === 'fixed'"
                v-model="item.discount_currency_id"
                label="Měna"
                name="discount_currency_id"
                :options="currencyStore.currenciesOptions"
              />
            </div>
          </LayoutContainer>
        </div>
        <div class="col-span-1 lg:sticky lg:top-24 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-is-active="false"
            :allow-translations="false"
          />
        </div>
      </div>
    </Form>
  </div>
</template>
