<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { ListBulletIcon, SwatchIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

const error = ref(false);
const loading = ref(false);

const contacts = ref([] as Array<any>);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nový seznam kontaktů' : 'Detail seznamu kontaktů',
);

const breadcrumbs = ref([
  {
    name: 'Kontakty',
    link: '/kontakty',
    current: false,
  },
  {
    name: 'Seznam kontaktů',
    link: '/kontakty/seznamy',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/kontakty/seznamy/pridat',
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
  }>('/api/admin/contact/list/' + route.params.id, {
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
        link: '/seznamy/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst seznam kontaktů. Zkuste to prosím později.',
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
    color: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/contact/list'
      : '/api/admin/contact/list/' + route.params.id,
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
            ? 'Seznam kontaktů byl úspěšně vytvořen.'
            : 'Seznam kontaktů byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/kontakty/seznamy/' + response.id);
      } else if (redirect) {
        router.push('/kontakty/seznamy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit seznam kontaktů. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadContacts() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/contact?contact_list_id=' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      contacts.value.data = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kontakty. Zkuste to prosím později.',
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
    loadContacts();
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
      :actions="route.params.id === 'pridat' ? [{ type: 'save' }, { type: 'save-and-stay' }] : []"
      slug="contacts"
      @save="saveItem"
    />

    <Form v-if="route.params.id === 'pridat'" ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <ListBulletIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Definice nového seznamu</LayoutTitle>
            </div>

            <div class="max-w-xl">
              <BaseFormInput
                v-model="item.name"
                label="Název seznamu"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. VIP klienti 2026"
              />
              <p class="mt-2 text-sm italic text-slate-400">
                Název by měl být stručný a výstižný pro snadnou orientaci v CRM.
              </p>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2 text-slate-400">
              <SwatchIcon class="size-4" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest"
                >Barevné odlišení</LayoutTitle
              >
            </div>

            <BaseFormColorPicker
              v-model="item.color"
              label="Barva štítku"
              name="color"
              class="w-full"
            />

            <div
              class="mt-6 flex flex-col items-center justify-center gap-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200"
            >
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                >Náhled segmentu</span
              >
              <div
                class="rounded-full px-4 py-1 text-xs font-bold shadow-sm transition-all"
                :style="{
                  backgroundColor: item.color + '20',
                  color: item.color,
                  border: `1px solid ${item.color}40`,
                }"
              >
                {{ item.name || 'Ukázka názvu' }}
              </div>
            </div>
          </LayoutContainer>
        </aside>
      </div>
    </Form>

    <LayoutContainer v-else>
      <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-6">
        <div class="flex items-center gap-3">
          <div
            class="flex size-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg"
          >
            <UserGroupIcon class="size-6" />
          </div>
          <div>
            <LayoutTitle class="!mb-0">Uživatelé v tomto seznamu</LayoutTitle>
            <p class="text-sm text-slate-500">
              Celkový počet kontaktů:
              <span class="font-bold text-indigo-600">{{ contacts.data?.length || 0 }}</span>
            </p>
          </div>
        </div>
      </div>

      <BaseTable
        :items="contacts"
        :columns="[
          { key: 'id', name: 'ID', type: 'text', width: 60, sortable: true },
          { key: 'firstname', name: 'Jméno', type: 'text', sortable: false },
          { key: 'lastname', name: 'Příjmení', type: 'text', sortable: false },
          { key: 'phone', name: 'Telefon', type: 'text', hidden: true, sortable: true },
          { key: 'email', name: 'E-mail', type: 'text', hidden: true, sortable: true },
          {
            key: 'phase',
            name: 'Fáze CRM',
            type: 'badge',
            sortable: false,
            colorKey: 'phase_color',
          },
          {
            key: 'source',
            name: 'Zdroj',
            type: 'badge',
            sortable: false,
            colorKey: 'source_color',
          },
        ]"
        :actions="[{ type: 'edit', hash: '#info', path: '/kontakty' }]"
        :loading="loading"
        :error="error"
        singular="Kontakt"
        plural="Kontakty"
        slug="contacts"
        class="pb-4"
      />
    </LayoutContainer>
  </div>
</template>
