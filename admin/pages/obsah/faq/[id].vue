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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový FAQ dotaz' : 'Detail FAQ dotazu');

const breadcrumbs = ref([
  {
    name: 'FAQ',
    link: '/obsah/faq',
    current: false,
  },
  {
    name: 'Nový dotaz',
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
  categories: [] as number[],
});

const translatableAttributes = ref([
  { field: 'question' as string, label: 'Dotaz' as string },
  { field: 'answer' as string, label: 'Odpověď' as string },
]);

const categories = ref([]);

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
    categories: number[];
  }>('/api/admin/faq/' + route.params.id, {
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
        link: '/obsah/faq/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst dotaz. Zkuste to prosím později.',
        color: 'red',
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

async function loadCategories() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{}>('/api/admin/faq/category', {
    method: 'GET',
    query: {
      orderBy: 'position',
      orderWay: 'asc',
    },
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      categories.value = response;
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description: 'Nepodařilo se načíst kategorie. Zkuste to prosím později.',
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
    question: string;
    answer: string;
    position: number;
    active: boolean;
    translations: object;
    categories: number[];
  }>(route.params.id === 'pridat' ? '/api/admin/faq' : '/api/admin/faq/' + route.params.id, {
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
            ? 'Dotaz byl úspěšně vytvořen.'
            : 'Dotaz byl úspěšně upraven.',
        color: 'green',
      });
      router.push('/obsah/faq');
      languageStore.fetchLanguages();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        title: 'Chyba',
        description:
          'Nepodařilo se upravit dotaz. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
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

function addRemoveItemCategory(categoryId) {
  if (item.value.categories.includes(categoryId)) {
    item.value.categories = item.value.categories.filter((category) => category !== categoryId);
    return;
  } else {
    item.value.categories.push(categoryId);
  }
}

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

onMounted(() => {
  loadCategories();
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
      :title="route.params.id === 'pridat' ? 'Nový dotaz' : item.name"
      :breadcrumbs="breadcrumbs"
      :actions="[{ type: 'save' }]"
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
          <div class="col-span-full border-b pb-6"></div>
          <div class="col-span-full grid grid-cols-4 gap-x-4 gap-y-6 pt-6">
            <LayoutTitle class="col-span-full">Zařazení do kategorií</LayoutTitle>
            <BaseFormCheckbox
              v-for="(category, key) in categories"
              :key="key"
              :label="category.name"
              :name="category.id"
              :value="item.categories.includes(category.id)"
              :checked="item.categories.includes(category.id)"
              class="col-span-1"
              @change="addRemoveItemCategory(category.id)"
            />
          </div>
        </LayoutContainer>
        <LayoutContainer class="col-span-2 w-full">
          <div class="col-span-1 border-b pb-6">
            <BaseFormSelect
              v-model="selectedLocale"
              label="Jazyk"
              name="locale"
              class="w-full"
              :options="languageStore.languageOptions"
            />
          </div>
          <div class="col-span-1 border-b pb-6">
            <BaseFormInput
              v-model="item.position"
              label="Pořadí ve výpisu"
              type="number"
              name="position"
              rules="required|min:3"
              class="col-span-1 pt-6"
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
