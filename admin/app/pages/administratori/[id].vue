<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import {
  InformationCircleIcon,
  KeyIcon,
  ShieldCheckIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();
const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const { refreshIdentity } = useSanctumAuth();
const user = useSanctumUser();

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nový administrátor' : 'Detail administrátora',
);

const breadcrumbs = ref([
  {
    name: 'Administrátoři',
    link: '/administratori',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/administratori/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
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
  new_password: '' as string,
  confirm_new_password: '' as string,
  user_group_id: 1 as number,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
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
    new_password: string;
    confirm_new_password: string;
    user_group_id: number;
  }>('/api/admin/user/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.firstname + ' ' + item.value.lastname;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/administratori/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst uživatelský profil. Zkuste to prosím později.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (
    item.value.new_password !== '' &&
    item.value.new_password !== null &&
    item.value.new_password !== item.value.confirm_new_password
  ) {
    $toast.show({
      summary: 'Chyba',
      detail: 'Pole heslo a pole pro potvrzení hesla se neshodují.',
      severity: 'error',
      group: 'bc',
    });
    return;
  }

  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
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
    new_password: string;
    confirm_new_password: string;
    user_group_id: number;
  }>(route.params.id === 'pridat' ? '/api/admin/user' : '/api/admin/user/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Administrátor byl úspěšně vytvořen.'
            : 'Administrátor byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push(`/administratori/${response.id}`);
      } else if (redirect) {
        router.push('/administratori');
      } else {
        loadItem();
      }
    })
    .then(() => {
      refreshIdentity();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit administrátora. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  title: pageTitle.value,
});

onMounted(() => {
  userGroupStore.fetchUserGroups();
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
      slug="users"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg"
              >
                <UserIcon class="size-6" />
              </div>
              <LayoutTitle class="!mb-0">Osobní profil uživatele</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
              <BaseFormInput
                v-model="item.firstname"
                label="Jméno"
                type="text"
                name="firstname"
                rules="required|min:3"
                placeholder="Např. Michal"
              />
              <BaseFormInput
                v-model="item.lastname"
                label="Příjmení"
                type="text"
                name="lastname"
                rules="required|min:3"
                placeholder="Např. Holý"
              />
              <BaseFormInput
                v-model="item.email"
                label="E-mailová adresa (přihlašovací jméno)"
                type="text"
                name="email"
                rules="required|email"
                placeholder="jmeno@barbershop.cz"
              />
              <BaseFormInput
                v-model="item.phone"
                label="Kontaktní telefon"
                type="text"
                name="phone"
                rules="required"
                placeholder="+420 777 000 000"
              />

              <div class="col-span-full pt-4">
                <LayoutDivider>Bydliště / Kontaktní adresa</LayoutDivider>
              </div>

              <BaseFormInput
                v-model="item.street"
                label="Ulice a číslo popisné"
                type="text"
                name="street"
                class="col-span-full lg:col-span-1"
              />

              <div class="col-span-full grid grid-cols-3 gap-4 lg:col-span-1">
                <BaseFormInput
                  v-model="item.zip"
                  label="PSČ"
                  type="text"
                  name="zip"
                  class="col-span-1"
                />
                <BaseFormInput
                  v-model="item.city"
                  type="text"
                  label="Město"
                  name="city"
                  class="col-span-2"
                />
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600"
              >
                <KeyIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Zabezpečení účtu</LayoutTitle>
            </div>

            <div
              v-if="item.id !== user.id"
              class="rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60"
            >
              <div class="mb-6">
                <h4 class="text-sm font-bold text-slate-900">Resetovat přístupové heslo</h4>
                <p class="text-xs text-slate-500">
                  Ponechte pole prázdná, pokud heslo nechcete měnit.
                </p>
              </div>

              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <BaseFormInput
                  v-model="item.new_password"
                  label="Nové heslo"
                  type="password"
                  name="new_password"
                  placeholder="••••••••"
                />
                <BaseFormInput
                  v-model="item.confirm_new_password"
                  label="Potvrzení hesla"
                  type="password"
                  name="confirm_new_password"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <div
              v-else
              class="flex items-center gap-4 rounded-2xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-200/50"
            >
              <InformationCircleIcon class="size-6 shrink-0 text-amber-600" />
              <p class="text-sm font-medium text-amber-900">
                Právě upravujete svůj vlastní profil. Změnu hesla z bezpečnostních důvodů proveďte v
                <NuxtLink
                  to="/profil"
                  class="font-bold underline decoration-amber-500/30 hover:decoration-amber-500"
                  >nastavení svého účtu</NuxtLink
                >.
              </p>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutContainer class="!py-6">
            <div class="mb-6 flex items-center gap-2">
              <ShieldCheckIcon class="size-4 text-slate-400" />
              <LayoutTitle class="!mb-0 text-xs uppercase tracking-widest text-slate-400"
                >Oprávnění</LayoutTitle
              >
            </div>

            <BaseFormSelect
              v-model="item.user_group_id"
              label="Uživatelská skupina (Role)"
              name="user_group_id"
              rules="required"
              :options="userGroupStore.userGroupsOptions"
            />

            <div class="mt-6 rounded-xl bg-slate-50 p-4">
              <p class="text-[11px] leading-relaxed text-slate-400">
                <strong>Poznámka:</strong> Role určuje, k jakým modulům a akcím (vytváření, mazání)
                bude mít uživatel v adminu přístup.
              </p>
            </div>
          </LayoutContainer>

          <div class="rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200">
            <h4 class="text-sm font-bold">Auditní stopa</h4>
            <p class="mt-2 text-xs leading-relaxed opacity-80">
              Všechny změny provedené tímto uživatelem jsou logovány. Ujistěte se, že každý člen
              týmu má svůj vlastní unikátní účet.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
