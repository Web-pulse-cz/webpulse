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

const pageTitle = ref(route.params.id === 'pridat' ? 'Nový changelog' : 'Detail changelogu');

const breadcrumbs = ref([
  {
    name: 'Changelog',
    link: '/nastaveni/changelog',
    current: false,
  },
  {
    name: pageTitle.value,
    link: '/nastaveni/changelog/pridat',
    current: true,
  },
]);

const item = ref({
  id: null as number | null,
  version: '' as string,
  title: '' as string,
  subtitle: '' as string,
  description: '' as string,
  type: 'bugfix' as string,
  priority: 'medium' as string,
});

async function loadItem() {
  const client = useSanctumClient();
  loading.value = true;

  await client<{
    id: number | null;
    version: string;
    title: string;
    subtitle: string;
    description: string;
    type: string;
    priority: string;
  }>('/api/admin/changelog/' + route.params.id, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
  })
    .then((response) => {
      item.value = response;
      pageTitle.value = item.value.title;
      breadcrumbs.value.pop();
      breadcrumbs.value.push({
        name: pageTitle.value,
        link: '/nastaveni/changelog/' + route.params.id,
        current: true,
      });
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: 'Nepodařilo se načíst changelog. Zkuste to prosím později.',
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
    version: string;
    title: string;
    subtitle: string | null;
    description: string | null;
    type: string;
    priority: string;
  }>(
    route.params.id === 'pridat'
      ? '/api/admin/changelog'
      : '/api/admin/changelog/' + route.params.id,
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
            ? 'Changelog byl úspěšně vytvořen.'
            : 'Changelog byl úspěšně upraven.',
        severity: 'success',
      });
      if (!redirect && route.params.id !== 'pridat') {
        router.push(`/nastaveni/changelog/${response.id}`);
      } else if (redirect) {
        router.push('/nastaveni/changelog');
      } else {
        loadItem();
      }
    })
    .catch(() => {
      error.value = true;
      $toast.show({
        summary: 'Chyba',
        detail: `Nepodařilo se ${route.params.id === 'pridat' ? 'vytvořit' : 'uložit'} changelog. Zkontrolujte, že máte vyplněna všechna pole správně a zkuste to znovu.`,
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
            v-model="item.version"
            label="Verze"
            type="text"
            name="version"
            rules="required|min:3"
            class="col-span-1"
          />
          <BaseFormSelect
            v-model="item.type"
            label="Typ"
            name="type"
            :options="[
              { value: 'bugfix', name: 'Oprava chyby' },
              { value: 'feature', name: 'Nová funkce' },
              { value: 'design', name: 'Vylepšení designu' },
              { value: 'other', name: 'Ostatní' },
            ]"
            rules="required"
            class="col-span-1"
          />
          <BaseFormSelect
            v-model="item.priority"
            label="Priorita"
            name="priority"
            :options="[
              { value: 'low', name: 'Nízká' },
              { value: 'medium', name: 'Normální' },
              { value: 'high', name: 'Vysoká' },
            ]"
            rules="required"
            class="col-span-1"
          />
          <BaseFormInput
            v-model="item.title"
            label="Název"
            type="text"
            name="title"
            rules="required|min:3"
            class="col-span-2"
          /><BaseFormInput
            v-model="item.subtitle"
            label="Podnadpis"
            type="text"
            name="subtitle"
            class="col-span-2"
          />
          <BaseFormEditor
            v-model="item.description"
            label="Popisek"
            name="description"
            rules="min:3"
            class="col-span-full"
          />
        </div>
      </LayoutContainer>
    </Form>
  </div>
</template>
