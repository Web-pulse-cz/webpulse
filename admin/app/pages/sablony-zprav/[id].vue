<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);

const allowedTypes = ref([
  { value: 'E-mail', name: 'E-mail' },
  { value: 'SMS', name: 'SMS' },
  { value: 'LinkedIn', name: 'LinkedIn' },
  { value: 'Messenger', name: 'Messenger' },
  { value: 'Facebook', name: 'Facebook' },
  { value: 'Instagram', name: 'Instagram' },
]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová šablona' : 'Detail šablony');

const breadcrumbs = ref([
  {
    name: 'Šablony zpráv',
    link: '/sablony-zprav',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/sablony-zprav/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  message: '' as string,
  type: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    message: string;
    type: string;
  }>('/api/admin/message/blueprint/' + route.params.id, {
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
        link: '/sablony-zprav/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fázi procesu. Zkuste to prosím později.',
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
    message: string;
    type: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/message/blueprint'
      : '/api/admin/message/blueprint/' + route.params.id,
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
            ? 'Šablona zpráv byla úspěšně vytvořena.'
            : 'Šablona zpráv byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/sablony-zprav/' + response.id);
      } else if (redirect) {
        router.push('/sablony-zprav');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit šablonu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  <div class="space-y-6">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="message_blueprints"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 gap-8">
        <LayoutContainer class="w-full">
          <div class="mb-8 flex items-center justify-between">
            <LayoutTitle class="!mb-0">Nastavení šablony</LayoutTitle>
            <div class="flex items-center gap-2">
              <span class="flex size-2 rounded-full bg-indigo-500"></span>
              <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400"
                >Editor zpráv</span
              >
            </div>
          </div>

          <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
            <div class="col-span-1">
              <BaseFormInput
                v-model="item.name"
                label="Název šablony"
                placeholder="Např. Poděkování za návštěvu"
                type="text"
                name="name"
                rules="required|min:3"
              />
              <p class="mt-1.5 text-xs text-slate-400">
                Interní název pro vaši orientaci v seznamu.
              </p>
            </div>

            <div class="col-span-1">
              <BaseFormSelect
                v-model="item.type"
                label="Typ kanálu"
                name="type"
                :options="allowedTypes"
                rules="required"
              />
              <p class="mt-1.5 text-xs text-slate-400">
                Určuje, pro jaký druh komunikace je šablona určena.
              </p>
            </div>

            <div class="col-span-full pt-4">
              <div class="rounded-2xl bg-slate-50 p-1 ring-1 ring-inset ring-slate-200">
                <BaseFormTextarea
                  v-model="item.message"
                  label="Text zprávy"
                  name="message"
                  rules="required"
                  placeholder="Zde napište text vaší šablony..."
                  :rows="12"
                  :max="5000"
                  class="col-span-full border-none bg-white !shadow-none focus:ring-0"
                />
              </div>
            </div>
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
