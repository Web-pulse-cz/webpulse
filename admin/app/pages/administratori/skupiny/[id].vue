<script setup lang="ts">
import { ref, inject } from 'vue';

import { Form } from 'vee-validate';
import {
  InformationCircleIcon,
  KeyIcon,
  ShieldCheckIcon,
  UserGroupIcon,
  UserIcon,
} from '@heroicons/vue/24/outline';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

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

const moduleGroups = ref([
  {
    title: 'Obsah',
    modules: [
      { slug: 'posts', name: 'Blogové články' },
      { slug: 'pages', name: 'Informační stránky' },
      { slug: 'novelties', name: 'Novinky' },
      { slug: 'services', name: 'Služby' },
      { slug: 'events', name: 'Události' },
      { slug: 'reviews', name: 'Reference' },
      { slug: 'logos', name: 'Loga klientů' },
      { slug: 'photo_galleries', name: 'Fotogalerie' },
      { slug: 'careers', name: 'Pracovní pozice' },
      { slug: 'quizzes', name: 'Kvízy' },
      { slug: 'faqs', name: 'FAQ' },
    ],
  },
  {
    title: 'Restaurace',
    modules: [
      { slug: 'allergens', name: 'Alergeny' },
      { slug: 'foodstuffs', name: 'Potraviny' },
      { slug: 'meals', name: 'Jídla' },
      { slug: 'recipes', name: 'Recepty' },
      { slug: 'menus', name: 'Menu' },
      { slug: 'restaurant_tables', name: 'Stoly' },
      { slug: 'reservations', name: 'Rezervace' },
    ],
  },
  {
    title: 'Ubytování',
    modules: [
      { slug: 'apartments', name: 'Apartmány' },
      { slug: 'apartment_types', name: 'Typy apartmánů' },
      { slug: 'buildings', name: 'Budovy' },
      { slug: 'amenities', name: 'Vybavení' },
      { slug: 'seasons', name: 'Roční období' },
      { slug: 'apartment_reservations', name: 'Rezervace ubytování' },
    ],
  },
  {
    title: 'Zákazníci',
    modules: [
      { slug: 'customers', name: 'Zákazníci' },
      { slug: 'vouchers', name: 'Vouchery' },
      { slug: 'newsletters', name: 'Odběry newsletteru' },
      { slug: 'demands', name: 'Poptávky' },
    ],
  },
  {
    title: 'Byznys a osobní růst',
    modules: [
      { slug: 'contacts', name: 'Kontakty' },
      { slug: 'users_has_activities', name: 'Aktivita' },
      { slug: 'message_blueprints', name: 'Šablony zpráv' },
      { slug: 'calendars', name: 'Kalendář' },
      { slug: 'cashflows', name: 'Cashflow' },
      { slug: 'leagues', name: 'Ligy' },
    ],
  },
  {
    title: 'Vedení firmy',
    modules: [
      { slug: 'clients', name: 'Klienti' },
      { slug: 'invoices', name: 'Faktury' },
      { slug: 'projects', name: 'Projekty' },
      { slug: 'price_offers', name: 'Cenové nabídky' },
      { slug: 'project_time_entries', name: 'Sledování času' },
      { slug: 'project_tasks', name: 'Úkoly' },
      { slug: 'employees', name: 'Zaměstnanci' },
      { slug: 'shifts', name: 'Směny' },
      { slug: 'employee_contracts', name: 'Smlouvy' },
      { slug: 'biographies', name: 'Životopisy' },
    ],
  },
  {
    title: 'Nastavení a správa',
    modules: [
      { slug: 'users', name: 'Administrátoři' },
      { slug: 'sites', name: 'Stránky' },
      { slug: 'filemanagers', name: 'Filemanager' },
      { slug: 'settings', name: 'Nastavení' },
      { slug: 'changelogs', name: 'Changelog' },
      { slug: 'activities', name: 'Aktivity' },
      { slug: 'emails', name: 'E-maily' },
      { slug: 'languages', name: 'Jazyky' },
      { slug: 'countries', name: 'Země' },
      { slug: 'currencies', name: 'Měny' },
      { slug: 'tax_rates', name: 'Sazby DPH' },
      { slug: 'trackings', name: 'Trackování' },
    ],
  },
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
  if (!(await validateForm())) return;

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

function getPermission(slug: string) {
  return item.value.permissions.find((p: any) => p.slug === slug);
}

function hasPermission(slug: string, type: 'view' | 'edit' | 'delete'): boolean {
  const perm = getPermission(slug);
  return perm?.permissions?.[type] === true;
}

function togglePermission(slug: string, name: string, type: 'view' | 'edit' | 'delete') {
  let perm = getPermission(slug);
  if (!perm) {
    perm = { name, slug, permissions: { view: false, edit: false, delete: false } };
    item.value.permissions.push(perm);
  }
  perm.permissions[type] = !perm.permissions[type];
  if (!perm.permissions.view && !perm.permissions.edit && !perm.permissions.delete) {
    const idx = item.value.permissions.findIndex((p: any) => p.slug === slug);
    if (idx !== -1) item.value.permissions.splice(idx, 1);
  }
}

function toggleGroupAll(group: any, type: 'view' | 'edit' | 'delete') {
  const allSet = group.modules.every((mod: any) => hasPermission(mod.slug, type));
  group.modules.forEach((mod: any) => {
    const perm = getPermission(mod.slug);
    if (allSet) {
      if (perm) {
        perm.permissions[type] = false;
        if (!perm.permissions.view && !perm.permissions.edit && !perm.permissions.delete) {
          const idx = item.value.permissions.findIndex((p: any) => p.slug === mod.slug);
          if (idx !== -1) item.value.permissions.splice(idx, 1);
        }
      }
    } else {
      if (!perm) {
        item.value.permissions.push({
          name: mod.name,
          slug: mod.slug,
          permissions: { view: false, edit: false, delete: false, [type]: true },
        });
      } else {
        perm.permissions[type] = true;
      }
    }
  });
}

function isGroupAllChecked(group: any, type: 'view' | 'edit' | 'delete'): boolean {
  return group.modules.every((mod: any) => hasPermission(mod.slug, type));
}

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

    <Form ref="formRef" @submit="saveItem">
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
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <KeyIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Přístupy k modulům systému</LayoutTitle>
            </div>

            <div class="space-y-5">
              <div
                v-for="group in moduleGroups"
                :key="group.title"
                class="overflow-hidden rounded-2xl ring-1 ring-slate-200"
              >
                <!-- Group header with bulk toggles -->
                <div class="flex items-center justify-between bg-slate-50 px-5 py-3">
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500">{{
                    group.title
                  }}</span>
                  <div class="hidden items-center gap-6 sm:flex">
                    <button
                      type="button"
                      class="text-[10px] font-bold uppercase tracking-widest transition"
                      :class="
                        isGroupAllChecked(group, 'view')
                          ? 'text-indigo-600'
                          : 'text-slate-400 hover:text-slate-600'
                      "
                      @click="toggleGroupAll(group, 'view')"
                    >
                      Zobrazit
                    </button>
                    <button
                      type="button"
                      class="text-[10px] font-bold uppercase tracking-widest transition"
                      :class="
                        isGroupAllChecked(group, 'edit')
                          ? 'text-indigo-600'
                          : 'text-slate-400 hover:text-slate-600'
                      "
                      @click="toggleGroupAll(group, 'edit')"
                    >
                      Editovat
                    </button>
                    <button
                      type="button"
                      class="text-[10px] font-bold uppercase tracking-widest transition"
                      :class="
                        isGroupAllChecked(group, 'delete')
                          ? 'text-indigo-600'
                          : 'text-slate-400 hover:text-slate-600'
                      "
                      @click="toggleGroupAll(group, 'delete')"
                    >
                      Mazat
                    </button>
                  </div>
                </div>

                <!-- Module rows -->
                <div class="divide-y divide-slate-100">
                  <div
                    v-for="mod in group.modules"
                    :key="mod.slug"
                    class="grid grid-cols-1 items-center gap-2 px-5 py-3 transition-colors hover:bg-slate-50/50 sm:grid-cols-12"
                  >
                    <div
                      class="col-span-1 text-sm font-medium sm:col-span-6"
                      :class="
                        hasPermission(mod.slug, 'view') ||
                        hasPermission(mod.slug, 'edit') ||
                        hasPermission(mod.slug, 'delete')
                          ? 'text-slate-900'
                          : 'text-slate-400'
                      "
                    >
                      {{ mod.name }}
                    </div>
                    <div
                      class="col-span-1 flex items-center justify-between sm:col-span-2 sm:justify-center"
                    >
                      <span class="text-xs text-slate-400 sm:hidden">Zobrazit</span>
                      <BaseFormCheckbox
                        :value="hasPermission(mod.slug, 'view')"
                        :checked="hasPermission(mod.slug, 'view')"
                        label=""
                        :name="mod.slug + '_view'"
                        class="!mb-0"
                        @click="togglePermission(mod.slug, mod.name, 'view')"
                      />
                    </div>
                    <div
                      class="col-span-1 flex items-center justify-between sm:col-span-2 sm:justify-center"
                    >
                      <span class="text-xs text-slate-400 sm:hidden">Editovat</span>
                      <BaseFormCheckbox
                        :value="hasPermission(mod.slug, 'edit')"
                        :checked="hasPermission(mod.slug, 'edit')"
                        label=""
                        :name="mod.slug + '_edit'"
                        class="!mb-0"
                        @click="togglePermission(mod.slug, mod.name, 'edit')"
                      />
                    </div>
                    <div
                      class="col-span-1 flex items-center justify-between sm:col-span-2 sm:justify-center"
                    >
                      <span class="text-xs text-slate-400 sm:hidden">Mazat</span>
                      <BaseFormCheckbox
                        :value="hasPermission(mod.slug, 'delete')"
                        :checked="hasPermission(mod.slug, 'delete')"
                        label=""
                        :name="mod.slug + '_delete'"
                        class="!mb-0"
                        @click="togglePermission(mod.slug, mod.name, 'delete')"
                      />
                    </div>
                  </div>
                </div>
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
