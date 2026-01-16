<script setup lang="ts">
import {inject, ref} from 'vue';
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
</script>

<template>
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="settings"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
          <div class="grid grid-cols-2 gap-x-8">
            <BaseFormSelect
              v-model="item.type"
              label="Typ nastavení"
              name="type"
              class="col-span-1 w-full"
              :options="[
                { value: 'topMenu', name: 'Horní menu' },
                { value: 'bottomMenu', name: 'Spodní menu' },
              ]"
              :disabled="route.params.id !== 'pridat'"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full">
          <div class="col-span-1">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
            <LayoutDivider v-if="user && user.sites">Zařazení do stránek</LayoutDivider>
            <BaseFormCheckbox
              v-for="(site, key) in user.sites"
              v-if="item.sites && user.sites"
              :key="key"
              :label="site.name"
              :name="site.id"
              :value="item.sites.includes(site.id)"
              :checked="item.sites.includes(site.id)"
              class="col-span-full"
              :reverse="true"
              label-color="grayCustom"
              @change="addRemoveItemSite(site.id)"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer
          v-if="item.type === 'topMenu' || item.type === 'bottomMenu'"
          class="col-span-full mt-0 w-full"
        >
          <div class="grid grid-cols-2 gap-x-8 gap-y-10">
            <div
              v-for="(group, index) in item.translations[selectedLocale].value.groups"
              v-if="item.translations[selectedLocale] && item.translations[selectedLocale].value"
              :key="index"
              class="col-span-2 grid grid-cols-12 gap-x-4 gap-y-2"
            >
              <BaseFormInput
                v-model="group.name"
                label="Název skupiny"
                :name="'groupName_' + index"
                class="col-span-5"
              /><BaseFormInput
                v-model="group.link"
                label="Odkaz"
                :name="'groupLink_' + index"
                class="col-span-5"
              />
              <div class="col-span-2 flex items-end justify-end gap-x-6">
                <div
                  class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-dangerLight ring-1 ring-danger"
                  @click="item.translations[selectedLocale].value.groups.splice(index, 1)"
                >
                  <TrashIcon class="h-4 w-4 text-white" />
                </div>
                <div
                  class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-successLight ring-1 ring-success"
                  @click="addSubmenu(item.translations[selectedLocale].value.groups, index)"
                >
                  <PlusIcon class="h-4 w-4 text-white" />
                </div>
              </div>
              <div
                v-for="(submenu, key) in group.submenu"
                v-if="group.submenu"
                :key="key"
                class="col-span-full grid grid-cols-12 gap-x-4 gap-y-2"
              >
                <div class="col-span-1">&nbsp;</div>
                <BaseFormInput
                  v-model="submenu.name"
                  label="Název odkazu"
                  :name="'submenuName_' + index + '_' + key"
                  class="col-span-5"
                />
                <BaseFormInput
                  v-model="submenu.link"
                  label="Odkaz"
                  :name="'submenuLink_' + index + '_' + key"
                  class="col-span-5"
                />
                <div class="col-span-1 flex items-end justify-end gap-x-6">
                  <div
                    class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-dangerLight ring-1 ring-danger"
                    @click="group.submenu.splice(key, 1)"
                  >
                    <TrashIcon class="h-4 w-4 text-white" />
                  </div>
                </div>
              </div>
            </div>
            <div class="col-span-full text-center">
              <BaseButton
                type="button"
                color="primary"
                size="lg"
                @click="item.translations[selectedLocale].value.groups.push({ name: '', link: '' })"
              >
                Přidat skupinu odkazů</BaseButton
              >
            </div>
          </div>
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
