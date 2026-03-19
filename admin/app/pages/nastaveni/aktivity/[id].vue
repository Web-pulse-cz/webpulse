<script setup lang="ts">
import { ref } from 'vue';

import { Form } from 'vee-validate';
import { useActivityStore } from '~~/stores/activityStore';

const activityStore = useActivityStore();

const { $toast } = useNuxtApp();

const route = useRoute();
const router = useRouter();

const error = ref(false);
const loading = ref(false);

const pageTitle = ref(route.params.id === 'pridat' ? 'Nová aktivita' : 'Detail aktivity');

const breadcrumbs = ref([
  {
    name: 'Aktivity',
    link: '/nastaveni/aktivity',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/aktivity/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  name: '' as string,
  color: '' as string | null,
  description: '' as string,
  is_business: false as boolean,
  is_personal: false as boolean,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    name: string;
    color: string | null;
    description: string | null;
    is_business: boolean;
    is_personal: boolean;
  }>('/api/admin/activity/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = item.value.name;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/aktivity/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst aktivitu. Zkuste to prosím později.',
        severity: 'error',
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
    name: string;
    color: string | null;
    description: string | null;
    is_business: boolean;
    is_personal: boolean;
  }>(
    route.params.id === 'pridat' ? '/api/admin/activity' : '/api/admin/activity/' + route.params.id,
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
            ? 'Aktivita byla úspěšně vytvořena.'
            : 'Aktivita byla úspěšně upravena.',
        severity: 'success',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/nastaveni/aktivity/${response.id}`);
      } else if (redirect) {
        router.push('/nastaveni/aktivity');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} aktivitu. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
        severity: 'error',
      });
    })
    .finally(() => {
      loading.value = false;
      activityStore.fetchActivities();
    });
}

useHead({
  title: pageTitle.value,
});

onMounted(() => {
  if (route.params.id !== 'pridat') {
    loadItem();
  }
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
      slug="activities"
      @save="saveItem"
    />
    <Form @submit="saveItem">
      <LayoutContainer>
        <div class="grid grid-cols-4 gap-x-8 gap-y-4">
          <BaseFormInput
            v-model="item.name"
            label="Název"
            type="text"
            name="name"
            rules="required|min:3"
            class="col-span-2"
          />
          <BaseFormColorPicker
            v-model="item.color"
            label="Barva"
            name="color"
            rules="min:3"
            class="col-span-2 lg:col-span-1"
          />
          <BaseFormTextarea
            v-model="item.description"
            label="Popisek"
            name="description"
            rules="min:3"
            class="col-span-full"
          />
          <BaseFormCheckbox
            v-model="item.is_business"
            :checked="item.is_business"
            label="Byznys aktivita"
            name="is_business"
            class="col-span-1"
          />
          <BaseFormCheckbox
            v-model="item.is_personal"
            :checked="item.is_personal"
            label="Osobní aktivita"
            name="is_personal"
            class="col-span-1"
          />
        </div>
      </LayoutContainer>
    </Form>
  </div>
</template>
