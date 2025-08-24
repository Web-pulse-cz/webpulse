<script setup lang="ts">
import { Form } from 'vee-validate';
import { definePageMeta } from '#imports';
import { useUserGroupStore } from '~/stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const toast = useToast();
const error = ref(false);
const loading = ref(false);
const { refreshIdentity, logout } = useSanctumAuth();

const breadcrumbs = ref([
  {
    name: 'Profil',
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
      toast.add({
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
      toast.add({
        title: 'Hotovo',
        description: 'Uživatelský profil byl úspěšně upraven.',
        color: 'green',
      });
      refreshIdentity();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit uživatelský profil. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function savePassword() {
  if (passwords.value.new_password !== passwords.value.confirm_new_password) {
    toast.add({
      title: 'Chyba',
      description: 'Pole nové heslo a potvrzení nového hesla se neshodují.',
      color: 'red',
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
      toast.add({
        title: 'Úspěch',
        description: 'Nové heslo bylo změněno. Nyní dojde k odhlášení.',
        color: 'green',
      });
      setTimeout(() => {
        logout();
      }, 3000);
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit heslo. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
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
      toast.add({
        title: 'Kopírováno',
        description: 'Kód pozvánky byl zkopírován do schránky.',
        color: 'green',
      });
    })
    .catch(() => {
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se zkopírovat kód pozvánky do schránky.',
        color: 'red',
      });
    });
}

function updateItemImage(files) {
  item.value.avatar = files[0];
}

useHead({
  title: 'Profil',
});

onMounted(() => {
  loadItem();
});
definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div>
    <LayoutHeader
      title="Profil"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      @save="saveItem"
    />
    <div class="grid grid-cols-1 gap-x-8 gap-y-4 lg:grid-cols-4">
      <LayoutContainer class="col-span-3 w-full">
        <Form @submit="saveItem">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.firstname"
              label="Jméno"
              type="text"
              name="firstname"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.lastname"
              label="Příjmení"
              type="text"
              name="lastname"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.email"
              label="E-mail"
              type="text"
              name="email"
              rules="required|email"
              class="col-span-1"
            />
            <div class="col-span-full mb-2 mt-4 border-b border-grayLight" />
            <BaseFormInput
              v-model="item.street"
              label="Ulice a č.p."
              type="text"
              name="street"
              class="col-span-1"
            />
            <br />
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
              class="col-span-1"
            />
          </div>
        </Form>
      </LayoutContainer>
      <LayoutContainer class="col-span-3 w-full lg:col-span-1">
        <div class="grid w-full grid-cols-2 gap-x-8 gap-y-4">
          <div class="col-span-full">
            <BaseFormUploadImage
              v-model="item.avatar"
              :multiple="false"
              type="user"
              format="thumb"
              label="Avatar"
              class="pt-6"
              @update-files="updateItemImage"
            />
          </div>
          <div class="col-span-full mb-2 mt-4 border-b border-grayLight" />
          <BaseFormInput
            v-model="item.invitation_token"
            label="Kód pozvánky"
            disabled
            name="invitation_token"
            class="col-span-full cursor-pointer"
            @click.prevent="copyToClipboard"
          />
          <BaseFormSelect
            v-model="item.user_group_id"
            label="Skupina"
            name="user_group_id"
            rules="required"
            class="col-span-full"
            disabled
            :options="userGroupStore.userGroupsOptions"
          />
        </div>
      </LayoutContainer>
      <LayoutContainer class="col-span-3 w-full lg:col-span-full">
        <Form @submit="savePassword">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4 lg:grid-cols-3">
            <BaseFormInput
              v-model="passwords.current_password"
              label="Současné heslo"
              type="password"
              name="password"
              rules="required"
              class="col-span-full lg:col-span-1"
            />
            <div class="col-span-2 hidden lg:block">&nbsp;</div>
            <BaseFormInput
              v-model="passwords.new_password"
              label="Nové heslo"
              type="password"
              name="new_password"
              rules="required"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="passwords.confirm_new_password"
              label="Potvrzení nového hesla"
              type="password"
              name="confirm_new_password"
              rules="required"
              class="col-span-1"
            />
            <div class="col-span-full flex items-end justify-end lg:col-span-1">
              <BaseButton type="submit" variant="primary" size="xl"> Změnit heslo </BaseButton>
            </div>
          </div>
        </Form>
      </LayoutContainer>
    </div>
  </div>
</template>
