<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové nastavení' : 'Detail nastavení');

const breadcrumbs = ref([
  {
    name: 'Nastavení',
    link: '/nastaveni',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  type: 'topMenu' as string,
  translations: {} as object,
  sites: [] as number[],
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    type: string;
    translations: object;
  }>('/api/admin/setting/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response) => {
      item.value = response;
      item.value.sites = response.sites.map((site) => site.id);
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst nastavení. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/nastaveni');
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
    type: string;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/setting' : '/api/admin/setting/' + route.params.id,
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
            ? 'Nastavení bylo úspěšně vytvořeno.'
            : 'Nastavení bylo úspěšně upraveno.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/' + response.id);
      } else if (redirect) {
        router.push('/nastaveni');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit nastavení. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function addSubmenu(groups: any[], index: number) {
  if (groups[index] && !groups[index].submenu) {
    groups[index].submenu = [];
  }
  groups[index].submenu.push({
    name: '',
    link: '',
  });
}

function addRemoveItemSite(siteId) {
  if (item.value.sites.includes(siteId)) {
    item.value.sites = item.value.sites.filter((site) => site !== siteId);
    return;
  } else {
    item.value.sites.push(siteId);
  }
}

watch(selectedSiteHash, () => {
  loadItem();
});

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
  languageStore.languages.forEach((lang) => {
    // first check if translations object exists
    if (!item.value.translations) {
      item.value.translations = {};
    }
    // then check if the specific language code exists
    if (!item.value.translations[lang.code]) {
      item.value.translations[lang.code] = {};
    }
    // finally, initialize the value for that language
    item.value.translations[lang.code] = { value: { groups: [] } };
  });
});
definePageMeta({
  middleware: 'sanctum:auth',
});

const addGroup = () => {
  if (!props.item.translations[props.selectedLocale].value) {
    props.item.translations[props.selectedLocale].value = { groups: [] };
  }
  props.item.translations[props.selectedLocale].value.groups.push({
    name: '',
    link: '',
    submenu: [],
  });
};
</script>

<template>
  <div class="space-y-6 pb-20">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="settings"
      @save="saveItem"
    />

    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-6 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <Cog6ToothIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Konfigurace menu</LayoutTitle>
            </div>

            <div class="max-w-md">
              <BaseFormSelect
                v-model="item.type"
                label="Umístění v šabloně"
                name="type"
                :options="[
                  { value: 'topMenu', name: 'Horní navigace (Header)' },
                  { value: 'bottomMenu', name: 'Spodní navigace (Footer)' },
                ]"
                :disabled="route.params.id !== 'pridat'"
              />
              <p class="mt-2 text-xs text-slate-400">
                Typ menu definuje, kde se tyto odkazy na webu vykreslí.
              </p>
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="item.type === 'topMenu' || item.type === 'bottomMenu'">
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <Bars3Icon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0"
                  >Struktura odkazů ({{ selectedLocale.toUpperCase() }})</LayoutTitle
                >
              </div>
              <BaseButton type="button" variant="primary" size="md" @click="addGroup">
                <PlusIcon class="mr-2 size-4" />
                Přidat skupinu
              </BaseButton>
            </div>

            <div class="space-y-8">
              <div
                v-for="(group, index) in item.translations[selectedLocale]?.value?.groups"
                :key="index"
                class="group relative rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200 transition-all hover:bg-white hover:shadow-xl hover:shadow-slate-200/50"
              >
                <div class="grid grid-cols-12 items-end gap-4">
                  <div class="col-span-12 mb-2 flex items-center gap-2 lg:col-span-full">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400"
                      >Hlavní úroveň #{{ index + 1 }}</span
                    >
                  </div>

                  <BaseFormInput
                    v-model="group.name"
                    label="Název v menu"
                    :name="'groupName_' + index"
                    class="col-span-12 lg:col-span-5"
                    placeholder="Např. Služby"
                  />
                  <BaseFormInput
                    v-model="group.link"
                    label="Cílová URL"
                    :name="'groupLink_' + index"
                    class="col-span-12 lg:col-span-5"
                    placeholder="/nase-sluzby"
                  />

                  <div class="col-span-12 flex items-center justify-end gap-2 lg:col-span-2">
                    <BaseButton
                      type="button"
                      variant="secondary"
                      size="md"
                      class="bg-white shadow-none ring-slate-200 hover:ring-indigo-500"
                      title="Přidat podmenu"
                      @click="addSubmenu(item.translations[selectedLocale].value.groups, index)"
                    >
                      <PlusIcon class="size-4 text-indigo-600" />
                    </BaseButton>
                    <BaseButton
                      type="button"
                      variant="danger"
                      size="md"
                      class="bg-white shadow-none ring-slate-200 hover:bg-red-50"
                      title="Smazat skupinu"
                      @click="item.translations[selectedLocale].value.groups.splice(index, 1)"
                    >
                      <TrashIcon class="size-4 text-red-500" />
                    </BaseButton>
                  </div>
                </div>

                <div
                  v-if="group.submenu && group.submenu.length > 0"
                  class="ml-4 mt-6 space-y-4 border-l-2 border-indigo-100 pl-8 lg:ml-8"
                >
                  <div
                    v-for="(submenu, key) in group.submenu"
                    :key="key"
                    class="relative grid grid-cols-12 items-end gap-4 rounded-2xl bg-white p-4 ring-1 ring-slate-100"
                  >
                    <div class="absolute -left-[34px] top-1/2 h-0.5 w-8 bg-indigo-100" />

                    <BaseFormInput
                      v-model="submenu.name"
                      label="Název pododkazu"
                      :name="'submenuName_' + index + '_' + key"
                      class="col-span-12 lg:col-span-5"
                    />
                    <BaseFormInput
                      v-model="submenu.link"
                      label="URL pododkazu"
                      :name="'submenuLink_' + index + '_' + key"
                      class="col-span-12 lg:col-span-6"
                    />
                    <div class="col-span-12 flex justify-end lg:col-span-1">
                      <button
                        type="button"
                        class="mb-2.5 flex size-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        @click="group.submenu.splice(key, 1)"
                      >
                        <TrashIcon class="size-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="!item.translations[selectedLocale]?.value?.groups?.length"
              class="mt-8 flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 py-12 text-center"
            >
              <div class="mb-4 rounded-full bg-slate-50 p-4">
                <LinkIcon class="size-8 text-slate-300" />
              </div>
              <p class="text-sm font-medium text-slate-500">Zatím jste nepřidali žádné odkazy.</p>
              <BaseButton
                type="button"
                variant="secondary"
                size="sm"
                class="mt-4"
                @click="addGroup"
              >
                Vytvořit první odkaz
              </BaseButton>
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:sites="item.sites"
            :allow-image="false"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200">
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-5 text-indigo-200" />
              <h4 class="text-sm font-bold">Tip pro navigaci</h4>
            </div>
            <p class="text-xs leading-relaxed opacity-90">
              Pro lepší SEO doporučujeme v menu používat jasné názvy (např. "Ceník služeb" místo jen
              "Ceny"). U horního menu se snažte nepřekročit 5–7 hlavních skupin, aby zůstalo
              přehledné i na tabletech.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
