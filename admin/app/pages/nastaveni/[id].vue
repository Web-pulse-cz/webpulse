<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  TrashIcon,
  PlusIcon,
  ChatBubbleBottomCenterTextIcon,
  MegaphoneIcon,
} from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const route = useRoute();
const router = useRouter();
const { formRef, validateForm } = useFormValidation();

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
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
});

function defaultValueForType(type: string) {
  if (type === 'popup') {
    return { title: '', content: '', buttonText: '', buttonLink: '' };
  }
  if (type === 'bar') {
    return {
      text: '',
      buttonText: '',
      buttonLink: '',
      backgroundColor: '#1e293b',
      textColor: '#ffffff',
    };
  }
  return { groups: [] };
}

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
  if (!(await validateForm())) return;

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
    item.value.translations[lang.code] = { value: defaultValueForType(item.value.type) };
  });
});

watch(
  () => item.value.type,
  (newType) => {
    if (route.params.id !== 'pridat') return;
    languageStore.languages.forEach((lang) => {
      if (!item.value.translations[lang.code]) {
        item.value.translations[lang.code] = {};
      }
      item.value.translations[lang.code] = { value: defaultValueForType(newType) };
    });
  },
);
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

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-6 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-white"
              >
                <Cog6ToothIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">{{
                item.type === 'popup'
                  ? 'Konfigurace pop-upu'
                  : item.type === 'bar'
                    ? 'Konfigurace proužku'
                    : 'Konfigurace menu'
              }}</LayoutTitle>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <BaseFormSelect
                  v-model="item.type"
                  label="Umístění v šabloně"
                  name="type"
                  :options="[
                    { value: 'topMenu', name: 'Horní navigace (Header)' },
                    { value: 'bottomMenu', name: 'Spodní navigace (Footer)' },
                    { value: 'popup', name: 'Pop-up okno' },
                    { value: 'bar', name: 'Oznamovací proužek' },
                  ]"
                  :disabled="route.params.id !== 'pridat'"
                />
                <p class="mt-2 text-xs text-slate-400">
                  Typ definuje, kde se obsah na webu vykreslí.
                </p>
              </div>

              <div class="flex flex-col justify-between rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                <label class="mb-2 block text-sm font-medium text-slate-700">Stav</label>
                <div class="flex items-center justify-between gap-3">
                  <BaseFormSwitch
                    v-model:enabled="item.active"
                    enabled-text="Aktivní"
                    disabled-text="Neaktivní"
                  />
                  <span
                    :class="[
                      item.active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500',
                      'rounded-full px-2.5 py-1 text-xs font-semibold',
                    ]"
                  >
                    {{ item.active ? 'Zobrazeno na webu' : 'Skryto' }}
                  </span>
                </div>
              </div>
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

          <LayoutContainer v-if="item.type === 'popup'">
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <ChatBubbleBottomCenterTextIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0"
                >Obsah pop-upu ({{ selectedLocale.toUpperCase() }})</LayoutTitle
              >
            </div>

            <div
              v-if="item.translations[selectedLocale]?.value"
              class="space-y-6"
            >
              <BaseFormInput
                v-model="item.translations[selectedLocale].value.title"
                label="Nadpis"
                name="popupTitle"
                placeholder="Např. Získejte 10% slevu"
                rules="required"
              />

              <BaseFormEditor
                v-model="item.translations[selectedLocale].value.content"
                label="Obsah"
                name="popupContent"
              />

              <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <p class="mb-4 text-xs font-black uppercase tracking-widest text-slate-400">
                  Volitelné tlačítko (CTA)
                </p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.buttonText"
                    label="Text tlačítka"
                    name="popupButtonText"
                    placeholder="Např. Chci slevu"
                  />
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.buttonLink"
                    label="Odkaz tlačítka"
                    name="popupButtonLink"
                    placeholder="/kontakt"
                  />
                </div>
                <p class="mt-3 text-xs text-slate-400">
                  Nechte obě pole prázdná, pokud tlačítko nechcete zobrazit.
                </p>
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="item.type === 'bar'">
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <MegaphoneIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0"
                >Obsah proužku ({{ selectedLocale.toUpperCase() }})</LayoutTitle
              >
            </div>

            <div v-if="item.translations[selectedLocale]?.value" class="space-y-6">
              <div
                class="rounded-2xl p-5 ring-1 ring-slate-200"
                :style="{
                  backgroundColor:
                    item.translations[selectedLocale].value.backgroundColor || '#1e293b',
                  color: item.translations[selectedLocale].value.textColor || '#ffffff',
                }"
              >
                <p class="mb-2 text-[10px] font-black uppercase tracking-widest opacity-60">
                  Náhled
                </p>
                <div class="flex flex-wrap items-center justify-center gap-4 text-sm font-medium">
                  <span>{{
                    item.translations[selectedLocale].value.text || 'Text proužku se zobrazí zde'
                  }}</span>
                  <a
                    v-if="
                      item.translations[selectedLocale].value.buttonText &&
                      item.translations[selectedLocale].value.buttonLink
                    "
                    class="rounded-full bg-white/20 px-4 py-1 text-xs font-semibold ring-1 ring-white/30"
                  >
                    {{ item.translations[selectedLocale].value.buttonText }}
                  </a>
                </div>
              </div>

              <BaseFormInput
                v-model="item.translations[selectedLocale].value.text"
                label="Text proužku"
                name="barText"
                placeholder="Např. 2+1 zdarma na vybrané pobyty"
                rules="required"
              />

              <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <p class="mb-4 text-xs font-black uppercase tracking-widest text-slate-400">
                  Volitelné tlačítko (CTA)
                </p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.buttonText"
                    label="Text tlačítka"
                    name="barButtonText"
                    placeholder="Např. Chci rezervovat"
                  />
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.buttonLink"
                    label="Odkaz tlačítka"
                    name="barButtonLink"
                    placeholder="/kontakt"
                  />
                </div>
                <p class="mt-3 text-xs text-slate-400">
                  Nechte obě pole prázdná, pokud tlačítko nechcete zobrazit.
                </p>
              </div>

              <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <p class="mb-4 text-xs font-black uppercase tracking-widest text-slate-400">
                  Barvy
                </p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.backgroundColor"
                    label="Barva pozadí"
                    name="barBackgroundColor"
                    type="color"
                  />
                  <BaseFormInput
                    v-model="item.translations[selectedLocale].value.textColor"
                    label="Barva textu"
                    name="barTextColor"
                    type="color"
                  />
                </div>
              </div>
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

          <div
            v-if="item.type === 'topMenu' || item.type === 'bottomMenu'"
            class="mt-6 rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200"
          >
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

          <div
            v-else-if="item.type === 'popup'"
            class="mt-6 rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200"
          >
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-5 text-indigo-200" />
              <h4 class="text-sm font-bold">Tip pro pop-up</h4>
            </div>
            <p class="text-xs leading-relaxed opacity-90">
              Pop-up bude na webu zobrazen pouze pokud je nastavení aktivní. Pro nejlepší výsledky
              volte stručný nadpis a jasné CTA tlačítko.
            </p>
          </div>

          <div
            v-else-if="item.type === 'bar'"
            class="mt-6 rounded-3xl bg-indigo-600 p-6 text-white shadow-xl shadow-indigo-200"
          >
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-5 text-indigo-200" />
              <h4 class="text-sm font-bold">Tip pro proužek</h4>
            </div>
            <p class="text-xs leading-relaxed opacity-90">
              Proužek se zobrazí nad hlavičkou webu. Návštěvník ho může zavřít a pokud změníte
              obsah, automaticky se mu znovu objeví. Ideální pro akce jako "2+1 zdarma" nebo sezónní
              slevy.
            </p>
          </div>
        </aside>
      </div>
    </Form>
  </div>
</template>
