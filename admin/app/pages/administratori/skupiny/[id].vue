<script setup lang="ts">
import { ref, inject } from 'vue';

import { Form } from 'vee-validate';
import {
  InformationCircleIcon,
  KeyIcon,
  PlusIcon,
  ShieldCheckIcon,
  ShieldExclamationIcon,
  TrashIcon,
  UserGroupIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

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
  { name: 'Administrátoři', value: 'Administrátoři', slug: 'users' },
  { name: 'Aktivita', value: 'Aktivita', slug: 'users_has_activities' },
  { name: 'Aktivity', value: 'Aktivity', slug: 'activities' },
  { name: 'Alergeny', value: 'Alergeny', slug: 'allergens' },
  { name: 'Blogové články', value: 'Blogové články', slug: 'posts' },
  { name: 'Cashflow', value: 'Cashflow', slug: 'cashflows' },
  { name: 'Cenové nabídky', value: 'Cenové nabídky', slug: 'price_offers' },
  { name: 'Changelog', value: 'Changelog', slug: 'changelogs' },
  { name: 'Zákazníci', value: 'Zákazníci', slug: 'customers' },
  { name: 'E-maily', value: 'E-maily', slug: 'emails' },
  { name: 'Faktury', value: 'Faktury', slug: 'invoices' },
  { name: 'FAQ', value: 'FAQ', slug: 'faqs' },
  { name: 'Informační stránky', value: 'Informační stránky', slug: 'pages' },
  { name: 'Jazyky', value: 'Jazyky', slug: 'languages' },
  { name: 'Jídla', value: 'Jídla', slug: 'meals' },
  { name: 'Kalendář', value: 'Kalendář', slug: 'calendars' },
  { name: 'Klienti', value: 'Klienti', slug: 'clients' },
  { name: 'Kontakty', value: 'Kontakty', slug: 'contacts' },
  { name: 'Kvízy', value: 'Kvízy', slug: 'quizzes' },
  { name: 'Ligy', value: 'Ligy', slug: 'leagues' },
  { name: 'Loga klientů', value: 'Loga klientů', slug: 'logos' },
  { name: 'Menu', value: 'Menu', slug: 'menus' },
  { name: 'Měny', value: 'Měny', slug: 'currencies' },
  { name: 'Nastavení', value: 'Nastavení', slug: 'settings' },
  { name: 'Novinky', value: 'Novinky', slug: 'novelties' },
  { name: 'Odběry newsletteru', value: 'Odběry newsletteru', slug: 'newsletters' },
  { name: 'Poptávky', value: 'Poptávky', slug: 'demands' },
  { name: 'Potraviny', value: 'Potraviny', slug: 'foodstuffs' },
  { name: 'Pracovní pozice', value: 'Pracovní pozice', slug: 'careers' },
  { name: 'Projekty', value: 'Projekty', slug: 'projects' },
  { name: 'Recepty', value: 'Recepty', slug: 'recipes' },
  { name: 'Reference', value: 'Reference', slug: 'reviews' },
  { name: 'Rezervace', value: 'Rezervace', slug: 'reservations' },
  { name: 'Sazby DPH', value: 'Sazby DPH', slug: 'tax_rates' },
  { name: 'Služby', value: 'Služby', slug: 'services' },
  { name: 'Směny', value: 'Směny', slug: 'shifts' },
  { name: 'Smlouvy', value: 'Smlouvy', slug: 'employee_contracts' },
  { name: 'Stoly', value: 'Stoly', slug: 'restaurant_tables' },
  { name: 'Stránky', value: 'Stránky', slug: 'sites' },
  { name: 'Šablony zpráv', value: 'Šablony zpráv', slug: 'message_blueprints' },
  { name: 'Sledování času', value: 'Sledování času', slug: 'project_time_entries' },
  { name: 'Trackování', value: 'Trackování', slug: 'trackings' },
  { name: 'Události', value: 'Události', slug: 'events' },
  { name: 'Vouchery', value: 'Vouchery', slug: 'vouchers' },
  { name: 'Úkoly', value: 'Úkoly', slug: 'project_tasks' },
  { name: 'Zaměstnanci', value: 'Zaměstnanci', slug: 'employees' },
  { name: 'Země', value: 'Země', slug: 'countries' },
  { name: 'Životopisy', value: 'Životopisy', slug: 'biographies' },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  permissions: [] as [],
  sites: [] as number[],
  users: [] as any[],
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
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = response.sites?.map((site: any) => site.id) || [];
      item.value.users = response.users || [];
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
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst administrátorskou skupinu. Zkuste to prosím později.',
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
        'X-Site-Hash': selectedSiteHash.value,
      },
    },
  )
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Administrátorská skupina byla úspěšně vytvořena.'
            : 'Administrátorská skupina byla úspěšně upravena.',
        severity: 'success',
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
      $toast.show({
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} administrátorskou skupinu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
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

watch(selectedSiteHash, () => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
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
                <ShieldCheckIcon class="size-6" />
              </div>
              <LayoutTitle class="!mb-0">Identita uživatelské skupiny</LayoutTitle>
            </div>

            <div class="max-w-xl">
              <BaseFormInput
                v-model="item.name"
                label="Název role"
                type="text"
                name="name"
                rules="required|min:3"
                placeholder="Např. Manažer pobočky, Editor obsahu..."
              />
              <p class="mt-2 text-sm italic text-slate-400">
                Tento název uvidíte přiřazený u detailu uživatele.
              </p>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <KeyIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Přístupy k modulům systému</LayoutTitle>
              </div>

              <BaseButton type="button" variant="secondary" size="md" @click="addPermission">
                <PlusIcon class="mr-2 size-4" />
                Přidat modul
              </BaseButton>
            </div>

            <div class="space-y-4">
              <div
                class="hidden grid-cols-12 gap-4 px-6 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 lg:grid"
              >
                <div class="col-span-4">Modul / Sekce</div>
                <div class="col-span-2 text-center">Zobrazit</div>
                <div class="col-span-2 text-center">Editovat</div>
                <div class="col-span-2 text-center">Mazat</div>
                <div class="col-span-2"></div>
              </div>

              <div
                v-for="(permission, key) in item.permissions"
                :key="key"
                class="group relative grid grid-cols-1 gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 transition-all hover:bg-white hover:shadow-md lg:grid-cols-12 lg:items-center lg:px-6"
              >
                <div class="col-span-1 lg:col-span-4">
                  <BaseFormSelect
                    v-model="permission.name"
                    label=""
                    :name="key + '_name'"
                    rules="required"
                    :options="availablePermissions(key)"
                    class="!mb-0"
                  />
                </div>

                <div
                  class="col-span-1 flex items-center justify-between rounded-xl bg-white p-2 px-4 ring-1 ring-slate-100 lg:col-span-2 lg:justify-center lg:bg-transparent lg:ring-0"
                >
                  <span class="text-xs font-bold text-slate-500 lg:hidden">Zobrazit</span>
                  <BaseFormCheckbox
                    v-model="permission.permissions.view"
                    label=""
                    :name="key + '_view'"
                    class="!mb-0"
                  />
                </div>

                <div
                  class="col-span-1 flex items-center justify-between rounded-xl bg-white p-2 px-4 ring-1 ring-slate-100 lg:col-span-2 lg:justify-center lg:bg-transparent lg:ring-0"
                >
                  <span class="text-xs font-bold text-slate-500 lg:hidden">Editovat</span>
                  <BaseFormCheckbox
                    v-model="permission.permissions.edit"
                    label=""
                    :name="key + '_edit'"
                    class="!mb-0"
                  />
                </div>

                <div
                  class="col-span-1 flex items-center justify-between rounded-xl bg-white p-2 px-4 ring-1 ring-slate-100 lg:col-span-2 lg:justify-center lg:bg-transparent lg:ring-0"
                >
                  <span class="text-xs font-bold text-slate-500 lg:hidden">Mazat</span>
                  <BaseFormCheckbox
                    v-model="permission.permissions.delete"
                    label=""
                    :name="key + '_delete'"
                    class="!mb-0"
                  />
                </div>

                <div class="col-span-1 flex justify-end lg:col-span-2">
                  <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-full text-slate-300 transition-colors hover:bg-red-50 hover:text-red-500"
                    @click="removePermission(key)"
                  >
                    <TrashIcon class="size-5" />
                  </button>
                </div>
              </div>

              <div
                v-if="!item.permissions || Object.keys(item.permissions).length === 0"
                class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 py-12 text-center"
              >
                <ShieldExclamationIcon class="mb-4 size-10 text-slate-300" />
                <p class="text-sm font-medium text-slate-500">
                  Tato role nemá definována žádná specifická oprávnění.
                </p>
                <BaseButton
                  type="button"
                  variant="secondary"
                  size="sm"
                  class="mt-4"
                  @click="addPermission"
                >
                  Vytvořit první přístup
                </BaseButton>
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="item.id">
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                >
                  <UserGroupIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Uživatelé v této skupině</LayoutTitle>
              </div>
              <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                {{ item.users?.length || 0 }}
              </span>
            </div>

            <div v-if="item.users && item.users.length > 0" class="space-y-2">
              <NuxtLink
                v-for="u in item.users"
                :key="u.id"
                :to="`/administratori/${u.id}`"
                class="group flex items-center gap-4 rounded-2xl p-3 transition-all hover:bg-slate-50"
              >
                <div
                  class="flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 group-hover:bg-slate-200"
                >
                  <img
                    v-if="u.avatar"
                    :src="u.avatar"
                    :alt="`${u.firstname} ${u.lastname}`"
                    class="size-10 rounded-full object-cover"
                  />
                  <UserIcon v-else class="size-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-800 group-hover:text-slate-900">
                    {{ u.firstname }} {{ u.lastname }}
                  </p>
                  <p class="text-xs text-slate-400">{{ u.email }}</p>
                </div>
              </NuxtLink>
            </div>

            <div
              v-else
              class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 py-12 text-center"
            >
              <UserGroupIcon class="mb-4 size-10 text-slate-300" />
              <p class="text-sm font-medium text-slate-500">
                V této skupině zatím nejsou žádní uživatelé.
              </p>
              <p class="mt-1 text-xs text-slate-400">
                Uživatele přiřadíte ke skupině v jeho detailu.
              </p>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 space-y-6 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:sites="item.sites"
            :allow-translations="false"
            :allow-image="false"
            :allow-is-active="false"
            class="shadow-sm"
          />

          <div class="rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200">
            <div class="mb-4 flex items-center gap-2">
              <InformationCircleIcon class="size-5 text-indigo-200" />
              <h4 class="text-sm font-bold uppercase tracking-wider">Bezpečnostní tip</h4>
            </div>
            <p class="text-xs leading-relaxed opacity-80">
              Při nastavování rolí postupujte podle principu <strong>"Nejméně privilegií"</strong>.
              Uživatel by měl mít přístup pouze k těm modulům, které nezbytně potřebuje ke své
              práci.
            </p>
          </div>

          <div class="rounded-3xl border border-dashed border-slate-300 p-6">
            <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400">
              Dědičnost
            </h5>
            <p class="mt-2 text-xs leading-relaxed text-slate-500">
              Admin skupina má automaticky přístup ke všem modulům bez nutnosti explicitního
              definování v této matici.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
