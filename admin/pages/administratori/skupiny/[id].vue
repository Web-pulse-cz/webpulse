<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useUserGroupStore } from '~/stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const { refreshIdentity } = useSanctumAuth();

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nová uživatelská skupina' : 'Detail uživatelské skupiny',
);

const breadcrumbs = ref([
  {
    name: 'Uživatelé',
    link: '/administratori',
    current: false,
  },
  {
    name: 'Uživatelské skupiny',
    link: '/administratori/skupiny',
    current: false,
  },
  {
    name: 'Nová uživatelská skupina',
    link: '/administratori/skupiny/pridat',
    current: true,
  },
]);

const allowedPermissions = ref([
  { name: 'Kontakty', value: 'Kontakty', slug: 'contacts' },
  { name: 'Kalednář', value: 'Kalednář', slug: 'calendars' },
  { name: 'Cashflow', value: 'Cashflow', slug: 'cashflows' },
  { name: 'Projekty', value: 'Projekty', slug: 'projects' },
  { name: 'Faktury', value: 'Faktury', slug: 'invoices' },
  { name: 'Trackování', value: 'Trackování', slug: 'trackings' },
  { name: 'Cenové nabídky', value: 'Nacenění', slug: 'price_offers' },
  { name: 'Uživatelé', value: 'Uživatelé', slug: 'users' },
  {
    name: 'Uživatelské skupiny',
    value: 'Uživatelské skupiny',
    slug: 'user_groups',
  },
  { name: 'Šablony zpráv', value: 'Šablony zpráv', slug: 'message_blueprints' },
  { name: 'Aktivita', value: 'Aktivita', slug: 'users_has_activities' },
  { name: 'Aktivity', value: 'Aktivity', slug: 'activities' },
  { name: 'Sazby DPH', value: 'Sazby DPH', slug: 'tax_rates' },
  { name: 'Jazyky', value: 'Jazyky', slug: 'languages' },
  { name: 'Země', value: 'Země', slug: 'countries' },
  { name: 'Měny', value: 'Měny', slug: 'currencies' },
  { name: 'Služby', value: 'Služby', slug: 'services' },
  { name: 'Novinky', value: 'Novinky', slug: 'novelties' },
  { name: 'Poptávky', value: 'Poptávky', slug: 'demands' },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  permissions: [] as [],
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    permissions: [];
  }>('/api/admin/user/group/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/administratori/skupiny/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst uživatelskou skupinu. Zkuste to prosím později.',
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
    id: number | null;
    name: string;
    permissions: [];
    confirm_new_password: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/user/group'
      : '/api/admin/user/group/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    },
  )
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Skupina uživatelů byla úspěšně vytvořena.'
            : 'Skupina uživatelů byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/administratori/skupiny');
    })
    .then(() => {
      refreshIdentity();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} uživatelskou skupinu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
      userGroupStore.fetchUserGroups();
    });
}

function addPermission() {
  item.value.permissions.push({
    name: '',
    slug: '',
    permissions: {
      view: true,
      edit: true,
      delete: true,
    },
  });
}

function removePermission(key: number) {
  item.value.permissions.splice(key, 1);
}

watch(
  () => item.value.permissions,
  (newPermissions) => {
    newPermissions.forEach((permission) => {
      const selectedPermission = allowedPermissions.value.find((p) => p.value === permission.name);
      if (selectedPermission) {
        permission.slug = selectedPermission.slug;
      }
    });
  },
  { deep: true },
);

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
      :title="route.params.id === 'pridat' ? 'Nová uživatelská skupina' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="user_groups"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <LayoutContainer>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
          <BaseFormInput
            v-model="item.name"
            label="Název"
            type="text"
            name="name"
            rules="required|min:3"
            class="col-span-1"
          />
        </div>
      </LayoutContainer>
      <LayoutContainer>
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
          <div
            v-for="(permission, key) in item.permissions"
            :key="key"
            class="col-span-full grid grid-cols-6 gap-x-8 gap-y-4"
          >
            <BaseFormSelect
              v-model="permission.name"
              label="Název"
              :name="key + '_name'"
              rules="required|min:3"
              class="col-span-2"
              :options="allowedPermissions"
            />
            <BaseFormCheckbox
              v-model="permission.permissions.view"
              label="Zobrazit"
              type="checkbox"
              :name="key + '_view'"
              class="col-span-1 flex items-end"
            />
            <BaseFormCheckbox
              v-model="permission.permissions.edit"
              label="Editovat"
              type="checkbox"
              :name="key + '_edit'"
              class="col-span-1 flex items-end"
            />
            <BaseFormCheckbox
              v-model="permission.permissions.delete"
              label="Mazat"
              type="checkbox"
              :name="key + '_delete'"
              class="col-span-1 flex items-end"
            />
            <div class="col-span-1 flex items-end justify-end">
              <BaseButton variant="danger" size="lg" @click="removePermission(key)">
                Odebrat
              </BaseButton>
            </div>
          </div>
          <div class="col-span-full text-center">
            <BaseButton
              type="button"
              variant="secondary"
              size="lg"
              class="mt-8"
              @click="addPermission"
            >
              Přidat oprávnění
            </BaseButton>
          </div>
        </div>
      </LayoutContainer>
    </Form>
  </div>
</template>
