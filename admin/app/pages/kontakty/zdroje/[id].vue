<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { FlagIcon, SwatchIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová zdroj' : 'Detail zdroje');

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Zdroje kontaktů',
    link: '/zdroje',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/zdroje/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string;
  }>('/api/admin/contact/source/' + route.params.id, {
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
        link: '/zdroje/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zdroj kontaktů. Zkuste to prosím později.',
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
      ? '/api/admin/contact/source'
      : '/api/admin/contact/source/' + route.params.id,
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
            ? 'Zdroj kontaktů byl úspěšně vytvořen.'
            : 'Zdroj kontaktů byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/kontakty/zdroje/' + response.id);
      } else if (redirect) {
        router.push('/kontakty/zdroje');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit zdroj kontaktů. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="contacts"
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
                <FlagIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Nastavení obchodní fáze</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <BaseFormInput
                v-model="item.name"
                label="Název fáze / zdroje"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. První kontakt, Vyjednávání, Uzavřeno..."
                class="max-w-xl"
              />
              <p class="text-xs text-slate-400">
                Tento název se bude zobrazovat u kontaktů, v grafech a v přehledu pipeline.
              </p>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2 text-slate-400">
              <SwatchIcon class="size-4" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest"
                >Vizuální odlišení</LayoutTitle
              >
            </div>

            <BaseFormColorPicker
              v-model="item.color"
              label="Barva štítku (Badge)"
              name="color"
              class="w-full"
            />

            <div
              class="mt-6 flex flex-col items-center justify-center gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
            >
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                >Náhled na webu</span
              >
              <div
                class="rounded-full px-3 py-1 text-xs font-bold shadow-sm transition-colors"
                :style="{
                  backgroundColor: item.color + '20',
                  color: item.color,
                  border: `1px solid ${item.color}40`,
                }"
              >
                {{ item.name || 'Ukázka fáze' }}
              </div>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
            <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-900">
              CRM Tip
            </h4>
            <p class="mt-2 text-xs leading-relaxed text-indigo-800/80">
              Snažte se používat barvy, které intuitivně odpovídají stavu – např.
              <strong>zelená</strong> pro úspěch, <strong>červená</strong> pro ztracený kontakt.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
