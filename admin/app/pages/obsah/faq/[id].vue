<script setup lang="ts">
import {inject, ref} from 'vue';
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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový FAQ dotaz' : 'Detail FAQ dotazu');

const breadcrumbs = ref([
  {
    name: 'FAQ',
    link: '/obsah/faq',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/obsah/faq/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  question: '' as string,
  answer: 0 as number,
  position: 0 as number,
  active: true as boolean,
  translations: {} as object,
  sites: [] as number[],
});

const translatableAttributes = ref([
  { field: 'question' as string, label: 'Dotaz' as string },
  { field: 'answer' as string, label: 'Odpověď' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    question: string;
    answer: string;
    position: number;
    active: boolean;
    translations: object;
  }>('/api/admin/faq/' + route.params.id, {
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
      pageTitle.value = item.value.question;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/obsah/faq/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst dotaz. Zkuste to prosím později.',
        severity: 'error',
      });
      router.push('/obsah/faq');
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
    question: string;
    answer: string;
    position: number;
    active: boolean;
    translations: object;
  }>(route.params.id === 'pridat' ? '/api/admin/faq' : '/api/admin/faq/' + route.params.id, {
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
            ? 'Dotaz byl úspěšně vytvořen.'
            : 'Dotaz byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/obsah/faq/' + response.id);
      } else if (redirect) {
        router.push('/obsah/faq');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit dotaz. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

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
      slug="faqs"
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
                item.translations[selectedLocale].question !== undefined
              "
              :key="`question-${selectedLocale}`"
              v-model="item.translations[selectedLocale].question"
              label="Dotaz"
              type="text"
              name="question"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormEditor
              v-if="
                item.translations &&
                item.translations[selectedLocale] !== undefined &&
                item.translations[selectedLocale].answer !== undefined
              "
              :key="`answer-${selectedLocale}`"
              v-model="item.translations[selectedLocale].answer"
              label="Odpověď"
              name="answer"
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
              type="number"
              name="position"
              rules="required|min:3"
              class="col-span-1"
            />
          </div>
          <div class="col-span-1">
            <BaseFormCheckbox
              v-model="item.active"
              name="active"
              label="Aktivní"
              class="col-span-1 flex-row-reverse justify-between"
              :checked="item.active"
              label-color="grayCustom"
              :reverse="true"
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
