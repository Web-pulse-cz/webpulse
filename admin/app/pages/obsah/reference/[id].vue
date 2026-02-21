<script setup lang="ts">
import { inject, ref } from 'vue';
import { Form } from 'vee-validate';
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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová reference' : 'Detail reference');

const breadcrumbs = ref([
  {
    name: 'Reference',
    link: '/obsah/reference',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/reference/pridat',
    current: true,
  },
]);

const categories = ref([]);

const item = ref({
  id: null as number | null,
  image: null as string | null,
  name: '' as string,
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'content' as string, label: 'Text' as string },
  { field: 'meta_title' as string, label: 'Meta název' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    image: string | null;
    name: string;
    active: boolean;
    translations: object;
  }>('/api/admin/review/' + route.params.id, {
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
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/reference/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst referenci. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/reference');
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
    image: string | null;
    name: string;
    active: boolean;
    translations: object;
  }>(route.params.id === 'pridat' ? '/api/admin/review' : '/api/admin/review/' + route.params.id, {
    method: 'POST',
    body: JSON.stringify(item.value),
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      $toast.show({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Reference byla úspěšně vytvořena.'
            : 'Reference byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/reference/' + response.id);
      } else if (redirect) {
        router.push('/obsah/reference');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit referenci. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

watch(selectedSiteHash, () => {
  loadItem();
});

useHead({
  title: pageTitle.value,
});

function fillEmptyTranslations() {
  // Set default translations for all languages
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

/* function updateRating(newRating: number) {
  item.value.rating = newRating;
} */

function updateItemImages(files) {
  item.value.images = files;
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
  <div>
    <LayoutHeader
      :title="pageTitle"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }, { type: 'save-and-stay' }]"
      slug="reviews"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
        <LayoutContainer class="col-span-9 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].name !== undefined
              "
              :key="`name-${selectedLocale}`"
              v-model="item.translations[selectedLocale].name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].perex !== undefined
              "
              :key="`perex-${selectedLocale}`"
              v-model="item.translations[selectedLocale].perex"
              label="Perex"
              name="perex"
              rules="required|min:21"
              class="col-span-2"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].content !== undefined
              "
              :key="`content-${selectedLocale}`"
              v-model="item.translations[selectedLocale].content"
              label="Text"
              name="content"
              rules="required|min:21"
              class="col-span-full"
            />
            <BaseFormUploadImage
              v-model="item.images"
              :multiple="true"
              type="review"
              format="medium"
              label="Galerie obrázků"
              @update-files="updateItemImages"
            />
            <LayoutDivider>SEO</LayoutDivider>
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].meta_title !== undefined
              "
              :key="`meta_title-${selectedLocale}`"
              v-model="item.translations[selectedLocale].meta_title"
              label="Meta název"
              type="text"
              name="meta_title"
              class="col-span-1"
            />
            <BaseFormTextarea
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].meta_description !== undefined
              "
              :key="`meta_description-${selectedLocale}`"
              v-model="item.translations[selectedLocale].meta_description"
              label="Meta popis"
              name="meta_description"
              class="col-span-1"
            />
          </div>
        </LayoutContainer>
        <LayoutActionsDetailBlock
          v-model:selected-locale="selectedLocale"
          v-model:translate-automatically="item.translateAutomatically"
          v-model:sites="item.sites"
          v-model:image="item.image"
          v-model:active="item.active"
          :allow-is-active="true"
          image-type="review"
          class="col-span-3"
        />
      </div>
    </Form>
  </div>
</template>
