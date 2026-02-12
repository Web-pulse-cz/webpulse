<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const assigenedUser = ref(null);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová stránka' : 'Detail stránky');

const breadcrumbs = ref([
  {
    name: 'Stránky',
    link: '/nastaveni/stranky',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/stranky',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  url: '' as string,
  is_secure: false as boolean,
  is_active: false as boolean,
  settings: {} as Record<string, unknown>,
  users: [] as Array<number>,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    url: string;
    is_secure: boolean;
    is_active: boolean;
    settings: Record<string, unknown>;
    users: Array<number>;
  }>('/api/admin/site/' + route.params.id, {
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
        link: '/nastaveni/stranky/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst stránku. Zkuste to prosím později.',
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
    url: string;
    is_secure: boolean;
    is_active: boolean;
    settings: Record<string, unknown>;
    users: Array<number>;
  }>(route.params.id === 'pridat' ? '/api/admin/site' : '/api/admin/site/' + route.params.id, {
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
            ? 'Stránka byla úspěšně vytvořena.'
            : 'Stránka byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/stranky/' + response.id + '?created=true');
      } else if (redirect) {
        router.push('/nastaveni/stranky');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se uložit stránku. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addUserToItem() {
  if (!item.value.users.includes(assigenedUser.value.id)) {
    item.value.users.push(assigenedUser.value);
  }
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
      slug="tax_rates"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-2 items-baseline gap-x-10">
        <LayoutContainer class="col-span-1 w-full">
          <div class="grid grid-cols-12 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-6"
            />
            <BaseFormInput
              v-model="item.url"
              label="URL"
              type="text"
              name="url"
              rules="required|min:3"
              class="col-span-6"
            />
            <BaseFormSwitch
              v-model:enabled="item.is_secure"
              disabled-text="HTTP"
              enabled-text="HTTPS"
              class="col-span-4"
            />
            <div class="col-span-4"></div>
            <BaseFormSwitch
              v-model:enabled="item.is_active"
              disabled-text="Neaktivní"
              enabled-text="Aktivní"
              class="col-span-4"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-1 w-full">
          <LayoutTitle>Přiřazení uživatelé</LayoutTitle>
          <div class="grid grid-cols-3 gap-x-8 gap-y-4">
            <div class="col-span-full grid grid-cols-3 items-end gap-x-8 p-1.5">
              <SiteUsersAutocomplete
                v-model="assigenedUser"
                label="Vyhledat uživatele"
                class="col-span-2"
              />
              <BaseButton
                v-if="assigenedUser !== null"
                type="button"
                variant="secondary"
                size="lg"
                @click="addUserToItem"
                >Přidat</BaseButton
              >
            </div>
            <div
              v-for="(user, index) in item.users"
              :key="index"
              class="col-span-full grid grid-cols-3 items-center border p-1.5"
            >
              <div class="col-span-1 text-grayCustom">
                {{ user.firstname + ' ' + user.lastname }}
              </div>
              <div class="col-span-1 text-grayCustom">
                {{ user.email }}
              </div>
              <div class="col-span-1 text-end">
                <BaseButton
                  type="button"
                  variant="secondary"
                  size="lg"
                  @click="item.users.splice(index, 1)"
                  >Odstranit</BaseButton
                >
              </div>
            </div>
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
