<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová recenze' : 'Detail recenze');

const breadcrumbs = ref([
  {
    name: 'Recenze',
    link: '/obsah/recenze',
    current: false,
  },
  {
    name: 'Nová recenze',
    link: '/obsah/recenze/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  firstname: '' as string,
  lastname: '' as string,
  name: '' as string,
  content: '' as string,
  rating: 1 as number,
  active: true as boolean,
  translations: {} as object,
});
const translatableAttributes = ref([
  { field: 'name' as string, label: 'Název' as string },
  { field: 'content' as string, label: 'Slug' as string },
]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    name: string;
    content: string;
    rating: number;
    active: boolean;
    translations: object;
  }>('/api/admin/review/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: item.value.name,
        link: '/obsah/novinky/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst recenzi. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function saveItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    firstname: string;
    lastname: string;
    name: string;
    content: string;
    rating: number;
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
    .then(() => {
      toast.add({
        title: 'Hotovo',
        description:
          route.params.id === 'pridat'
            ? 'Recenze byla úspěšně vytvořena.'
            : 'Recenze byla úspěšně upravena.',
        color: 'green',
      });
      router.push('/obsah/recenze');
      languageStore.fetchLanguages();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit recenzi. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        color: 'red',
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

function updateRating(newRating: number) {
  item.value.rating = newRating;
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
      :title="route.params.id === 'pridat' ? 'Nová recenze' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
      slug="reviews"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <div class="grid grid-cols-1 items-baseline gap-x-4 gap-y-8 lg:grid-cols-7">
        <LayoutContainer class="col-span-4 w-full">
          <div class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div class="col-span-full">
              <label class="block text-left text-xs font-medium text-grayCustom lg:text-sm/6"
                >Hodnocení<span class="ml-1 text-danger">*</span></label
              >
              <ReviewStars v-model:rating="item.rating" :max-stars="5" class="col-span-full mt-2" />
            </div>
            <BaseFormInput
              v-model="item.firstname"
              label="Jméno"
              type="text"
              name="firstname"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.lastname"
              label="Příjmení"
              type="text"
              name="lastname"
              class="col-span-1"
            />
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
                item.translations[selectedLocale].content !== undefined
              "
              :key="`content-${selectedLocale}`"
              v-model="item.translations[selectedLocale].content"
              label="Popis"
              name="content"
              rules="required|min:21"
              class="col-span-2"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-3 w-full">
          <div class="col-span-1 border-b pb-6">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
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
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
