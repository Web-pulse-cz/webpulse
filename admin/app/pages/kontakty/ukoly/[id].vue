<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { ArrowPathIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);

const phases = ref([]);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová úkol' : 'Detail úkolu');

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Úkoly',
    link: '/ukoly',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/ukoly/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  contact_phase_id: null as string | null,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    contact_phase_id: string | null;
  }>('/api/admin/contact/task/' + route.params.id, {
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
        link: '/faze/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst úkol. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadPhases() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{ id: number }>('/api/admin/contact/phase', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      response.forEach((phase: { id: number; name: string }) => {
        phases.value.push({
          value: phase.id,
          name: phase.name,
        });
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fáze. Zkuste to prosím později.',
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
    contact_phase_id: string | null;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/task'
      : '/api/admin/contact/task/' + route.params.id,
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
          route.params.id === 'pridat' ? 'Úkol byl úspěšně vytvořen.' : 'Úkol byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/kontakty/ukoly/${response.id}`);
      } else if (redirect) {
        router.push('/kontakty/ukoly');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit úkol. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  loadPhases();
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

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <CheckBadgeIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Zadání úkolu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <BaseFormInput
                v-model="item.name"
                label="Co je potřeba udělat? (Název úkolu)"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. Odeslat uvítací balíček, Zavolat po týdnu..."
                class="max-w-xl"
              />
              <p class="text-sm text-slate-400">
                Tento text se zobrazí jako položka v checklistu u konkrétního kontaktu.
              </p>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2 text-slate-400">
              <ArrowPathIcon class="size-4" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest"
                >Spouštěč úkolu</LayoutTitle
              >
            </div>

            <div class="space-y-4">
              <BaseFormSelect
                v-model="item.contact_phase_id"
                :options="phases"
                label="Přiřadit k fázi"
                name="contact_phase_id"
                class="w-full"
              />
              <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                <p class="text-xs leading-relaxed text-slate-500">
                  Úkol se v CRM automaticky nabídne u všech kontaktů, které se nacházejí ve zvolené
                  fázi.
                </p>
              </div>
            </div>
          </LayoutContainer>
        </aside>
      </div>
    </Form>
  </div>
</template>
