<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { Form } from 'vee-validate';
import { useUserGroupStore } from '~/../stores/userGroupStore';

const userGroupStore = useUserGroupStore();

const toast = useToast();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const { refreshIdentity } = useSanctumAuth();

const pageTitle = ref(
  route.params.id === 'pridat'
    ? 'Nová administrátorská skupina'
    : 'Detail administrátorské skupiny',
);

const breadcrumbs = ref([
  {
    name: 'Administrátoři',
    link: '/administratori',
    current: false,
  },
  {
    name: 'Administrátorské skupiny',
    link: '/administratori/skupiny',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/administratori/skupiny/pridat',
    current: true,
  },
]);

const allowedPermissions = ref([
  { name: 'Aktivita', value: 'Aktivita', slug: 'users_has_activities' },
  { name: 'Aktivity', value: 'Aktivity', slug: 'activities' },
  { name: 'Administrátoři', value: 'Administrátoři', slug: 'users' },
  { name: 'Administrátorské skupiny', value: 'Administrátorské skupiny', slug: 'user_groups' },
  { name: 'Blogové články', value: 'Blogové články', slug: 'posts' },
  { name: 'Cashflow', value: 'Cashflow', slug: 'cashflows' },
  { name: 'Cenové nabídky', value: 'Nacenění', slug: 'price_offers' },
  { name: 'Dodavatelé', value: 'Dodavatelé', slug: 'suppliers' },
  { name: 'E-maily', value: 'E-maily', slug: 'emails' },
  { name: 'Faktury', value: 'Faktury', slug: 'invoices' },
  { name: 'FAQ', value: 'FAQ', slug: 'faqs' },
  { name: 'Informační stránky', value: 'Informační stránky', slug: 'pages' },
  { name: 'Jazyky', value: 'Jazyky', slug: 'languages' },
  { name: 'Kalednář', value: 'Kalednář', slug: 'calendars' },
  { name: 'Klienti', value: 'Klienti', slug: 'clients' },
  { name: 'Kontakty', value: 'Kontakty', slug: 'contacts' },
  { name: 'Kvízy', value: 'Kvízy', slug: 'quizzes' },
  { name: 'Ligy', value: 'Ligy', slug: 'leagues' },
  { name: 'Loga klientů', value: 'Loga klientů', slug: 'logos' },
  { name: 'Měny', value: 'Měny', slug: 'currencies' },
  { name: 'Nastavení', value: 'Nastavení', slug: 'settings' },
  { name: 'Novinky', value: 'Novinky', slug: 'novelties' },
  { name: 'Oběry newsletteru', value: 'Odběry newsletteru', slug: 'newsletters' },
  { name: 'Poptávky', value: 'Poptávky', slug: 'demands' },
  { name: 'Pracovní pozice', value: 'Pracovní pozice', slug: 'careers' },
  { name: 'Projekty', value: 'Projekty', slug: 'projects' },
  { name: 'Reference', value: 'Reference', slug: 'reviews' },
  { name: 'Sazby DPH', value: 'Sazby DPH', slug: 'tax_rates' },
  { name: 'Služby', value: 'Služby', slug: 'services' },
  { name: 'Smlouvy', value: 'Smlouvy', slug: 'contracts' },
  { name: 'Šablony zpráv', value: 'Šablony zpráv', slug: 'message_blueprints' },
  { name: 'Trackování', value: 'Trackování', slug: 'trackings' },
  { name: 'Události', value: 'Události', slug: 'events' },
  { name: 'Úkoly', value: 'Úkoly', slug: 'tasks' },
  { name: 'Zaměstnanci', value: 'Zaměstnanci', slug: 'employees' },
  { name: 'Země', value: 'Země', slug: 'countries' },
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
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/administratori/skupiny/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst administrátorskou skupinu. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
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
    .then((response) => {
      toast.add({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Administrátorská skupina byla úspěšně vytvořena.'
            : 'Administrátorská skupina byla úspěšně upravena.',
        severity: 'succcess',
        group: 'bc',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push(`/administratori/skupiny/${response.id}`);
      } else if (redirect) {
        router.push('/administratori/skupiny');
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
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} administrátorskou skupinu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        severity: 'error',
        group: 'bc',
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

function availablePermissions(index: number) {
  const selectedPermissions = item.value.permissions.map((p) => p.name);
  const currentPermission = item.value.permissions[index]?.name;

  return allowedPermissions.value.filter((perm) => {
    return perm.value === currentPermission || !selectedPermissions.includes(perm.value);
  });
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
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
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
              :options="availablePermissions(key)"
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
