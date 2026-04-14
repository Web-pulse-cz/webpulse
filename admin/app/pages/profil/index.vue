<script setup lang="ts">
import { Form } from 'vee-validate';

import { DocumentDuplicateIcon, LockClosedIcon, UserIcon } from '@heroicons/vue/24/outline';
import { definePageMeta } from '#imports';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const { $toast } = useNuxtApp();
const error = ref(false);
const loading = ref(false);
const { refreshIdentity, logout } = useSanctumAuth();

const pageTitle = ref('Uživatelský profil');

const breadcrumbs = ref([
  {
    name: pageTitle.value,
    link: '/profil',
    current: true,
  },
]);

const item = ref({
  firstname: '' as string,
  lastname: '' as string,
  avatar: '' as string,
  email: '' as string,
  phone_prefix: '+420' as string,
  phone: '' as string,
  invitation_token: '' as string,
  street: '' as string,
  city: '' as string,
  zip: '' as string,
});

const passwords = ref({
  current_password: '' as string,
  new_password: '' as string,
  confirm_new_password: '' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    firstname: string;
    lastname: string;
    avatar: string;
    email: string;
    phone_prefix: string;
    phone: string;
    invitation_token: string;
    street: string;
    city: string;
    zip: string;
  }>('/api/admin/profile', {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        title: 'Chyba',
        description: 'Nepodařilo se načíst uživatelský profil. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    firstname: string;
    lastname: string;
    avatar: string;
    email: string;
    phone_prefix: string;
    phone: string;
    invitation_token: string;
    street: string;
    city: string;
    zip: string;
  }>('/api/admin/profile', {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
    })
    .then(() => {
      $toast.show({
        summary: 'Hotovo',
        detail: 'Uživatelský profil byl úspěšně upraven.',
        severity: 'success',
      });
      refreshIdentity();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit uživatelský profil. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function savePassword() {
  if (passwords.value.new_password !== passwords.value.confirm_new_password) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Pole nové heslo a potvrzení nového hesla se neshodují.',
      severity: 'error',
      group: 'bc',
    });
    return;
  }
  const client = useSanctumClient();
  loading.value = true;

  await client<{ current_password: string; new_password: string; confirm_new_password: string }>(
    '/api/admin/profile/password',
    {
      method: 'POST',
      body: JSON.stringify(passwords.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then((response) => {
      item.value = response;
    })
    .then(() => {
      refreshIdentity();
      $toast.show({
        summary: 'Úspěch',
        detail: 'Nové heslo bylo změněno. Nyní dojde k odhlášení.',
        severity: 'success',
      });
      setTimeout(() => {
        logout();
      }, 3000);
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit heslo. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function copyToClipboard() {
  await navigator.clipboard
    .writeText(item.value.invitation_token)
    .then(() => {
      $toast.show({
        summary: 'Kopírováno',
        detail: 'Kód pozvánky byl zkopírován do schránky.',
        severity: 'success',
      });
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se zkopírovat kód pozvánky do schránky.',
        severity: 'error',
      });
    });
}

useHead({
  title: 'Profil',
});

onMounted(() => {
  userGroupStore.fetchUserGroups();
  loadItem();
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div class="space-y-6 pb-12">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      @save="saveItem"
    />

    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
      <div class="col-span-1 space-y-8 lg:col-span-9">
        <LayoutContainer>
          <Form @submit="saveItem">
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <UserIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Osobní údaje</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <BaseFormInput
                v-model="item.firstname"
                label="Jméno"
                type="text"
                name="firstname"
                rules="required|min:3"
              />
              <BaseFormInput
                v-model="item.lastname"
                label="Příjmení"
                type="text"
                name="lastname"
                rules="required|min:3"
              />
              <BaseFormInput
                v-model="item.email"
                label="E-mailová adresa"
                type="text"
                name="email"
                rules="required|email"
                class="col-span-1 lg:col-span-1"
              />

              <div class="col-span-full pt-4">
                <LayoutDivider>Bydliště / Kontaktní adresa</LayoutDivider>
              </div>

              <BaseFormInput
                v-model="item.street"
                label="Ulice a č.p."
                type="text"
                name="street"
                class="col-span-1 sm:col-span-2 lg:col-span-2"
              />

              <div class="col-span-1 hidden lg:block"></div>

              <BaseFormInput v-model="item.zip" label="PSČ" type="text" name="zip" />
              <BaseFormInput
                v-model="item.city"
                label="Město"
                type="text"
                name="city"
                class="col-span-1 sm:col-span-1 lg:col-span-2"
              />
            </div>
          </Form>
        </LayoutContainer>

        <LayoutContainer>
          <Form @submit="savePassword">
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600"
              >
                <LockClosedIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Zabezpečení účtu</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
              <div class="col-span-full lg:col-span-1">
                <BaseFormInput
                  v-model="passwords.current_password"
                  label="Současné heslo"
                  type="password"
                  name="password"
                  rules="required"
                />
                <p class="mt-2 text-xs text-slate-400">
                  Pro změnu údajů musíte potvrdit stávající heslo.
                </p>
              </div>

              <div class="hidden lg:col-span-2 lg:block"></div>

              <BaseFormInput
                v-model="passwords.new_password"
                label="Nové heslo"
                type="password"
                name="new_password"
                rules="required"
              />
              <BaseFormInput
                v-model="passwords.confirm_new_password"
                label="Potvrzení hesla"
                type="password"
                name="confirm_new_password"
                rules="required"
              />

              <div class="col-span-full flex items-end justify-end pt-4">
                <BaseButton type="submit" variant="primary" size="xl" class="w-full sm:w-auto">
                  Aktualizovat heslo
                </BaseButton>
              </div>
            </div>
          </Form>
        </LayoutContainer>
      </div>

      <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
        <LayoutContainer class="!py-6">
          <LayoutTitle class="text-xs uppercase tracking-widest text-slate-400"
            >Informace o účtu</LayoutTitle
          >

          <div class="mt-6 space-y-6">
            <div>
              <BaseFormSelect
                v-model="item.user_group_id"
                label="Uživatelská skupina"
                name="user_group_id"
                disabled
                class="opacity-80"
                :options="userGroupStore.userGroupsOptions"
              />
              <p class="mt-1.5 text-[11px] italic text-slate-400">
                Skupinu může měnit pouze administrátor.
              </p>
            </div>

            <div v-if="item.invitation_token" class="border-t border-slate-100 pt-4">
              <label class="mb-2 block text-sm font-medium text-slate-700">Kód pozvánky</label>
              <div
                class="group relative cursor-pointer rounded-xl bg-slate-50 p-3 ring-1 ring-inset ring-slate-200 transition-all hover:bg-slate-100"
                @click="copyToClipboard"
              >
                <code class="font-mono text-xs text-indigo-600">{{ item.invitation_token }}</code>
                <div class="absolute inset-y-0 right-3 flex items-center">
                  <DocumentDuplicateIcon
                    class="size-4 text-slate-400 group-hover:text-indigo-600"
                  />
                </div>
              </div>
              <p class="mt-2 text-[11px] text-slate-400">Kliknutím kód zkopírujete.</p>
            </div>
          </div>
        </LayoutContainer>

        <div class="rounded-3xl bg-indigo-50 p-6 ring-1 ring-inset ring-indigo-100">
          <h4 class="text-sm font-bold text-indigo-900">Potřebujete pomoc?</h4>
          <p class="mt-2 text-xs leading-relaxed text-indigo-700/80">
            Pokud nemůžete změnit svůj e-mail nebo skupinu, kontaktujte prosím technickou podporu
            Barbershop Adminu.
          </p>
        </div>
      </aside>
    </div>
  </div>
</template>
