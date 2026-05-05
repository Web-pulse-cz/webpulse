<script setup lang="ts">
import { inject, ref, computed, watch } from 'vue';
import { Form } from 'vee-validate';
import {
  HomeModernIcon,
  SparklesIcon,
  CurrencyEuroIcon,
  KeyIcon,
  LockClosedIcon,
  PlusIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';
import { useCurrencyStore } from '~/../stores/currencyStore';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));
const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const currencyStore = useCurrencyStore();
const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const isNew = computed(() => route.params.id === 'pridat');

const pageTitle = ref(isNew.value ? 'Nový apartmán' : 'Detail apartmánu');

const breadcrumbs = ref([
  { name: 'Apartmány', link: '/ubytovani/apartmany', current: false },
  { name: pageTitle.value, link: '/ubytovani/apartmany/pridat', current: true },
]);

const tabs = ref([
  { name: 'Základní údaje', link: '#info' },
  { name: 'Ceny', link: '#ceny' },
  { name: 'Rezervace', link: '#rezervace' },
  { name: 'Blokace', link: '#blokace' },
]);

const activeTab = ref('#info');
const reservationSubTab = ref<'upcoming' | 'current' | 'past'>('upcoming');

const item = ref({
  id: null as number | null,
  code: '' as string,
  image: '' as any,
  status: 'draft' as string,
  apartment_type_id: null as number | null,
  building_id: null as number | null,
  currency_id: null as number | null,
  capacity: 2 as number,
  bedrooms: 1 as number,
  bathrooms: 1 as number,
  area: null as number | null,
  floor: null as number | null,
  base_price: 0 as number,
  position: 0 as number,
  amenities: [] as number[],
  season_prices: [] as { season_id: number; price: number }[],
  translations: {} as Record<string, any>,
  sites: [] as number[],
  translateAutomatically: false as boolean,
});

const translatableAttributes = ref([
  { field: 'name', label: 'Název' },
  { field: 'slug', label: 'URL slug' },
  { field: 'perex', label: 'Perex' },
  { field: 'text', label: 'Popis' },
  { field: 'meta_title', label: 'SEO titulek' },
  { field: 'meta_description', label: 'SEO popis' },
]);

const apartmentTypes = ref<{ value: number; name: string }[]>([]);
const buildings = ref<{ value: number; name: string }[]>([]);
const amenities = ref<{ id: number; name: string; icon: string }[]>([]);
const seasons = ref<{ id: number; name: string; color: string }[]>([]);
const reservations = ref<any[]>([]);
const blocks = ref<any[]>([]);

const newBlock = ref({
  apartment_id: null as number | null,
  start_date: '' as string,
  end_date: '' as string,
  reason: 'maintenance' as string,
  note: '' as string,
});

async function loadLookups() {
  const client = useSanctumClient();
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Site-Hash': selectedSiteHash.value,
  };

  await Promise.all([
    client('/api/admin/apartment/type', { method: 'GET', headers }).then((res: any) => {
      apartmentTypes.value = (Array.isArray(res) ? res : res.data || []).map((x: any) => ({
        value: x.id,
        name: x.name,
      }));
    }),
    client('/api/admin/building', { method: 'GET', headers }).then((res: any) => {
      buildings.value = (Array.isArray(res) ? res : res.data || []).map((x: any) => ({
        value: x.id,
        name: x.name,
      }));
    }),
    client('/api/admin/amenity', { method: 'GET', headers }).then((res: any) => {
      amenities.value = Array.isArray(res) ? res : res.data || [];
    }),
    client('/api/admin/season', { method: 'GET', headers }).then((res: any) => {
      seasons.value = Array.isArray(res) ? res : res.data || [];
    }),
  ]).catch(() => {
    $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst číselníky.', severity: 'error' });
  });
}

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/apartment/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      item.value = {
        ...response,
        amenities: response.amenities?.map((a: any) => a.id) || [],
        season_prices: response.season_prices || [],
      };
      item.value.sites = response.sites?.map((s: any) => s.id) || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.translations?.cs?.name || 'Detail apartmánu';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/ubytovani/apartmany/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
      ensureAllSeasonPrices();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst apartmán.',
        severity: 'error',
      });
      router.push('/ubytovani/apartmany');
    })
    .finally(() => {
      loading.value = false;
    });
}

function ensureAllSeasonPrices() {
  const existingIds = new Set(item.value.season_prices.map((sp: any) => sp.season_id));
  seasons.value.forEach((season) => {
    if (!existingIds.has(season.id)) {
      item.value.season_prices.push({ season_id: season.id, price: 0 });
    }
  });
}

async function loadReservations() {
  if (isNew.value) return;
  const client = useSanctumClient();
  await client('/api/admin/apartment/reservation', {
    method: 'GET',
    query: {
      apartment_id: route.params.id,
      tab: reservationSubTab.value,
      orderBy: 'start_date',
      orderWay: 'desc',
    },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((res: any) => {
      reservations.value = Array.isArray(res) ? res : res.data || [];
    })
    .catch(() => {
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst rezervace.',
        severity: 'error',
      });
    });
}

async function loadBlocks() {
  if (isNew.value) return;
  const client = useSanctumClient();
  await client('/api/admin/apartment/block', {
    method: 'GET',
    query: { apartment_id: route.params.id },
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((res: any) => {
      blocks.value = Array.isArray(res) ? res : res.data || [];
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst blokace.', severity: 'error' });
    });
}

async function saveBlock() {
  if (!newBlock.value.start_date || !newBlock.value.end_date) {
    $toast.show({
      summary: 'Upozornění',
      detail: 'Vyberte prosím datum od a do.',
      severity: 'warn',
    });
    return;
  }
  const client = useSanctumClient();
  newBlock.value.apartment_id = Number(route.params.id);
  await client('/api/admin/apartment/block', {
    method: 'POST',
    body: JSON.stringify(newBlock.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Blokace byla přidána.', severity: 'success' });
      newBlock.value = {
        apartment_id: null,
        start_date: '',
        end_date: '',
        reason: 'maintenance',
        note: '',
      };
      loadBlocks();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se přidat blokaci.', severity: 'error' });
    });
}

async function deleteBlock(id: number) {
  const client = useSanctumClient();
  await client('/api/admin/apartment/block/' + id, {
    method: 'DELETE',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then(() => {
      $toast.show({ summary: 'Hotovo', detail: 'Blokace byla smazána.', severity: 'success' });
      loadBlocks();
    })
    .catch(() => {
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se smazat blokaci.', severity: 'error' });
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;
  const client = useSanctumClient();
  loading.value = true;

  await client(isNew.value ? '/api/admin/apartment' : '/api/admin/apartment/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  })
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail: isNew.value ? 'Apartmán byl vytvořen.' : 'Apartmán byl upraven.',
        severity: 'success',
      });
      if (!redirect && isNew.value) router.push('/ubytovani/apartmany/' + response.id);
      else if (redirect) router.push('/ubytovani/apartmany');
      else loadItem();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit apartmán.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function toggleAmenity(amenityId: number) {
  if (item.value.amenities.includes(amenityId)) {
    item.value.amenities = item.value.amenities.filter((id) => id !== amenityId);
  } else {
    item.value.amenities.push(amenityId);
  }
}

function getSeasonPrice(seasonId: number) {
  const found = item.value.season_prices.find((sp: any) => sp.season_id === seasonId);
  return found ? found.price : 0;
}

function setSeasonPrice(seasonId: number, price: number) {
  const found = item.value.season_prices.find((sp: any) => sp.season_id === seasonId);
  if (found) {
    found.price = price;
  } else {
    item.value.season_prices.push({ season_id: seasonId, price });
  }
}

function fillEmptyTranslations() {
  languageStore.languages.forEach((language: any) => {
    if (item.value.translations[language.code] === undefined)
      item.value.translations[language.code] = {};
    translatableAttributes.value.forEach((attribute) => {
      if (item.value.translations[language.code][attribute.field] === undefined) {
        item.value.translations[language.code][attribute.field] = '';
      }
    });
  });
}

function switchTab(link: string) {
  activeTab.value = link;
  router.replace({ hash: link });
  if (link === '#rezervace') loadReservations();
  if (link === '#blokace') loadBlocks();
}

function switchReservationSubTab(key: 'upcoming' | 'current' | 'past') {
  reservationSubTab.value = key;
  loadReservations();
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => {
  loadLookups().then(() => {
    if (!isNew.value) loadItem();
  });
});

watch(seasons, () => {
  if (!isNew.value) ensureAllSeasonPrices();
});

onMounted(async () => {
  if (route.hash) activeTab.value = route.hash;
  await loadLookups();
  if (!isNew.value) {
    await loadItem();
    if (activeTab.value === '#rezervace') loadReservations();
    if (activeTab.value === '#blokace') loadBlocks();
  }
  fillEmptyTranslations();
});

definePageMeta({ middleware: 'sanctum:auth' });
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="apartments"
      @save="saveItem"
    />

    <div v-if="!isNew" class="flex flex-wrap gap-2 border-b border-slate-200">
      <button
        v-for="tab in tabs"
        :key="tab.link"
        type="button"
        class="relative px-4 py-3 text-sm font-semibold transition-colors"
        :class="activeTab === tab.link ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900'"
        @click="switchTab(tab.link)"
      >
        {{ tab.name }}
        <span
          v-if="activeTab === tab.link"
          class="absolute bottom-0 left-0 h-0.5 w-full bg-indigo-600"
        />
      </button>
    </div>

    <Form v-show="activeTab === '#info' || isNew" ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <HomeModernIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Základní údaje</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyk:</span
                >
                <span
                  class="rounded-md bg-slate-900 px-2 py-1 text-xs font-bold uppercase text-white"
                  >{{ selectedLocale }}</span
                >
              </div>
            </div>

            <div class="grid grid-cols-1 gap-y-6">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                :key="`name-${selectedLocale}`"
                v-model="item.translations[selectedLocale].name"
                label="Název apartmánu"
                type="text"
                name="name"
                rules="required|min:2"
              />
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                :key="`slug-${selectedLocale}`"
                v-model="item.translations[selectedLocale].slug"
                label="URL slug"
                type="text"
                name="slug"
              />
              <BaseFormTextarea
                v-if="item.translations?.[selectedLocale]?.perex !== undefined"
                :key="`perex-${selectedLocale}`"
                v-model="item.translations[selectedLocale].perex"
                label="Perex"
                name="perex"
              />
              <BaseFormEditor
                v-if="item.translations?.[selectedLocale]?.text !== undefined"
                :key="`text-${selectedLocale}`"
                v-model="item.translations[selectedLocale].text"
                label="Popis"
                name="text"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <LayoutTitle class="!mb-6">Parametry</LayoutTitle>
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-x-6">
              <BaseFormSelect
                v-model="item.apartment_type_id"
                label="Typ apartmánu"
                name="apartment_type_id"
                :options="apartmentTypes"
              />
              <BaseFormSelect
                v-model="item.building_id"
                label="Budova"
                name="building_id"
                :options="buildings"
              />
              <BaseFormSelect
                v-model="item.status"
                label="Stav"
                name="status"
                :options="[
                  { value: 'draft', name: 'Koncept' },
                  { value: 'published', name: 'Publikováno' },
                  { value: 'archived', name: 'Archivováno' },
                ]"
              />
              <BaseFormInput
                v-model="item.capacity"
                label="Kapacita (osob)"
                type="number"
                name="capacity"
              />
              <BaseFormInput
                v-model="item.bedrooms"
                label="Ložnice"
                type="number"
                name="bedrooms"
              />
              <BaseFormInput
                v-model="item.bathrooms"
                label="Koupelny"
                type="number"
                name="bathrooms"
              />
              <BaseFormInput v-model="item.area" label="Plocha (m²)" type="number" name="area" />
              <BaseFormInput v-model="item.floor" label="Patro" type="number" name="floor" />
              <BaseFormInput
                v-model="item.base_price"
                label="Základní cena / noc"
                type="number"
                name="base_price"
              />
              <BaseFormSelect
                v-model="item.currency_id"
                label="Měna"
                name="currency_id"
                :options="
                  currencyStore.currencies?.map((c: any) => ({ value: c.id, name: c.code })) || []
                "
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <SparklesIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Vybavení</LayoutTitle>
            </div>
            <div
              v-if="amenities.length > 0"
              class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
            >
              <div
                v-for="amenity in amenities"
                :key="amenity.id"
                class="flex items-center rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 transition-all hover:ring-indigo-300"
              >
                <BaseFormCheckbox
                  :label="amenity.name"
                  :name="`amenity-${amenity.id}`"
                  :value="item.amenities.includes(amenity.id)"
                  :checked="item.amenities.includes(amenity.id)"
                  class="w-full flex-row-reverse justify-between font-medium text-slate-700"
                  @change="toggleAmenity(amenity.id)"
                />
              </div>
            </div>
            <p v-else class="text-center text-sm italic text-slate-400">
              Žádné vybavení zatím není v systému. Nejdřív si založ vybavení v sekci Ubytování →
              Vybavení.
            </p>
          </LayoutContainer>

          <LayoutContainer>
            <LayoutDivider>Galerie fotografií</LayoutDivider>
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="true"
              format="apartment"
              type="icon"
              label="Nahrát fotografie"
              @update-files="
                (files: any) => {
                  item.image = files;
                }
              "
            />
          </LayoutContainer>

          <LayoutContainer>
            <LayoutDivider>SEO</LayoutDivider>
            <div class="grid grid-cols-1 gap-y-6 pt-4">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                :key="`meta_title-${selectedLocale}`"
                v-model="item.translations[selectedLocale].meta_title"
                label="SEO titulek"
                type="text"
                name="meta_title"
              />
              <BaseFormTextarea
                v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                :key="`meta_description-${selectedLocale}`"
                v-model="item.translations[selectedLocale].meta_description"
                label="SEO popis"
                name="meta_description"
              />
            </div>
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:position="item.position"
            v-model:state="item.status"
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-position="true"
            :allow-state="true"
            :states="[
              { value: 'draft', name: 'Koncept' },
              { value: 'published', name: 'Publikováno' },
              { value: 'archived', name: 'Archivováno' },
            ]"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>

    <div v-show="activeTab === '#ceny' && !isNew">
      <LayoutContainer>
        <div class="mb-6 flex items-center gap-3">
          <div class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
            <CurrencyEuroIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Ceny podle ročních období</LayoutTitle>
        </div>
        <div v-if="seasons.length === 0" class="text-center text-sm italic text-slate-400">
          Žádná roční období nejsou založena. Založ je nejdřív v sekci Ubytování → Roční období.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="season in seasons"
            :key="season.id"
            class="flex items-center justify-between rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200"
          >
            <div class="flex items-center gap-3">
              <div class="size-3 rounded-full" :style="{ background: season.color || '#94a3b8' }" />
              <span class="font-medium text-slate-700">{{ season.name }}</span>
            </div>
            <div class="flex items-center gap-2">
              <input
                type="number"
                class="w-32 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                :value="getSeasonPrice(season.id)"
                @input="
                  setSeasonPrice(season.id, Number(($event.target as HTMLInputElement).value))
                "
              />
              <span class="text-sm text-slate-500">/ noc</span>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            type="button"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
            @click="saveItem(false)"
          >
            Uložit ceny
          </button>
        </div>
      </LayoutContainer>
    </div>

    <div v-show="activeTab === '#rezervace' && !isNew">
      <LayoutContainer>
        <div class="mb-6 flex items-center gap-3">
          <div
            class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
          >
            <KeyIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Rezervace apartmánu</LayoutTitle>
        </div>

        <div class="mb-4 flex flex-wrap gap-2 border-b border-slate-200">
          <button
            v-for="tab in [
              { key: 'upcoming', label: 'Budoucí' },
              { key: 'current', label: 'Aktuální' },
              { key: 'past', label: 'Minulé a zrušené' },
            ]"
            :key="tab.key"
            type="button"
            class="relative px-3 py-2 text-sm font-semibold transition-colors"
            :class="
              reservationSubTab === tab.key
                ? 'text-indigo-600'
                : 'text-slate-500 hover:text-slate-900'
            "
            @click="switchReservationSubTab(tab.key as 'upcoming' | 'current' | 'past')"
          >
            {{ tab.label }}
            <span
              v-if="reservationSubTab === tab.key"
              class="absolute bottom-0 left-0 h-0.5 w-full bg-indigo-600"
            />
          </button>
        </div>

        <div
          v-if="reservations.length === 0"
          class="py-8 text-center text-sm italic text-slate-400"
        >
          Žádné rezervace v této kategorii.
        </div>
        <div v-else class="overflow-hidden rounded-xl ring-1 ring-slate-200">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Kód</th>
                <th class="px-4 py-3">Host</th>
                <th class="px-4 py-3">Od</th>
                <th class="px-4 py-3">Do</th>
                <th class="px-4 py-3">Stav</th>
                <th class="px-4 py-3">Cena</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="res in reservations" :key="res.id" class="hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs">{{ res.code }}</td>
                <td class="px-4 py-3">{{ res.guest_firstname }} {{ res.guest_lastname }}</td>
                <td class="px-4 py-3">{{ res.start_date }}</td>
                <td class="px-4 py-3">{{ res.end_date }}</td>
                <td class="px-4 py-3">{{ res.status }}</td>
                <td class="px-4 py-3">{{ res.total_price }}</td>
                <td class="px-4 py-3 text-right">
                  <NuxtLink
                    :to="'/ubytovani/rezervace/' + res.id"
                    class="text-xs font-semibold text-indigo-600 hover:underline"
                  >
                    Detail
                  </NuxtLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </LayoutContainer>
    </div>

    <div v-show="activeTab === '#blokace' && !isNew">
      <LayoutContainer>
        <div class="mb-6 flex items-center gap-3">
          <div class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <LockClosedIcon class="size-5" />
          </div>
          <LayoutTitle class="!mb-0">Blokace</LayoutTitle>
        </div>

        <div class="mb-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <BaseFormInput
              v-model="newBlock.start_date"
              label="Od"
              type="date"
              name="block_start"
            />
            <BaseFormInput v-model="newBlock.end_date" label="Do" type="date" name="block_end" />
            <BaseFormSelect
              v-model="newBlock.reason"
              label="Důvod"
              name="block_reason"
              :options="[
                { value: 'maintenance', name: 'Údržba' },
                { value: 'owner', name: 'Pobyt majitele' },
                { value: 'other', name: 'Jiné' },
              ]"
            />
            <div class="flex items-end">
              <button
                type="button"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                @click="saveBlock"
              >
                <PlusIcon class="size-4" />
                Přidat
              </button>
            </div>
          </div>
          <BaseFormInput
            v-model="newBlock.note"
            label="Poznámka"
            type="text"
            name="block_note"
            class="mt-4"
          />
        </div>

        <div v-if="blocks.length === 0" class="py-8 text-center text-sm italic text-slate-400">
          Žádné blokace.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="block in blocks"
            :key="block.id"
            class="flex items-center justify-between rounded-xl bg-white p-4 ring-1 ring-slate-200"
          >
            <div>
              <div class="font-medium text-slate-700">
                {{ block.start_date }} — {{ block.end_date }}
              </div>
              <div class="text-xs text-slate-500">
                {{
                  { maintenance: 'Údržba', owner: 'Pobyt majitele', other: 'Jiné' }[
                    block.reason as string
                  ]
                }}
                <span v-if="block.note"> · {{ block.note }}</span>
              </div>
            </div>
            <button
              type="button"
              class="rounded-lg p-2 text-rose-600 hover:bg-rose-50"
              @click="deleteBlock(block.id)"
            >
              <TrashIcon class="size-4" />
            </button>
          </div>
        </div>
      </LayoutContainer>
    </div>
  </div>
</template>
