<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { ClipboardDocumentListIcon, RocketLaunchIcon, TagIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový changelog' : 'Detail changelogu');

const breadcrumbs = ref([
  {
    name: 'Changelog',
    link: '/nastaveni/changelog',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/changelog/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  version: '' as string,
  title: '' as string,
  subtitle: '' as string,
  description: '' as string,
  type: 'bugfix' as string,
  priority: 'medium' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    version: string;
    title: string;
    subtitle: string;
    description: string;
    type: string;
    priority: string;
  }>('/api/admin/changelog/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = item.value.title;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/changelog/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst changelog. Zkuste to prosím později.',
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
    version: string;
    title: string;
    subtitle: string | null;
    description: string | null;
    type: string;
    priority: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/changelog'
      : '/api/admin/changelog/' + route.params.id,
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
            ? 'Changelog byl úspěšně vytvořen.'
            : 'Changelog byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/nastaveni/changelog/${response.id}`);
      } else if (redirect) {
        router.push('/nastaveni/changelog');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} changelog. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
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
      slug="activities"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-8">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <RocketLaunchIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Detail záznamu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <BaseFormInput
                v-model="item.title"
                label="Hlavní název aktivity"
                type="text"
                name="title"
                rules="required|min:3"
                placeholder="Např. Optimalizace databáze a nové API"
              />

              <BaseFormInput
                v-model="item.subtitle"
                label="Podnadpis / Krátké shrnutí"
                type="text"
                name="subtitle"
                placeholder="Drobné úpravy v jádru systému..."
              />

              <div class="pt-4">
                <BaseFormEditor
                  v-model="item.description"
                  label="Podrobný popis změn"
                  name="description"
                  rules="min:3"
                />
              </div>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-4">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2">
              <TagIcon class="size-4 text-slate-400" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest text-slate-400"
                >Metadata záznamu</LayoutTitle
              >
            </div>

            <div class="space-y-6">
              <div class="rounded-2xl bg-slate-900 p-5 text-white shadow-lg shadow-slate-200">
                <BaseFormInput
                  v-model="item.version"
                  label="Verze sestavení"
                  type="text"
                  name="version"
                  rules="required|min:1"
                  placeholder="v1.0.0"
                  class="!text-white"
                />
              </div>

              <div
                class="grid grid-cols-1 gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
              >
                <BaseFormSelect
                  v-model="item.type"
                  label="Typ záznamu"
                  name="type"
                  :options="[
                    { value: 'bugfix', name: '🐞 Oprava chyby' },
                    { value: 'feature', name: '✨ Nová funkce' },
                    { value: 'design', name: '🎨 Vylepšení designu' },
                    { value: 'other', name: '🔧 Ostatní' },
                  ]"
                  rules="required"
                />

                <BaseFormSelect
                  v-model="item.priority"
                  label="Důležitost"
                  name="priority"
                  :options="[
                    { value: 'low', name: 'Nízká' },
                    { value: 'medium', name: 'Normální' },
                    { value: 'high', name: 'Vysoká' },
                  ]"
                  rules="required"
                />
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
            <div class="mb-3 flex items-center gap-2">
              <ClipboardDocumentListIcon class="size-4 text-indigo-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-900">
                Správa aktivit
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-indigo-800/80">
              Záznamy o aktivitách se automaticky propisují do <strong>Timeline</strong> projektu.
              Snažte se o stručné a jasné názvy, aby uživatelé věděli, co je nového na první pohled.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
