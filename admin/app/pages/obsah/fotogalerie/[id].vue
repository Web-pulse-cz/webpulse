<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
import Draggable from 'vuedraggable';
import {
  PhotoIcon,
  GlobeAltIcon,
  LightBulbIcon,
  TrashIcon,
  StarIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import { useLanguageStore } from '~~/stores/languageStore';

const { $toast } = useNuxtApp();
const user = useSanctumUser();
const selectedSiteHash = ref(inject('selectedSiteHash', ''));

const { formRef, validateForm } = useFormValidation();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová fotogalerie' : 'Detail fotogalerie');

const breadcrumbs = ref([
  {
    name: 'Fotogalerie',
    link: '/obsah/fotogalerie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/fotogalerie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  active: true as boolean,
  position: 0 as number,
  name: '' as string,
  images: [] as string[],
  translations: {} as object,
  sites: [] as number[],
});

const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'description' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    active: boolean;
    position: number;
    name: string;
    images: string[];
    translations: object;
  }>('/api/admin/photo-gallery/' + route.params.id, {
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
      item.value.images = response.images || [];
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/fotogalerie/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst fotogalerii. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/fotogalerie');
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
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/photo-gallery'
      : '/api/admin/photo-gallery/' + route.params.id,
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
            ? 'Fotogalerie byla úspěšně vytvořena.'
            : 'Fotogalerie byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/fotogalerie/' + response.id);
      } else if (redirect) {
        router.push('/obsah/fotogalerie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit fotogalerii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

function onImagesUploaded(result: any) {
  const filenames: string[] = Array.isArray(result)
    ? result.map((r: any) => (typeof r === 'string' ? r : r.filename || r.name)).filter(Boolean)
    : result?.filename
      ? [result.filename]
      : [];
  item.value.images = [...item.value.images, ...filenames];
}

function removeImage(index: number) {
  item.value.images.splice(index, 1);
}

function setMainImage(index: number) {
  if (index === 0) return;
  const [moved] = item.value.images.splice(index, 1);
  item.value.images.unshift(moved);
}

watch(selectedSiteHash, () => {
  loadItem();
});

useHead({
  title: pageTitle.value,
});

function fillEmptyTranslations() {
  languageStore.languages.forEach((language) => {
    if (item.value.translations[language.code] === undefined) {
      item.value.translations[language.code] = {};
      translatableAttributes.value.forEach((attribute) => {
        if (item.value.translations[language.code][attribute.field] === undefined) {
          item.value.translations[language.code][attribute.field] = '';
        }
      });
    }
  });
}

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
  fillEmptyTranslations();
});

definePageMeta({
  middleware: 'sanctum:auth',
});
</script>

<template>
  <div class="space-y-6 pb-24">
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="photo_galleries"
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
                  <PhotoIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Obsah galerie</LayoutTitle>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400"
                  >Jazyková verze:</span
                >
                <span
                  class="inline-flex items-center rounded-md bg-slate-900 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-white"
                >
                  {{ selectedLocale }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-2">
              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.name !== undefined"
                :key="`name-${selectedLocale}`"
                v-model="item.translations[selectedLocale].name"
                label="Název galerie"
                type="text"
                name="name"
                rules="required|min:3"
                class="col-span-full"
                placeholder="Např. Léto 2026 v hotelu"
              />

              <BaseFormInput
                v-if="item.translations?.[selectedLocale]?.slug !== undefined"
                :key="`slug-${selectedLocale}`"
                v-model="item.translations[selectedLocale].slug"
                label="URL slug"
                type="text"
                name="slug"
                class="col-span-full"
                placeholder="leto-2026"
              />

              <div
                class="col-span-full grid grid-cols-1 gap-6 rounded-2xl bg-slate-50 p-6 ring-1 ring-inset ring-slate-200/60 lg:grid-cols-2"
              >
                <div class="col-span-full mb-1 flex items-center gap-2">
                  <GlobeAltIcon class="size-4 text-slate-400" />
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500"
                    >SEO Nastavení</span
                  >
                </div>

                <BaseFormInput
                  v-if="item.translations?.[selectedLocale]?.meta_title !== undefined"
                  :key="`meta_title-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_title"
                  label="Meta název (Titulek v Googlu)"
                  type="text"
                  name="meta_title"
                  class="col-span-full"
                />

                <BaseFormTextarea
                  v-if="item.translations?.[selectedLocale]?.meta_description !== undefined"
                  :key="`meta_description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].meta_description"
                  label="Meta popis (Snippet ve vyhledávání)"
                  name="meta_description"
                  rows="2"
                  class="col-span-full"
                />
              </div>

              <div class="col-span-full pt-4">
                <BaseFormEditor
                  v-if="item.translations?.[selectedLocale]?.description !== undefined"
                  :key="`description-${selectedLocale}`"
                  v-model="item.translations[selectedLocale].description"
                  label="Popis galerie"
                  name="description"
                />
              </div>
            </div>
          </LayoutContainer>

          <LayoutContainer>
            <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-5">
              <div class="flex items-center gap-3">
                <div
                  class="flex size-8 items-center justify-center rounded-lg bg-pink-50 text-pink-600"
                >
                  <PhotoIcon class="size-5" />
                </div>
                <LayoutTitle class="!mb-0">Fotografie</LayoutTitle>
              </div>
              <span class="text-xs font-medium text-slate-400">
                {{ item.images.length }}
                {{ item.images.length === 1 ? 'fotografie' : item.images.length >= 2 && item.images.length <= 4 ? 'fotografie' : 'fotografií' }}
              </span>
            </div>

            <div v-if="item.images.length > 0" class="mb-6">
              <p class="mb-3 text-xs text-slate-500">
                Přetáhněte fotografie pro změnu pořadí. První fotografie se použije jako hlavní
                náhled galerie.
              </p>
              <draggable
                v-model="item.images"
                item-key="filename-{{ index }}"
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
                handle=".drag-handle"
              >
                <template #item="{ element, index }">
                  <div
                    class="drag-handle group relative aspect-square overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200 transition-all hover:shadow-lg"
                  >
                    <img
                      :src="`/content/images/gallery/full/${element}`"
                      :alt="`Fotografie ${index + 1}`"
                      class="h-full w-full cursor-grab object-cover transition-transform duration-500 group-hover:scale-105 active:cursor-grabbing"
                    />

                    <div
                      v-if="index === 0"
                      class="absolute left-2 top-2 inline-flex items-center gap-1 rounded-full bg-amber-500 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md"
                    >
                      <StarIconSolid class="size-3" />
                      Hlavní
                    </div>

                    <div
                      class="absolute right-2 top-2 z-10 flex gap-x-1 opacity-100 transition-opacity duration-200 md:opacity-0 md:group-hover:opacity-100"
                    >
                      <button
                        v-if="index !== 0"
                        type="button"
                        class="flex size-7 items-center justify-center rounded-full bg-amber-500 text-white shadow-sm ring-1 ring-amber-600 transition-colors hover:bg-amber-600"
                        title="Nastavit jako hlavní"
                        @click="setMainImage(index)"
                      >
                        <StarIcon class="size-3.5" />
                      </button>
                      <button
                        type="button"
                        class="flex size-7 items-center justify-center rounded-full bg-red-500 text-white shadow-sm ring-1 ring-red-600 transition-colors hover:bg-red-600"
                        title="Odstranit"
                        @click="removeImage(index)"
                      >
                        <TrashIcon class="size-3.5" />
                      </button>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>

            <div>
              <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">
                Nahrát další fotografie
              </p>
              <BaseFormUploadImage
                :multiple="true"
                type="gallery"
                format="full"
                label="Nahrát fotografie"
                @update-files="onImagesUploaded"
              />
            </div>
          </LayoutContainer>
        </div>

        <div class="col-span-1 lg:sticky lg:top-8 lg:col-span-3">
          <LayoutActionsDetailBlock
            v-model:selected-locale="selectedLocale"
            v-model:translate-automatically="item.translateAutomatically"
            v-model:sites="item.sites"
            v-model:active="item.active"
            v-model:position="item.position"
            :allow-image="false"
            :allow-is-active="true"
            :allow-position="true"
            class="shadow-sm"
          />

          <div class="mt-6 rounded-3xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-100">
            <div class="mb-3 flex items-center gap-2">
              <LightBulbIcon class="size-4 text-amber-600" />
              <h4 class="text-xs font-bold uppercase tracking-widest text-amber-900">
                Tip pro galerii
              </h4>
            </div>
            <p class="text-sm leading-relaxed text-amber-800/80">
              Pro lepší dohledatelnost vyplňte <strong>SEO meta název a popis</strong>. První
              fotografie se použije jako hlavní náhled galerie — pořadí lze upravit přetažením.
            </p>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>
