<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();
const toast = useToast();

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

async function saveItem(redirect = true as boolean) {
  if (
    item.value.new_password !== '' &&
    item.value.new_password !== null &&
    item.value.new_password !== item.value.confirm_new_password
  ) {
    toast.add({
      title: 'Chyba',
      description: 'Pole heslo a pole pro potvrzení hesla se neshodují.',
      color: 'red',
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
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Administrátor byl úspěšně vytvořen.'
            : 'Administrátor byl úspěšně upraven.',
        color: 'green',
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
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se uložit administrátora. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="users"
      @save="saveItem"
    />
    <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-4">
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
            <BaseFormInput
              v-model="item.phone"
              label="Telefon"
              type="text"
              name="phone"
              rules="required"
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
            <div class="col-span-full mb-2 mt-4 border-b border-grayLight" />
            <div v-if="item.id !== user.id" class="col-span-full grid grid-cols-2 gap-x-8 gap-y-4">
              <BaseFormInput
                v-model="item.new_password"
                label="Nové heslo"
                type="password"
                name="new_password"
                class="col-span-1"
              />
              <BaseFormInput
                v-model="item.confirm_new_password"
                label="Potvrzení nového hesla"
                type="password"
                name="confirm_new_password"
                class="col-span-1"
              />
            </div>
            <div v-else class="col-span-full">
              <span class="block text-sm/6 font-medium text-grayCustom"
                >Změnu hesla provedete ve svém uživatelském účtu</span
              >
            </div>
          </div>
        </Form>
      </LayoutContainer>
      <LayoutContainer class="col-span-3 w-full lg:col-span-1">
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
          <!--          <div class="col-span-full text-center">
            <NuxtImg
              class="mx-auto size-full rounded-full"
              src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
              alt=""
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
          /> -->
          <BaseFormSelect
            v-model="item.user_group_id"
            label="Skupina"
            name="user_group_id"
            rules="required"
            class="col-span-full"
            :options="userGroupStore.userGroupsOptions"
          />
        </div>
      </LayoutContainer>
    </div>
  </div>
</template>
