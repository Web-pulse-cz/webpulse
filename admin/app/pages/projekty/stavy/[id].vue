<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový stav projektu' : 'Stav projektu');

const breadcrumbs = ref([
  {
    name: 'Projekty',
    link: '/projekty',
    current: false,
  },
  {
    name: 'Statusy projektů',
    link: '/projekty/stavy',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/projekty/stavy/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string,
  position: 0 as number,
  is_closed: false as boolean,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string;
  }>('/api/admin/project/status/' + route.params.id, {
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
        link: '/projekty/stavy/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stav projektu. Zkuste to prosím později.',
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
    color: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/project/status'
      : '/api/admin/project/status/' + route.params.id,
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
            ? 'Stav projektu byl úspěšně vytvořen.'
            : 'Stav projektu byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/projekty/stavy/' + response.id);
      } else if (redirect) {
        router.push('/projekty/stavy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit stav projektu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="projects"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 gap-8">
        <LayoutContainer class="w-full">
          <div class="mb-8 flex items-center gap-3">
            <div
              class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
            >
              <SwatchIcon class="size-5" />
            </div>
            <LayoutTitle class="!mb-0">Identifikace projektu</LayoutTitle>
          </div>

          <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
            <div class="col-span-1">
              <BaseFormInput
                v-model="item.name"
                label="Název"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. Branding 2026"
              />
              <p class="mt-1.5 text-xs text-slate-400">
                Jasný a unikátní název pro snadnou identifikaci v seznamech.
              </p>
            </div>

            <div class="col-span-1">
              <BaseFormColorPicker v-model="item.color" label="Barva projektu" name="color" />
              <p class="mt-1.5 text-xs text-slate-400">
                Tato barva se použije pro odlišení projektu v kalendáři a grafech.
              </p>
            </div>

            <div class="col-span-1">
              <BaseFormInput
                v-model="item.position"
                label="Pořadí"
                type="number"
                name="position"
              />
            </div>

            <div class="col-span-1">
              <BaseFormCheckbox
                v-model="item.is_closed"
                label="Uzavřený stav (projekt je ukončen)"
                name="is_closed"
              />
            </div>
          </div>
        </LayoutContainer>

        <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200 lg:p-8">
          <div class="flex items-start gap-4">
            <div
              class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm"
            >
              <QuestionMarkCircleIcon class="size-6" />
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900">Proč zvolit barvu?</h4>
              <p class="mt-1 text-sm leading-relaxed text-slate-600">
                Přiřazení barvy vám pomůže vizuálně seskupit související úkoly a poptávky. V
                přehledech tak na první pohled uvidíte, které aktivity patří k tomuto projektu.
              </p>
            </div>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
