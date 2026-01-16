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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová kategorie' : 'Detail kategorie');

const breadcrumbs = ref([
  {
    name: 'Blogové články',
    link: '/obsah/clanky',
    current: false,
  },
  {
    name: 'Kategorie',
    link: '/obsah/clanky/kategorie',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/clanky/kategorie/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  position: 0 as number,
  image: '' as string,
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'slug' as string, label: 'Slug' as string },
  { field: 'perex' as string, label: 'Perex' as string },
  { field: 'text' as string, label: 'Popis' as string },
  { field: 'meta_title' as string, label: 'Meta title' as string },
  { field: 'meta_description' as string, label: 'Meta popis' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    position: number;
    image: string;
    active: boolean;
    translations: object;
  }>('/api/admin/post/category/' + route.params.id, {
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
        link: '/obsah/clanky/kategorie/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst kategorii. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/clanky/kategorie');
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
    name: string;
    position: number;
    image: string;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/post/category'
      : '/api/admin/post/category/' + route.params.id,
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
            ? 'Kategorie byla úspěšně vytvořena.'
            : 'Kategorie byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push(`/obsah/clanky/kategorie/${response.id}`);
      } else if (redirect) {
        router.push('/obsah/clanky/kategorie');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit kategorii. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function updateItemImage(files) {
  item.value.image = files[0];
}

function addRemoveItemSite(siteId) {
  if (item.value.sites.includes(siteId)) {
    item.value.sites = item.value.sites.filter((site) => site !== siteId);
    return;
  } else {
    item.value.sites.push(siteId);
  }
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
      slug="posts"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-5 w-full">
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
              class="col-span-full"
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
              class="col-span-2"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].text !== undefined
              "
              :key="`text-${selectedLocale}`"
              v-model="item.translations[selectedLocale].text"
              label="Popis"
              name="text"
              class="col-span-2"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full space-y-6">
          <div class="col-span-1">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
          <div class="col-span-1">
            <BaseFormInput
              v-model="item.position"
              label="Pořadí ve výpisu"
              name="position"
              class="col-span-1"
              min="0"
              type="number"
            />
          </div>
          <div class="col-span-1">
            <BaseFormCheckbox
              v-model="item.active"
              name="active"
              label="Aktivní"
              class="col-span-1 mt-4 flex-row-reverse justify-between"
              :checked="item.active"
              label-color="grayCustom"
              :reverse="true"
            />
          </div>
          <div class="col-span-1">
            <BaseFormUploadImage
              v-model="item.image"
              :multiple="false"
              type="post_category"
              format="medium"
              label="Náhledový obrázek"
              @update-files="updateItemImage"
            />
          </div>
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
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
