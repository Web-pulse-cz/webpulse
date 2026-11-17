<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová fáze procesu' : 'Detail fáze procesu');

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Fáze procesu',
    link: '/kontakty/faze',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/kontakty/faze/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string,
  position: 0 as number,
  show_in_statistics: true as boolean,
});

const { markClean } = useDirtyGuard(item);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string;
    position: number;
    show_in_statistics: boolean;
  }>('/api/admin/contact/phase/' + route.params.id, {
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
        detail: 'Nepodařilo se načíst fázi procesu. Zkuste to prosím později.',
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
    position: number;
    show_in_statistics: boolean;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/phase'
      : '/api/admin/contact/phase/' + route.params.id,
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
            ? 'Fáze procesu byla úspěšně vytvořena.'
            : 'Fáze procesu byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/kontakty/faze/' + response.id);
      } else if (redirect) {
        router.push('/kontakty/faze');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit fázi procesu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-leave' }]"
      slug="contacts"
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
        </aside>
      </div>
    </Form>
  </div>
</template>
