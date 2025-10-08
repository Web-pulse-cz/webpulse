<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { Form } from 'vee-validate';

const toast = useToast();

const route = useRoute();
const router = useRouter();

const languageStore = useLanguageStore();
const selectedLocale = ref('cs');

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová země' : 'Detail země');

const breadcrumbs = ref([
  {
    name: 'Země',
    link: '/nastaveni/zeme',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/zeme/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  code: '' as string,
  iso: '' as string,
  phone_prefix: '' as string,
  active: true as boolean,
  translations: {} as object,
});
const translatableAttributes = ref([{ field: 'name' as string, label: 'Název' as string }]);

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    code: string;
    iso: string;
    phone_prefix: string;
    active: boolean;
    translations: object;
  }>('/api/admin/country/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      breadcrumbs.value.pop();
      pageTitle.value = item.value.name;
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/zeme/' + route.params.id,
        current: true,
      });
      fillEmptyTranslations();
    })
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst zemi. Zkuste to prosím později.',
        severity: 'error',
        group: 'bc',
      });
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
    code: string;
    iso: string;
    phone_prefix: string;
    active: boolean;
    translations: object;
  }>(
    route.params.id === 'pridat' ? '/api/admin/country' : '/api/admin/country/' + route.params.id,
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
      toast.add({
        summary: 'Hotovo',
        detail:
          route.params.id === 'pridat'
            ? 'Země byla úspěšně vytvořena.'
            : 'Země byla úspěšně upravena.',
        severity: 'succcess',
        group: 'bc',
      });
      if (!redirect && route.params.id === 'pridat') {
        router.push('/nastaveni/zeme/' + response.id);
      } else if (redirect) {
        router.push('/nastaveni/zeme');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      toast.add({
        summary: 'Chyba',
        detail:
          'Nepodařilo se upravit zemi. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.',
        severity: 'error',
        group: 'bc',
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
      slug="countries"
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
              v-model="item.translations[selectedLocale].name"
              label="Název"
              type="text"
              name="name"
              rules="required|min:3"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.code"
              label="Kód země"
              type="text"
              name="code"
              rules="required|min:2"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.iso"
              label="ISO kód země"
              type="text"
              name="iso"
              rules="required|min:2"
              class="col-span-1"
            />
            <BaseFormInput
              v-model="item.phone_prefix"
              label="Telefonní předvolba země"
              type="text"
              name="phone_prefix"
              rules="required|min:2"
              class="col-span-1"
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
          <BaseFormCheckbox
            v-model="item.active"
            name="active"
            label="Aktivní"
            class="col-span-1 mt-4 flex-row-reverse justify-between"
            :checked="item.active"
            label-color="grayCustom"
            :reverse="true"
          />
        </LayoutContainer>
      </div>
    </Form>
  </div>
</template>
