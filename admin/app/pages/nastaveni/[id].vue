<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import {
  TrashIcon,
  PlusIcon,
  ChatBubbleBottomCenterTextIcon,
  MegaphoneIcon,
  BuildingOffice2Icon,
  ClockIcon,
  CalendarDaysIcon,
  XMarkIcon,
  GlobeAltIcon,
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

const SOCIAL_PLATFORMS = [
  { key: 'facebook', label: 'Facebook', placeholder: 'https://facebook.com/...' },
  { key: 'instagram', label: 'Instagram', placeholder: 'https://instagram.com/...' },
  { key: 'linkedin', label: 'LinkedIn', placeholder: 'https://linkedin.com/company/...' },
  { key: 'youtube', label: 'YouTube', placeholder: 'https://youtube.com/@...' },
  { key: 'twitter', label: 'X / Twitter', placeholder: 'https://x.com/...' },
  { key: 'tiktok', label: 'TikTok', placeholder: 'https://tiktok.com/@...' },
];

const WEEK_DAYS = [
  { key: 'monday', label: 'Pondělí', short: 'Po' },
  { key: 'tuesday', label: 'Úterý', short: 'Út' },
  { key: 'wednesday', label: 'Středa', short: 'St' },
  { key: 'thursday', label: 'Čtvrtek', short: 'Čt' },
  { key: 'friday', label: 'Pátek', short: 'Pá' },
  { key: 'saturday', label: 'Sobota', short: 'So' },
  { key: 'sunday', label: 'Neděle', short: 'Ne' },
];

function defaultDays() {
  const days: Record<string, { closed: boolean; intervals: { from: string; to: string }[] }> = {};
  WEEK_DAYS.forEach((d) => {
    days[d.key] = { closed: false, intervals: [{ from: '', to: '' }] };
  });
  return days;
}

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
  if (type === 'contacts') {
    return {
      companyName: '',
      ico: '',
      dic: '',
      phone: '',
      email: '',
      address: '',
      mapEmbed: '',
      socials: SOCIAL_PLATFORMS.reduce<Record<string, string>>((acc, p) => {
        acc[p.key] = '';
        return acc;
      }, {}),
    };
  }
  if (type === 'openingHours') {
    return {
      days: defaultDays(),
      exceptions: [] as {
        date: string;
        closed: boolean;
        intervals: { from: string; to: string }[];
        note: string;
      }[],
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

function ensureOpeningHoursShape(value: any) {
  if (!value.days) value.days = defaultDays();
  WEEK_DAYS.forEach((d) => {
    if (!value.days[d.key]) {
      value.days[d.key] = { closed: false, intervals: [{ from: '', to: '' }] };
    }
    if (!Array.isArray(value.days[d.key].intervals)) {
      value.days[d.key].intervals = [{ from: '', to: '' }];
    }
  });
  if (!Array.isArray(value.exceptions)) value.exceptions = [];
}

function addInterval(dayKey: string) {
  const value = item.value.translations[selectedLocale.value].value;
  ensureOpeningHoursShape(value);
  value.days[dayKey].intervals.push({ from: '', to: '' });
}

function removeInterval(dayKey: string, index: number) {
  const value = item.value.translations[selectedLocale.value].value;
  value.days[dayKey].intervals.splice(index, 1);
  if (value.days[dayKey].intervals.length === 0) {
    value.days[dayKey].intervals.push({ from: '', to: '' });
  }
}

function toggleDayClosed(dayKey: string) {
  const value = item.value.translations[selectedLocale.value].value;
  ensureOpeningHoursShape(value);
  value.days[dayKey].closed = !value.days[dayKey].closed;
  if (value.days[dayKey].closed) {
    value.days[dayKey].intervals = [];
  } else if (value.days[dayKey].intervals.length === 0) {
    value.days[dayKey].intervals = [{ from: '', to: '' }];
  }
}

function addException() {
  const value = item.value.translations[selectedLocale.value].value;
  ensureOpeningHoursShape(value);
  value.exceptions.push({
    date: '',
    closed: true,
    intervals: [],
    note: '',
  });
}

function removeException(index: number) {
  const value = item.value.translations[selectedLocale.value].value;
  value.exceptions.splice(index, 1);
}

function toggleExceptionClosed(index: number) {
  const value = item.value.translations[selectedLocale.value].value;
  const ex = value.exceptions[index];
  ex.closed = !ex.closed;
  if (ex.closed) {
    ex.intervals = [];
  } else if (!ex.intervals || ex.intervals.length === 0) {
    ex.intervals = [{ from: '', to: '' }];
  }
}

function addExceptionInterval(index: number) {
  const value = item.value.translations[selectedLocale.value].value;
  if (!Array.isArray(value.exceptions[index].intervals)) {
    value.exceptions[index].intervals = [];
  }
  value.exceptions[index].intervals.push({ from: '', to: '' });
}

function removeExceptionInterval(exIndex: number, intIndex: number) {
  const value = item.value.translations[selectedLocale.value].value;
  value.exceptions[exIndex].intervals.splice(intIndex, 1);
}

const addGroup = () => {
  if (!item.value.translations[selectedLocale.value]) {
    item.value.translations[selectedLocale.value] = { value: { groups: [] } };
  }
  if (!item.value.translations[selectedLocale.value].value) {
    item.value.translations[selectedLocale.value].value = { groups: [] };
  }
  if (!item.value.translations[selectedLocale.value].value.groups) {
    item.value.translations[selectedLocale.value].value.groups = [];
  }
  item.value.translations[selectedLocale.value].value.groups.push({
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
                    : item.type === 'contacts'
                      ? 'Konfigurace kontaktů'
                      : item.type === 'openingHours'
                        ? 'Konfigurace otevírací doby'
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
                    { value: 'contacts', name: 'Kontakty' },
                    { value: 'openingHours', name: 'Otevírací doba' },
                  ]"
                  :disabled="route.params.id !== 'pridat'"
                />
                <p class="mt-2 text-xs text-slate-400">
                  Typ definuje, kde se obsah na webu vykreslí.
                </p>
              </div>

              <div
                class="flex flex-col justify-between rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200"
              >
                <label class="mb-2 block text-sm font-medium text-slate-700">Stav</label>
                <div class="flex items-center justify-between gap-3">
                  <BaseFormSwitch
                    v-model:enabled="item.active"
                    enabled-text="Aktivní"
                    disabled-text="Neaktivní"
                  />
                  <span
                    :class="[
                      item.active
                        ? 'bg-emerald-50 text-emerald-600'
                        : 'bg-slate-100 text-slate-500',
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

            <div v-if="item.translations[selectedLocale]?.value" class="space-y-6">
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

          <LayoutContainer v-if="item.type === 'contacts'">
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <BuildingOffice2Icon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0"
                >Kontaktní údaje ({{ selectedLocale.toUpperCase() }})</LayoutTitle
              >
            </div>

            <div v-if="item.translations[selectedLocale]?.value" class="space-y-6">
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.companyName"
                  label="Název firmy"
                  name="contactsCompanyName"
                  placeholder="Např. WebPulse s.r.o."
                />
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.ico"
                  label="IČO"
                  name="contactsIco"
                  placeholder="Např. 12345678"
                />
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.dic"
                  label="DIČ"
                  name="contactsDic"
                  placeholder="Např. CZ12345678"
                />
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.phone"
                  label="Telefon"
                  name="contactsPhone"
                  placeholder="Např. +420 123 456 789"
                />
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.email"
                  label="E-mail"
                  name="contactsEmail"
                  placeholder="info@example.com"
                  rules="email"
                />
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.address"
                  label="Adresa"
                  name="contactsAddress"
                  placeholder="Ulice 123, 110 00 Praha"
                />
              </div>

              <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <p class="mb-3 text-xs font-black uppercase tracking-widest text-slate-400">Mapa</p>
                <BaseFormInput
                  v-model="item.translations[selectedLocale].value.mapEmbed"
                  label="Embed kód mapy nebo URL"
                  name="contactsMapEmbed"
                  placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>'
                />
                <p class="mt-2 text-xs text-slate-400">
                  Vložte iframe embed kód z Google Maps nebo URL mapy.
                </p>
              </div>

              <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <div class="mb-4 flex items-center gap-2">
                  <GlobeAltIcon class="size-4 text-indigo-600" />
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">
                    Sociální sítě
                  </p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <BaseFormInput
                    v-for="platform in SOCIAL_PLATFORMS"
                    :key="platform.key"
                    v-model="item.translations[selectedLocale].value.socials[platform.key]"
                    :label="platform.label"
                    :name="'contactsSocial_' + platform.key"
                    :placeholder="platform.placeholder"
                  />
                </div>
                <p class="mt-3 text-xs text-slate-400">
                  Nechte prázdná pole, pokud konkrétní síť na webu nechcete zobrazit.
                </p>
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer v-if="item.type === 'openingHours'">
            <div class="mb-8 flex items-center gap-3 border-b border-slate-100 pb-5">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
              >
                <ClockIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0"
                >Otevírací doba ({{ selectedLocale.toUpperCase() }})</LayoutTitle
              >
            </div>

            <div v-if="item.translations[selectedLocale]?.value?.days" class="space-y-4">
              <div
                v-for="day in WEEK_DAYS"
                :key="day.key"
                class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200"
              >
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <span
                      class="flex size-9 items-center justify-center rounded-lg bg-white text-xs font-black uppercase tracking-wider text-slate-700 ring-1 ring-slate-200"
                      >{{ day.short }}</span
                    >
                    <span class="text-sm font-semibold text-slate-700">{{ day.label }}</span>
                  </div>

                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      :class="[
                        'rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                        item.translations[selectedLocale].value.days[day.key]?.closed
                          ? 'bg-red-50 text-red-600 ring-1 ring-red-200'
                          : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100',
                      ]"
                      @click="toggleDayClosed(day.key)"
                    >
                      {{
                        item.translations[selectedLocale].value.days[day.key]?.closed
                          ? 'Zavřeno'
                          : 'Otevřeno'
                      }}
                    </button>
                    <BaseButton
                      v-if="!item.translations[selectedLocale].value.days[day.key]?.closed"
                      type="button"
                      variant="secondary"
                      size="md"
                      class="bg-white shadow-none ring-slate-200 hover:ring-indigo-500"
                      title="Přidat interval"
                      @click="addInterval(day.key)"
                    >
                      <PlusIcon class="size-4 text-indigo-600" />
                    </BaseButton>
                  </div>
                </div>

                <div
                  v-if="!item.translations[selectedLocale].value.days[day.key]?.closed"
                  class="mt-4 space-y-2"
                >
                  <div
                    v-for="(interval, intIndex) in item.translations[selectedLocale].value.days[
                      day.key
                    ].intervals"
                    :key="intIndex"
                    class="flex items-center gap-2"
                  >
                    <BaseFormInput
                      v-model="interval.from"
                      label="Od"
                      :name="'oh_' + day.key + '_from_' + intIndex"
                      type="time"
                      class="flex-1"
                    />
                    <span class="mt-6 text-slate-400">–</span>
                    <BaseFormInput
                      v-model="interval.to"
                      label="Do"
                      :name="'oh_' + day.key + '_to_' + intIndex"
                      type="time"
                      class="flex-1"
                    />
                    <button
                      type="button"
                      class="mt-6 flex size-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                      title="Odebrat interval"
                      @click="removeInterval(day.key, intIndex)"
                    >
                      <XMarkIcon class="size-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-10 border-t border-slate-100 pt-6">
              <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                  >
                    <CalendarDaysIcon class="size-5" />
                  </div>
                  <LayoutTitle class="!mb-0">Výjimky a svátky</LayoutTitle>
                </div>
                <BaseButton type="button" variant="primary" size="md" @click="addException">
                  <PlusIcon class="mr-2 size-4" />
                  Přidat výjimku
                </BaseButton>
              </div>

              <div
                v-if="item.translations[selectedLocale]?.value?.exceptions?.length"
                class="space-y-4"
              >
                <div
                  v-for="(exception, exIndex) in item.translations[selectedLocale].value.exceptions"
                  :key="exIndex"
                  class="rounded-2xl bg-amber-50/40 p-5 ring-1 ring-amber-100"
                >
                  <div class="grid grid-cols-12 items-end gap-4">
                    <BaseFormInput
                      v-model="exception.date"
                      label="Datum"
                      :name="'exception_date_' + exIndex"
                      type="date"
                      class="col-span-12 md:col-span-3"
                    />
                    <BaseFormInput
                      v-model="exception.note"
                      label="Popis (volitelné)"
                      :name="'exception_note_' + exIndex"
                      placeholder="Např. Štědrý den"
                      class="col-span-12 md:col-span-6"
                    />
                    <div class="col-span-12 flex items-center justify-end gap-2 md:col-span-3">
                      <button
                        type="button"
                        :class="[
                          'rounded-full px-3 py-1 text-xs font-semibold transition-colors',
                          exception.closed
                            ? 'bg-red-50 text-red-600 ring-1 ring-red-200'
                            : 'bg-white text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100',
                        ]"
                        @click="toggleExceptionClosed(exIndex)"
                      >
                        {{ exception.closed ? 'Zavřeno' : 'Otevřeno' }}
                      </button>
                      <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        title="Smazat výjimku"
                        @click="removeException(exIndex)"
                      >
                        <TrashIcon class="size-4" />
                      </button>
                    </div>
                  </div>

                  <div v-if="!exception.closed" class="mt-4 space-y-2">
                    <div
                      v-for="(interval, intIndex) in exception.intervals"
                      :key="intIndex"
                      class="flex items-center gap-2"
                    >
                      <BaseFormInput
                        v-model="interval.from"
                        label="Od"
                        :name="'exception_' + exIndex + '_from_' + intIndex"
                        type="time"
                        class="flex-1"
                      />
                      <span class="mt-6 text-slate-400">–</span>
                      <BaseFormInput
                        v-model="interval.to"
                        label="Do"
                        :name="'exception_' + exIndex + '_to_' + intIndex"
                        type="time"
                        class="flex-1"
                      />
                      <button
                        type="button"
                        class="mt-6 flex size-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        title="Odebrat interval"
                        @click="removeExceptionInterval(exIndex, intIndex)"
                      >
                        <XMarkIcon class="size-4" />
                      </button>
                    </div>
                    <BaseButton
                      type="button"
                      variant="secondary"
                      size="sm"
                      class="bg-white shadow-none ring-slate-200 hover:ring-indigo-500"
                      @click="addExceptionInterval(exIndex)"
                    >
                      <PlusIcon class="mr-1 size-3 text-indigo-600" />
                      Přidat interval
                    </BaseButton>
                  </div>
                </div>
              </div>

              <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-amber-200 py-10 text-center"
              >
                <div class="mb-3 rounded-full bg-amber-50 p-3">
                  <CalendarDaysIcon class="size-6 text-amber-400" />
                </div>
                <p class="text-sm font-medium text-slate-500">
                  Zatím nejsou nastavené žádné výjimky.
                </p>
                <p class="mt-1 text-xs text-slate-400">
                  Přidejte svátky nebo dny, kdy se otevírací doba liší.
                </p>
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
        </aside>
      </div>
    </Form>
  </div>
</template>
