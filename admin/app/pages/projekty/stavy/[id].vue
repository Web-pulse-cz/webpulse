<script setup lang="ts">
import { ref, inject } from 'vue';

import { Form } from 'vee-validate';
import { SwatchIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

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
  sites: [] as number[],
});

const { markClean } = useDirtyGuard(item);

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
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = Array.isArray(response.sites)
        ? response.sites.map((s: any) => (typeof s === 'object' ? s.id : s))
        : [];
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
      markClean();
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
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
      markClean();
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
  title: pageTitle,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
  if (route.params.id === 'pridat') markClean();
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
      :actions="[{ type: 'save' }, { type: 'save-and-leave' }]"
      slug="projects"
      :saving="loading"
      @save="saveItem"
    />
    <div
      v-if="loading && !item.id && route.params.id !== 'pridat'"
      class="grid animate-pulse grid-cols-1 gap-8 lg:grid-cols-12"
      aria-busy="true"
    >
      <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-9">
        <div class="h-5 w-1/3 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-24 rounded bg-slate-100"></div>
      </div>
      <div class="h-64 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 lg:col-span-3"></div>
    </div>
    <Form v-else ref="formRef" @submit="() => saveItem(false)">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
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
