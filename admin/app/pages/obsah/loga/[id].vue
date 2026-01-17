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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nové logo' : 'Detail loga');

const breadcrumbs = ref([
  {
    name: 'Loga',
    link: '/obsah/loga',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/loga/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  image: '' as string,
  name: '' as string,
  position: 0 as number,
  url: '' as string,
  translations: {} as object,
  sites: [] as number[],
});
const translatableAttributes = ref([{ field: 'url' as string, label: 'URL' as string }]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    image: string;
    name: string;
    position: number;
    url: string;
    translations: object;
  }>('/api/admin/logo/' + route.params.id, {
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
        link: '/obsah/loga/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst logo. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/loga');
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
    price_type: string;
    price: number;
    tax_rate_id: number;
    currency_id: number;
    image: string;
    active: boolean;
    translations: object;
  }>(route.params.id === 'pridat' ? '/api/admin/logo' : '/api/admin/logo/' + route.params.id, {
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
            ? 'Logo bylo úspěšně vytvořeno.'
            : 'Logo bylo úspěšně upraveno.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/loga/' + response.id);
      } else if (redirect) {
        router.push('/obsah/loga');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit logo. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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
      slug="logos"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-start gap-x-4 gap-y-8 lg:grid-cols-12">
        <LayoutContainer class="col-span-9 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <BaseFormInput
              v-model="item.name"
              label="Název klienta"
              type="text"
              name="name"
              rules="required|min:1"
              class="col-span-1"
            />
            <BaseFormInput
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].url !== undefined
              "
              :key="`url-${selectedLocale}`"
              v-model="item.translations[selectedLocale].url"
              label="Odkaz"
              type="text"
              name="url"
              rules="required|min:3"
              class="col-span-1"
            />
            <div class="col-span-1">
              <BaseFormUploadImage
                v-model="item.image"
                :multiple="false"
                type="logo"
                format="large"
                label="Logo"
                class="pt-6"
                @update-files="updateItemImage"
              />
            </div>
          </div>
        </LayoutContainer>
        <LayoutActionsDetailBlock
          v-model:selected-locale="selectedLocale"
          v-model:position="item.position"
          v-model:sites="item.sites"
          :allow-position="true"
          :allow-image="false"
          class="col-span-3"
        />
      </div>
    </Form>
  </div>
</template>
