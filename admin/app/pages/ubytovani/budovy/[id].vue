<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { BuildingOffice2Icon, MapPinIcon, PhoneIcon } from '@heroicons/vue/24/outline';
import { useCountryStore } from '~/../stores/countryStore';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const countryStore = useCountryStore();
const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová budova' : 'Detail budovy');

const breadcrumbs = ref([
  { name: 'Budovy', link: '/ubytovani/budovy', current: false },
  { name: pageTitle.value, link: '/ubytovani/budovy/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  image: '' as any,
  position: 0 as number,
  address_street: '' as string,
  address_city: '' as string,
  address_zip: '' as string,
  country_id: null as number | null,
  latitude: null as number | null,
  longitude: null as number | null,
  contact_name: '' as string,
  contact_email: '' as string,
  contact_phone: '' as string,
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

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client('/api/admin/building/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Site-Hash': selectedSiteHash.value,
    },
  })
    .then((response: any) => {
      item.value = response;
      item.value.sites = response.sites?.map((site: any) => site.id) || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.translations?.cs?.name || 'Detail budovy';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/ubytovani/budovy/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se načíst budovu.', severity: 'error' });
      router.push('/ubytovani/budovy');
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem(redirect = true as boolean) {
  if (!(await validateForm())) return;

  const client = useSanctumClient();
  loading.value = true;

  await client(
    route.params.id === 'pridat' ? '/api/admin/building' : '/api/admin/building/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail: route.params.id === 'pridat' ? 'Budova byla vytvořena.' : 'Budova byla upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/ubytovani/budovy/' + response.id);
      } else if (redirect) {
        router.push('/ubytovani/budovy');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({ summary: 'Chyba', detail: 'Nepodařilo se uložit budovu.', severity: 'error' });
    })
    .finally(() => {
      loading.value = false;
    });
}

function fillEmptyTranslations() {
  languageStore.languages.forEach((language: any) => {
    if (item.value.translations[language.code] === undefined) {
      item.value.translations[language.code] = {};
    }
    translatableAttributes.value.forEach((attribute) => {
      if (item.value.translations[language.code][attribute.field] === undefined) {
        item.value.translations[language.code][attribute.field] = '';
      }
    });
  });
}

useHead({ title: pageTitle.value });
watch(selectedSiteHash, () => {
  if (route.params.id !== 'pridat') loadItem();
});

onMounted(() => {
  if (route.params.id !== 'pridat') loadItem();
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
      slug="buildings"
      @save="saveItem"
    />

    <Form ref="formRef" @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
        <div class="col-span-1 space-y-8 lg:col-span-9">
          <LayoutContainer>
            <div class="mb-8 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"
                >
                  <BuildingOffice2Icon class="size-5" />
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
                label="Název budovy"
                type="text"
                name="name"
                rules="required|min:2"
                placeholder="Např. Hotel Panorama"
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
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
              >
                <MapPinIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Adresa a poloha</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
              <BaseFormInput
                v-model="item.address_street"
                label="Ulice a č.p."
                type="text"
                name="address_street"
                class="sm:col-span-2"
              />
              <BaseFormInput
                v-model="item.address_city"
                label="Město"
                type="text"
                name="address_city"
              />
              <BaseFormInput
                v-model="item.address_zip"
                label="PSČ"
                type="text"
                name="address_zip"
              />
              <BaseFormSelect
                v-model="item.country_id"
                label="Země"
                name="country_id"
                :options="
                  countryStore.countries?.map((c: any) => ({ value: c.id, name: c.name })) || []
                "
              />
              <BaseFormInput
                v-model="item.latitude"
                label="Zeměpisná šířka"
                type="text"
                name="latitude"
              />
              <BaseFormInput
                v-model="item.longitude"
                label="Zeměpisná délka"
                type="text"
                name="longitude"
              />
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center gap-3">
              <div
                class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600"
              >
                <PhoneIcon class="size-5" />
              </div>
              <LayoutTitle class="!mb-0">Kontaktní osoba</LayoutTitle>
            </div>
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
              <BaseFormInput
                v-model="item.contact_name"
                label="Jméno"
                type="text"
                name="contact_name"
                class="sm:col-span-2"
              />
              <BaseFormInput
                v-model="item.contact_email"
                label="E-mail"
                type="email"
                name="contact_email"
              />
              <BaseFormInput
                v-model="item.contact_phone"
                label="Telefon"
                type="text"
                name="contact_phone"
              />
            </div>
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
            v-model:sites="item.sites"
            v-model:image="item.image"
            :allow-image="true"
            :allow-position="true"
            image-type="building"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
