<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { ClipboardDocumentCheckIcon, UserIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref('Detail žádosti');

const breadcrumbs = ref([
  {
    name: 'Pracovní pozice',
    link: '/obsah/pracovni-pozice',
    current: false,
  },
  {
    name: 'Žádosti',
    link: '/obsah/pracovni-pozice/zadosti',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/pracovni-pozice/zadosti/' + route.params.id,
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  email: '' as string,
  phone: '' as string,
  cover_letter: '' as string,
  resume: '' as string,
  status: 'pending' as string,
  salary_expectation: null as number | null,
  availability: null as string | null,
  source: '' as string,
  locale: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    cover_letter: string;
    resume: string;
    status: string;
    salary_expectation: number | null;
    availability: string | null;
    source: string;
    locale: string;
  }>('/api/admin/career/application/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = `Žádost o pracovní pozici ${item.value.career.name}`;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst žádost. Zkuste to prosím později.',
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
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
    cover_letter: string;
    resume: string;
    status: string;
    salary_expectation: number | null;
    availability: string | null;
    source: string;
    locale: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/career/application'
      : '/api/admin/career/application/' + route.params.id,
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
            ? 'Žádost byla úspěšně vytvořena.'
            : 'Žádost byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/pracovni-pozice/zadosti/' + response.id);
      } else if (redirect) {
        router.push('/obsah/pracovni-pozice/zadosti');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit žádost. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="careers"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UserIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Profil a kontaktní údaje</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.firstname"
                name="firstname"
                label="Jméno"
                rules="required"
                placeholder="Např. Petr"
              />
              <BaseFormInput
                v-model="item.lastname"
                name="lastname"
                label="Příjmení"
                rules="required"
                placeholder="Např. Svoboda"
              />
              <BaseFormInput
                v-model="item.email"
                name="email"
                label="E-mail"
                type="email"
                rules="required|email"
                placeholder="petr.svoboda@email.cz"
              />
              <BaseFormInput
                v-model="item.phone"
                name="phone"
                label="Telefon"
                type="text"
                placeholder="+420 777 888 999"
              />

              <div class="col-span-full pt-4">
                <LayoutDivider>Podrobnosti žádosti</LayoutDivider>
              </div>

              <div class="col-span-full">
                <BaseFormTextarea
                  v-model="item.cover_letter"
                  name="cover_letter"
                  label="Motivační dopis / Zpráva uchazeče"
                  rows="10"
                  placeholder="Zde se zobrazí text, který uchazeč zaslal..."
                  class="!bg-slate-50 transition-colors focus:!bg-white"
                />
              </div>

              <BaseFormInput
                v-model="item.salary_expectation"
                name="salary_expectation"
                label="Očekávaný plat (představa)"
                type="number"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.availability"
                name="availability"
                label="Možný termín nástupu"
                type="text"
                disabled
                class="col-span-1 opacity-70"
              />
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2">
              <ClipboardDocumentCheckIcon class="size-4 text-slate-400" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest text-slate-400"
                >Správa stavu</LayoutTitle
              >
            </div>

            <div class="space-y-5">
              <BaseFormSelect
                v-model="item.status"
                name="status"
                label="Aktuální fáze"
                :options="[
                  { value: 'pending', name: 'Čeká na vyřízení' },
                  { value: 'reviewed', name: 'Zobrazeno / Posouzení' },
                  { value: 'accepted', name: 'Přijato k pohovoru' },
                  { value: 'rejected', name: 'Odmítnuto' },
                ]"
              />

              <BaseFormSelect
                v-model="item.source"
                name="source"
                label="Zdroj kandidáta"
                :options="[
                  { value: 'website', name: 'Webová stránka' },
                  { value: 'referral', name: 'Osobní doporučení' },
                  { value: 'social_media', name: 'Sociální sítě' },
                  { value: 'job_board', name: 'Pracovní portál' },
                  { value: 'other', name: 'Jiné' },
                ]"
              />

              <div class="border-t border-slate-100 pt-4">
                <BaseFormInput
                  v-model="item.locale"
                  name="locale"
                  label="Jazyk rozhraní uchazeče"
                  type="text"
                  :disabled="true"
                  class="opacity-60"
                />
              </div>
            </div>
          </LayoutContainer>
        </aside>
      </div>
    </Form>
  </div>
</template>
