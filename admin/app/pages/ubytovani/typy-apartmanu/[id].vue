<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import { Squares2X2Icon } from '@heroicons/vue/24/outline';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(
  route.params.id === 'pridat' ? 'Nový typ apartmánu' : 'Detail typu apartmánu',
);

const breadcrumbs = ref([
  { name: 'Typy apartmánů', link: '/ubytovani/typy-apartmanu', current: false },
  { name: pageTitle.value, link: '/ubytovani/typy-apartmanu/pridat', current: true },
]);

const item = ref({
  id: null as number | null,
  image: '' as string,
  images: [] as string[],
  position: 0 as number,
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

  await client('/api/admin/apartment/type/' + route.params.id, {
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
      item.value.images = response.images?.map((img: any) => img.filename) || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.translations?.cs?.name || 'Detail typu apartmánu';
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/ubytovani/typy-apartmanu/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst typ apartmánu.',
        severity: 'error',
      });
      router.push('/ubytovani/typy-apartmanu');
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
    route.params.id === 'pridat'
      ? '/api/admin/apartment/type'
      : '/api/admin/apartment/type/' + route.params.id,
    {
      method: 'POST',
      body: JSON.stringify(item.value),
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    },
  )
    .then((response: any) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Typ apartmánu byl vytvořen.'
            : 'Typ apartmánu byl upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/ubytovani/typy-apartmanu/' + response.id);
      } else if (redirect) {
        router.push('/ubytovani/typy-apartmanu');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se uložit typ apartmánu.',
        severity: 'error',
      });
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
      slug="apartment_types"
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
                  <Squares2X2Icon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Typ apartmánu</LayoutTitle>
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
                label="Název"
                type="text"
                name="name"
                rules="required|min:2"
                placeholder="Např. Apartmán Deluxe"
              />
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                :key="`slug-${selectedLocale}`"
                v-model="item.translations[selectedLocale].slug"
                label="URL slug"
                type="text"
                name="slug"
                placeholder="apartman-deluxe"
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

          <LayoutContainer>
            <LayoutDivider>Galerie fotografií</LayoutDivider>
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="true"
              format="apartment-type"
              type="icon"
              label="Nahrát fotografie"
              @update-files="
                (files: string[]) => {
                  item.image = files;
                }
              "
            />
          </LayoutContainer>
        </div>

        <aside class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:position="item.position"
            v-model:sites="item.sites"
            :allow-image="false"
            :allow-position="true"
            class="shadow-sm"
          />
        </aside>
      </div>
    </Form>
  </div>
</template>
